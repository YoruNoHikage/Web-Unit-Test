<?php
require_once 'Entity/User.php';
require_once 'Entity/Test.php';
require_once 'Entity/Subtest.php';
require_once 'Entity/Result.php';
require_once 'Entity/Project.php';

require 'Model/UserModel.php';
require 'Model/ProjectModel.php';
require 'Model/TestModel.php';

class Controller
{
    public function __construct()
    {
        if($this->getSession('flashToDelete'))
            $this->deleteSession('flash');
        else
            $this->setSession('flashToDelete', true);
    }

    public function connectedOnly()
    {
        if(!$this->getSession('user')) // you have to be connected
        {
            $this->setFlashError('Vous devez être connecté !');
            header("Location: index.php");
        }
        else
            return unserialize($this->getSession('user'));
    }

    public function teacherOnly($user)
    {
        if($user->getRole() != 'teacher') // you have to be a teacher
        {
            $this->setFlashError('Vous n\'avez pas les droits nécessaires !');
            header("Location: index.php");
        }
        else
            return true;
    }

    public function indexAction()
    {
        require 'Views/index.php';
    }

    public function signInAction()
    {
        if($this->getSession('user')) // if already connected
        {
            //echo $this->getSession('user');
            $this->setFlashError('Vous êtes déjà connecté !');
            header("Location: index.php");
        }

        $usermodel = new UserModel();
        $user = $usermodel->getOneUserBy($_POST['username']);
        if($user != null)
        {
            $this->setSession('user', serialize($user));
            header("Location: index.php?action=userpanel");
        }
        else
        {
            $this->setFlashError('Le username n\'existe pas !');
            header("Location: index.php");
        }
    }

    public function signOutAction()
    {
        session_destroy();

        $this->setFlash('Vous avez bien été déconnecté.');

        header("Location: index.php");
    }

    public function userPanelAction()
    {
        $user = $this->connectedOnly();
        //on va recuperer les resultats de l'utilisateur
        $userModel = new UserModel();
        $user = $userModel->getUserResults($user);

        //on va ensuite recuperer les id de projets auxquel l'utilisateur a participe
        $results = $user->getResults();
        $projectIds = array();
        foreach($results as $result)
        {
            $fullNameArray = explode(":", $result["subtest"]->getFullname());
            if(!in_array($fullNameArray[2], $projectIds))
                array_push($projectIds, $fullNameArray[2]);
        }
        $projectModel = new ProjectModel();
        $projects = $projectModel->getAllProjects($projectIds);

        require 'Views/panel/index.php';
    }

