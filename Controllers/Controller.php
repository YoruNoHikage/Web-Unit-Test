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
        // you have to be connected
        if(!$this->getSession('user'))
        {
            $this->setFlashError('Vous devez être connecté !');
            header("Location: index.php");
        }
        else
            return unserialize($this->getSession('user'));
    }

    public function teacherOnly($user)
    {
        // you have to be a teacher
        if($user->getRole() != 'teacher')
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
        // if already connected
        if($this->getSession('user'))
        {
            //echo $this->getSession('user');
            $this->setFlashError('Vous êtes déjà connecté !');
            header("Location: index.php");
        }

        //we load user's personal data
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
        //we're going to load users results
        $userModel = new UserModel();
        $user = $userModel->getUserResults($user);

        //we get id's of the project student took part in
        $results = $user->getResults();
        $projectIds = array();
        foreach($results as $result)
        {
            $fullNameArray = explode(":", $result["subtest"]->getFullname());
            if(!in_array($fullNameArray[2], $projectIds))
                array_push($projectIds, $fullNameArray[2]);
        }

        //we get all projects available
        $projectModel = new ProjectModel();
        $projects = $projectModel->getAllProjects($projectIds);

        require 'Views/panel/index.php';
    }

    public function uploadSourcesAction()
    {
        $user = $this->connectedOnly();
        //if the users has already sent some files
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            //we get the files the user just uploaded (in his personal tmp dir)
            $url = 'Projects/tmp/' . $user->getUsername();
            $files = scandir($url);
            $zip = new ZipArchive;

            foreach($files as $file)
            {
                $filename = explode('.', $file);
                //if the file is really a archive
                if(end($filename) == 'zip')
                {
                    $res = $zip->open($url . '/' . $file);
                    if ($res === true && isset($_POST['projectId']))
                    {
                        //we clear all the tested files already sent before
                        $projectDir = "Projects/". $_POST['projectId'] . '/src/' . $user->getUsername();
                        if(is_dir($projectDir))
                            self::delTree($projectDir);
                        //we clear all the user results for this project in db
                        $userModel = new UserModel();
                        $userModel->deleteUserResults($user, $_POST['projectId']);
                        //we create the dir where the tested files will be copied
                        mkdir($projectDir, 0755, true);
                        $zip->extractTo($projectDir);
                        $zip->close();
                        $this->setFlash('Projet uploadé');
                        //we launch a new test session
                        $this->launchTests($_POST['projectId'], $user->getUsername());
                    } else {
                        $this->setFlash('Projet non uploadé');
                    }
                }
            }
        }
        else
        {
            //if the user want to upload some files
            //we clear all the tmp files from the user personal tmp dir
            if(is_dir('Projects/tmp/' . $user->getUsername()))
                self::delTree('Projects/tmp/' . $user->getUsername());
            //if the project id is set
            if(isset($_GET['id']))
            {
                $projectId = $_GET['id'];
                $projectModel = new ProjectModel();
                $project = $projectModel->getOneProjectBy($projectId);
                $now = new DateTime('now');
                //if the project isn't closed (due date)
                if($project->getDue_date() > $now)
                    require 'Views/panel/uploadsources.php';
                else
                {
                    $this->setFlashError('Le projet est clos');
                    header("Location: index.php");
                }
            }
            else
            {
                $this->setFlash('Pas de projet sélectionné');
                header("Location: index.php");
            }
        }
    }

    public function uploadTmpAction()
    {
        //the user just sent some files
        $user = $this->connectedOnly();
        //pb
        //$this->teacherOnly($user);

        //if the tmp dir doesn't exist, we create it
        $uploadDir = 'Projects/tmp/' .$user->getUsername();
        if(!is_dir($uploadDir))
            mkdir($uploadDir, 0755, true);
        //we move uploaded files to tmp dir
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
                $fileToDelete = 'Projects/' . $_POST['projectid'] . '/' . $_POST['class'] . '.java';
                if(file_exists($fileToDelete)) {
                    $testModel = new TestModel();
                    $testModel->deleteTest($_POST['projectid'], $_POST['class']); // deletes the test in DB
                    unlink($fileToDelete); // deletes the test on the server
                    header($_SERVER["SERVER_PROTOCOL"]." 200 OK : " . $_POST['projectid'] . " " .$_POST['class']);
                }
                else {
                    header($_SERVER["SERVER_PROTOCOL"]." 404 File " . $fileToDelete . " Not Found");
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

    public function newProjectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        // first form was sent
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            //we're going to scan all files in user tmp dir
            $url = 'Projects/tmp/' . $user->getUsername();
            $filenames = scandir($url);

            $filesToProcess = array();
            //for each file to process
            foreach($filenames as $filename)
            {
                $filenameExploded = explode('.', $filename);
                $extension = end($filenameExploded);
                //we verify that the file is really a java one
                if($extension == 'java')
                {
                    //we parse the .java file to extract subtests
                    $file = fopen($url . '/' . $filename, 'r');
                    $content = fread($file, filesize($url . '/' . $filename));
                    fclose($file);
                    preg_match_all('#@Test([\t\n\r\s])+public void (.*?)\(#', $content, $matches);
                    $testFuncs = $matches[2];
                    array_push($filesToProcess, array('class' => $filenameExploded[0], 'subtests' => $testFuncs));
                }
            }
            $this->setSession("tests", serialize($filesToProcess));

            //if all inputs has been filled
            if(isset($_POST['name']) && isset($_POST['duedate']))
            {
                $duedate = DateTime::createFromFormat('d/m/Y H:i', $_POST['duedate']);
                //we create a new project in db
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
            //we clear the user tmp dir
            if(is_dir('Projects/tmp/' . $user->getUsername()))
                self::delTree('Projects/tmp/' . $user->getUsername());
            require 'Views/panel/newproject.php';
        }
    }

    public function projectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);
        //the project id must be set
        if(isset($_GET['id']))
            $projectId = $_GET['id'];
        else
        {
            $this->setFlashError('Mauvais paramètres !');
            header("Location: index.php");
        }
        //we get the project in db and his tests
        $projectModel = new ProjectModel();
        $project = $projectModel->getOneProjectBy($projectId);
        $project = $projectModel->getProjectTests($project); 
        //we get the fee schedule
        $projectTotalWeight = $projectModel->getProjectTotalWeight($project);
        //we get all users that took part in and their results
        $users = $projectModel->getProjectParticipants($project);
        //get participation stats for pie chart
        $userModel = new UserModel();
        $nbUsers = $userModel->getNbUsers();
        //get succesful stats for pie charts
        $stats = $projectModel->getProjectStats($project);

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

        // if we confirm the deletion
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            //the project id must be set
            if(isset($_POST["id"]))
                $projectId = $_POST["id"];
            else
            {
                $this->setFlash('Pas de projet sélectionné');
                header("Location: index.php");
            }
            //we delete the project in db
            $projectModel = new ProjectModel();
            $projectModel->deleteProject($projectId);

            //we delete the dir where tests files are stored
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
        //the project id must be set
        if(isset($_GET["id"]))
        {
            $projectId = $_GET["id"];
            //we get the tests stored in session
            $tests = unserialize($this->getSession("tests"));
            $this->deleteSession("tests");
            $testsArray = array();

            //we get all project tests name in db to avoid duplicates
            $projectModel = new ProjectModel();
            $testNames = $projectModel->getAllTestNames($projectId);

            foreach ($tests as $test)
            {
                //duplicates check
                if(!in_array($test["class"], $testNames))
                {
                    //we create a new test...
                    $newTest = new Test();
                    $newTest->setName($test["class"]);
                    $newTest->setFullname($test["class"] . ":" . $projectId);
                    $newTest->setDescription("description");

                    $subtests = $test['subtests'];
                    //... and his subtests
                    foreach($subtests as $subtest)
                    {
                        $newSubtest = new Subtest();
                        $newSubtest->setName($subtest);
                        $fullname = $subtest . ":" . $test["class"] . ":" . $projectId;
                        $newSubtest->setFullname($fullname);
                        if(isset($_POST[$fullname]))
                            $newSubtest->setWeight($_POST[$fullname]);
                        $newSubtest->setKind("kind");
                        $newTest->addSubtest($newSubtest);
                    }
                    array_push($testsArray, $newTest);

                    //we move now the test class (.java file) to the project test dir
                    $projectDir = "Projects/". $projectId . '/tests';
                    if(!is_dir($projectDir))
                        mkdir($projectDir, 0755, true);
                    copy("Projects/tmp/" . $user->getUsername(). "/" . $test["class"] . ".java", $projectDir . '/' . $test["class"] . ".java");
                }
                else
                {
                    $this->setFlashError("Nom de test déjà utilisé : " . $test["class"]);
                    header("Location: index.php");
                }
                //we delete the java file in tmp dir
                unlink("Projects/tmp/" . $user->getUsername() . "/" . $test["class"] . ".java");
            }
            //if they really are tests to submit
            if(count($testsArray))
            {
                $projectModel = new ProjectModel();
                $projectModel->addTests($projectId, $testsArray);
                $this->setFlash("Les tests ont bien été ajoutés !");
            }
            else
                $this->setFlash("Aucun test ajouté !");

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
        //we get the user and then his results
        $userModel = new userModel();
        $pupil = $userModel->getUserWithProjectResults($username, $projectId);
        
        require 'Views/panel/results.php';
    }

    public function launchTests($projectId, $username)
    {
        //we get the project from the db
        $projectModel = new ProjectModel();
        $project = $projectModel->getOneProjectBy($projectId);
        $project = $projectModel->getProjectTests($project);

        //we check if they aren't any missing files
        $fileMissing = false;
        $testNames = array();
        foreach($project->getTests() as $test)
        {
            if(!file_exists('Projects/' . $projectId . '/tests/' . $test->getName() . '.java'))
                $fileMissing = true;
            array_push($testNames, $test->getName());
        }

        //if they aren't any files missing
        if(!$fileMissing)
        {
            //java compilation
            $output = array();
            //$cmdCompil'javac -cp Lib/hamcrest-core-1.3.jar:Lib/junit-4.11.jar:Lib/jdbc.jar:Lib/mysql-connector-java-5.1.26-bin.jar:Projects:Projects/' . $projectId . '/src/' . $username . ':Projects/' . $projectId . '/tests Projects/Main.java Projects/' . $projectId . '/src/' . $username . '/Money.java 2>&1'
            $cmdCompil = 'javac -cp  ./Lib/*;./Projects;./Projects/'. $projectId .'/src/'. $username . ';./Projects/' . $projectId . '/tests ./Projects/Main.java ./Projects/' . $projectId . '/src/' . $username .'/*.java ./Projects/' . $projectId . '/tests/*.java 2>&1';
            echo $cmdCompil;
            exec($cmdCompil, $output);
            var_dump($output);
            //if errors are caught
            if(count($output) > 0)
            {
                //we specify what are JAVA errors
                $error = 'Erreur JAVA :';
                foreach($output as $outputline)
                {
                    $error .= $outputline;
                }
                $this->setFlashError($error);
                header("Location: index.php?action=userpanel");
            }
            else
            {
                //we launch java programm
                //$cmdLaunch = 'java -cp Lib/hamcrest-core-1.3.jar:Lib/junit-4.11.jar:Lib/jdbc.jar:Lib/mysql-connector-java-5.1.26-bin.jar:Projects:Projects/' . $projectId . '/src/' . $username . ':Projects/' . $projectId . '/tests Main ' . implode(' ', $project->getTests()) . ' 2>&1';
                $cmdLaunch = 'java -cp  ./Lib/*;./Projects;./Projects/' . $projectId . '/src/' . $username . ';./Projects/' . $projectId . '/tests Main ' . $projectId . ' ' . $username . ' ' . implode(' ', $testNames) . ' 2>&1';
                echo $cmdLaunch;
                exec($cmdLaunch, $output);
                //if errors are caught
                if(count($output) > 0)
                {
                    $error = 'Erreur JAVA :';
                    foreach($output as $outputline)
                    {
                        $error .= $outputline;
                    }
                    $this->setFlashError($error);
                    header("Location: index.php?action=userpanel");
                }
                else
                    header("Location: index.php?action=results&projectid=" . $projectId . "&username=" . $username);

            }
        }
        else
        {
            $this->setFlashError('Il manque des fichiers nécessaires à la compilation');
            header("Location: index.php?action=userpanel");
        }
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