    public function uploadSourcesAction()
    {
        $user = $this->connectedOnly();

        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $url = 'Projects/tmp/' . $user->getUsername();
            $files = scandir($url);
            $zip = new ZipArchive;

            foreach($files as $file)
            {
                $filename = explode('.', $file);
                if(end($filename) == 'zip')
                {
                    $res = $zip->open($url . '/' . $file);
                    if ($res === true && isset($_POST['projectId']))
                    {
                        $projectDir = "Projects/". $_POST['projectId'] . '/src/' . $user->getUsername();
                        if(is_dir($projectDir))
                            self::delTree($projectDir);
                        $userModel = new UserModel();
                        $userModel->deleteUserResults($user, $_POST['projectId']);
                        mkdir($projectDir, 0755, true);
                        $zip->extractTo($projectDir);
                        $zip->close();
                        $this->setFlash('Projet uploadé');
                        $this->launchTests($_POST['projectId'], $user->getUsername());
                    } else {
                        $this->setFlash('Projet non uploadé');
                    }
                    header("Location: index.php?action=results&projectid=" . $_POST['projectId'] . "&username=" . $user->getUsername());
                }
            }
        }
        else
        {
            if(is_dir('Projects/tmp/' . $user->getUsername()))
                self::delTree('Projects/tmp/' . $user->getUsername());
            if(isset($_GET['id']))
                $projectId = $_GET['id'];
            else
            {
                $this->setFlash('Pas de projet sélectionné');
                header("Location: index.php");
            }
            require 'Views/panel/uploadsources.php';
        }
    }

    public function uploadTmpAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        $uploadDir = 'Projects/tmp/' .$user->getUsername();
        if(!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);

        $uploadFile = $uploadDir . '/' . basename($_FILES['file']['name']);

        move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile);
    }

    public function deleteUploadedFileAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        if(isset($_POST['class']) && isset($_POST['status']))
        {
            if($_POST['status'] == 'old' && isset($_POST['projectid']))
            {
                $fileToDelete = 'Projects/' . $_POST['projectid'] . '/tests/' . $_POST['class'] . '.java';
                if(file_exists($fileToDelete)) {
                    $testModel = new TestModel();
                    $testModel->deleteTest($_POST['projectid'], $_POST['class']); // deletes the test in DB
                    unlink($fileToDelete); // deletes the test on the server
                    //header($_SERVER["SERVER_PROTOCOL"]." 200 OK : " . $_POST['projectid'] . " " .$_POST['class']);
                }
                else {
                    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
                }
            }
            else if($_POST['status'] == 'new')
            {
                $fileToDelete = 'Projects/tmp/' . $user->getUsername() . '/' . $_POST['class'] . '.java';
                if(file_exists($fileToDelete))
                    unlink($fileToDelete);
            }
            else
            {
                header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            }
        }
        else
        {
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        }
    }

    public function serializeTests($user)
    {
        //nous allons verifier tous les fichiers dans le rep tmp de l'utilisateur
        $url = 'Projects/tmp/' . $user->getUsername();
        $filenames = scandir($url);

        $filesToProcess = array();
        //pour chaque fichier à traiter
        foreach($filenames as $filename)
        {
            $filenameExploded = explode('.', $filename);

            $extension = end($filenameExploded);
            //on verifie que l'extension est bien .java
            if($extension == 'java')
            {
                $file = fopen($url . '/' . $filename, 'r');
                $content = fread($file, filesize($url . '/' . $filename));
                fclose($file);

                preg_match_all('#@Test([\t\n\r\s])+public void (.*?)\(#', $content, $matches);
                $testFuncs = array();
                foreach($matches[2] as $name)
                {
                    array_push($testFuncs, array("name" => $name, "value" => 1));
                }
                array_push($filesToProcess, array('class' => $filenameExploded[0], 'subtests' => $testFuncs, 'reprocess' => false));
            }
        }

        return $filesToProcess;
    }

    public function newProjectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        if($_SERVER['REQUEST_METHOD'] == 'POST') // first form was sent
        {
            $filesToProcess = $this->serializeTests($user);
            $this->setSession("tests", serialize($filesToProcess));

            //on verif que les champs ont bien ete remplis
            if(isset($_POST['name']) && isset($_POST['duedate']))
            {
                $duedate = DateTime::createFromFormat('d/m/Y H:i', $_POST['duedate']);

                $projectModel = new ProjectModel(); 
                $project = new Project();
                $project->setName($_POST['name']);
                $project->setEnabled(1);
                $project->setDue_date($duedate);
                $project->setOwner($user);
                $projectModel->newProject($project);
            }
            else
            {
                $this->setFlashError('Veuillez remplir tous les champs');
                header("Location: index.php?action=newproject");
            }

            require 'Views/panel/newproject.php';
        }
        else
        {
            if(is_dir('Projects/tmp/' . $user->getUsername()))
                self::delTree('Projects/tmp/' . $user->getUsername());
            require 'Views/panel/newproject.php';
        }
    }

    public function projectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        if(isset($_GET['id']))
            $projectId = $_GET['id'];
        else
        {
            $this->setFlashError('Mauvais paramètres !');
            header("Location: index.php");
        }

        $projectModel = new ProjectModel();
        $project = $projectModel->getOneProjectBy($projectId);

        $projectTotalWeight = $projectModel->getProjectTotalWeight($project);

        $project = $projectModel->getProjectTests($project);

        $users = $projectModel->getProjectParticipants($project);

        require 'Views/panel/project.php';
    }

    public function editProjectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        if(isset($_GET['id']))
        {
            $projectModel = new ProjectModel();
            $project = $projectModel->getOneProjectBy($_GET['id']);
            //gerer si id defaillant ?
            $project = $projectModel->getProjectTests($project);
            $testModel = new TestModel();
            foreach($project->getTests() as $test)
            {
                $test = $testModel->getTestSubtests($test);
            }
            
            if($_SERVER['REQUEST_METHOD'] == 'POST') // first form was sent
            {
                $filesToProcess = $this->serializeTests($user);
                foreach($project->getTests() as $test)
                {
                    $subtests = array();
                    foreach($test->getSubtests() as $sub)
                    {
                        array_push($subtests, array("name" => $sub->getName(), "value" => $sub->getWeight()));
                    }
                    array_push($filesToProcess, array('class' => $test->getName(),
                                                      'subtests' => $subtests,
                                                      'reprocess' => true));
                }
                $this->setSession("tests", serialize($filesToProcess)); // we keep the new and edited files

                if(isset($_POST['name']) && isset($_POST['duedate']))
                {
                    $duedate = DateTime::createFromFormat('d/m/Y H:i', $_POST['duedate']);

                    $project->setId($_POST['projectid']);
                    $project->setName($_POST['name']);
                    $project->setDue_date($duedate);
                    
                    $projectModel->updateProject($project);
                }
                else
                {
                    $this->setFlashError('Veuillez remplir tous les champs');
                    header("Location: index.php?action=editproject&id=" . $_GET['id']);
                }

                require 'Views/panel/editproject.php';
            }
        }
        else
        {
            $this->setFlash('Pas de projet sélectionné');
            header("Location: index.php");
        }

        require 'Views/panel/editproject.php';
    }

    public function deleteProjectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        if($_SERVER['REQUEST_METHOD'] == 'POST') // if we confirm the deletion
        {
            if(isset($_POST["id"]))
                $projectId = $_POST["id"];
            else
            {
                $this->setFlash('Pas de projet sélectionné');
                header("Location: index.php");
            }

            $projectModel = new ProjectModel();
            $projectModel->deleteProject($projectId);

            if(is_dir("Projects/" . $projectId))
                self::delTree("Projects/" . $projectId);

            $this->setFlash('Le projet a bien été supprimé !');
            header("Location: index.php?action=userpanel");
        }
        else
            require 'Views/panel/deleteproject.php';
    }

    public function addTestsAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        if(isset($_GET["id"]))
        {
            $projectId = $_GET["id"];
            $tests = unserialize($this->getSession("tests"));
            //$this->deleteSession("tests");
            $testsArray = array();

            // we get the tests in database to avoid redundancy and to update the old ones
            $testModel = new TestModel();
            $testsDb = $testModel->getTestsSubtestsByProjectId($projectId);
            
            // just the names
            $testNames = array();
            foreach($testsDb as $testDb)
            {
                if(!in_array($testDb->getName(), $testNames))
                    array_push($testNames, $testDb->getName());
            }
            
            $error = null;
            foreach($tests as $test)
            {
                // if the test doesn't exist
                $key = array_search($test["class"], $testNames);
                if($key === false)
                {
                    $newTest = new Test();
                    $newTest->setName($test["class"]);
                    $newTest->setFullname($test["class"] . ":" . $projectId);
                    $newTest->setDescription("description");

                    $subtests = $test['subtests'];
                    foreach($subtests as $subtest)
                    {
                        $newSubtest = new Subtest();
                        $newSubtest->setName($subtest['name']);
                        $fullname = $subtest['name'] . ":" . $test["class"] . ":" . $projectId;
                        $newSubtest->setFullname($fullname);
                        if(isset($_POST[$fullname]))
                            $newSubtest->setWeight($_POST[$fullname]);
                        $newSubtest->setKind("kind");
                        $newTest->addSubtest($newSubtest);
                    }
                    array_push($testsArray, $newTest);

                    //on copie maintenant la classe de test dans le repertoire du projet
                    $projectDir = "Projects/". $projectId . '/tests';
                    if(!is_dir($projectDir))
                        mkdir($projectDir, 0755, true);
                    
                    $location = "Projects/tmp/" . $user->getUsername(). "/" . $test["class"] . ".java";
                    if(file_exists($location))
                        copy($location, $projectDir . '/' . $test["class"] . ".java");
                }
                else // update the test
                {                    
                    $subs = $testsDb[$key]->getSubtests();
                    foreach($subs as $sub)
                    {
                        //echo $sub->getWeight();
                        $sub->setWeight($_POST[$sub->getFullname()]);
                        //echo ' => ' . $sub->getWeight() . '<br/>';
                    }
                }
                
                if(!$test["reprocess"]) // if the files in tmp are new, we delete them
                {
                    unlink("Projects/tmp/" . $user->getUsername() . "/" . $test["class"] . ".java");
                }
            }
            
            $projectModel = new ProjectModel();
            //si on a bien des tests à rajouter
            if(count($testsArray) && !$error)
            {
                $projectModel->addTests($projectId, $testsArray);
                $this->setFlash("Les tests ont bien été ajoutés !");
            }
            
            // We update the others
            $projectModel->updateTestsSubtests($projectId, $testsDb);

            header("Location: index.php?action=userpanel");
        }
        else
        {
            $this->setFlashError('Pas de projet sélectionné !');
            header("Location: index.php");
        }
    }

    public function resultsAction()
    {
        $user = $this->connectedOnly();

        if(isset($_GET['projectid']) && isset($_GET['username']))
        {
            $projectId = $_GET['projectid'];
            $username = $_GET['username'];
        }
        else
        {
            $this->setFlashError('Mauvais paramètres !');
            header("Location: index.php");
        }

        $userModel = new userModel();
        $pupil = $userModel->getUserWithProjectResults($username, $projectId);

        require 'Views/panel/results.php';
    }

    public function launchTests($projectId, $username)
    {
        //windows compilation
        $cmd = 'javac -cp  ./Lib/*;./Projects;./Projects/'. $projectId .'/src/'. $username . ';./Projects/' . $projectId . '/tests ./Projects/Main.java ./Projects/' . $projectId . '/src/' . $username .'/*.java';
        exec($cmd);
        exec('java -cp  ./Lib/*;./Projects;./Projects/' . $projectId . '/src/' . $username . ';./Projects/' . $projectId . '/tests Main ' . $projectId . ' ' . $username);
        //linux compilation
        //exec("javac -cp Lib/hamcrest-core-1.3.jar:Lib/junit-4.11.jar:Lib/jdbc.jar:Lib/mysql-connector-java-5.1.26-bin.jar:Projects:Projects/8/src/pizza:Projects/8/tests Projects/Main.java Projects/8/src/pizza/Money.java");
        //exec("java -cp Lib/hamcrest-core-1.3.jar:Lib/junit-4.11.jar:Lib/jdbc.jar:Lib/mysql-connector-java-5.1.26-bin.jar:Projects:Projects/8/src/pizza:Projects/8/tests Main");

    }

    public function setFlashError($message)
    {
        $this->setFlash($message, 'danger');
    }

    public function setFlash($message, $type = 'success')
    {
        $this->setSession('flashType', $type);
        $this->setSession('flash', $message);
        $this->setSession('flashToDelete', false);
    }

    public function getSession($name)
    {
        if(!isset($_SESSION[$name]))
            return false;

        return $_SESSION[$name];
    }

    public function setSession($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function deleteSession($name)
    {
        unset($_SESSION[$name]);
    }

    public static function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    } 
}