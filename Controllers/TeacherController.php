<?php

require_once 'Controller.php';

require_once 'Model/TestModel.php';
require_once 'Model/GroupModel.php';

class TeacherController extends Controller
{
    public function csvExportAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);
        
        //the project id must be set
        if(!isset($_GET['id']))
            return;
        else
            $projectId = $_GET['id'];
        
        //we get the project in db and his tests
        $projectModel = new ProjectModel();
        $project = $projectModel->getOneProjectBy($projectId);
        $project = $projectModel->getProjectTests($project); 
        //we get the fee schedule
        $projectTotalWeight = $projectModel->getProjectTotalWeight($project);
        //we get all users that took part in and their results
        $users = $projectModel->getProjectParticipants($project);
        
        $content = array(array('Résultat du projet : ' . $project->getName()), 
                         array('nom', 'prénom', 'note'));
        
        foreach($users as $user)
        {
            array_push($content, array($user->getFirstName(), 
                                       $user->getLastName(), 
                                       $user->getFinalMark($project) . '/' . $projectTotalWeight));
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=test.csv');

        $out = fopen('php://output', 'w');
        foreach ($content as $fields)
        {
            fputcsv($out, $fields);
        }

        fclose($out);
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
        
        // we need the groups
        $groupModel = new GroupModel();
        $groups = $groupModel->getAllGroups();

        if($_SERVER['REQUEST_METHOD'] == 'POST') // first form was sent
        {
            $filesToProcess = $this->serializeTests($user);
            $this->setSession("tests", serialize($filesToProcess));

            //if all inputs has been filled
            if(isset($_POST['name']) && isset($_POST['duedate']))
            {
                $duedate = DateTime::createFromFormat('d/m/Y H:i', $_POST['duedate']);
                $group = new Group();
                $group->setName($_POST['group']);

                //we create a new project in db
                $projectModel = new ProjectModel(); 
                $project = new Project();
                $project->setName($_POST['name']);
                $project->setEnabled(1);
                $project->setDue_date($duedate);
                $project->setOwner($user);
                $project->setTargetGroup($group);
                $projectModel->newProject($project);

                if(count($filesToProcess) == 0)
                {
                    header("Location: index.php?action=addcustomtest&id=" . $project->getId());
                }
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
            // we need the groups
            $groupModel = new GroupModel();
            $groups = $groupModel->getAllGroups();
            
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

                if(isset($_POST['name']) && isset($_POST['duedate']) && isset($_POST['group']))
                {
                    $duedate = DateTime::createFromFormat('d/m/Y H:i', $_POST['duedate']);
                    $group = new Group();
                    $group->setName($_POST['group']);

                    $project->setId($_POST['projectid']);
                    $project->setName($_POST['name']);
                    $project->setDue_date($duedate);
                    $project->setTargetGroup($group);

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
                        $newSubtest->setName($subtest['name']);
                        $fullname = $subtest['name'] . ":" . $test["class"] . ":" . $projectId;
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
            //if they really are tests to submit
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

    public function addCustomTestAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        if(isset($_GET['id']))
        {
            $projectId = intval($_GET['id']);
            if($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                $fileContent = "";
                $fileContent .= "import static org.junit.Assert.*;\n
                                import org.junit.After;\n
                                import org.junit.Before;
                                import org.junit.Test;


                                public class ";
                $fileContent .= $_POST['testName'];
                $fileContent .= "{
                    ";
                $fileContent .= $_POST['declarations'];
                $fileContent .= "@Before
                                public void setUp() throws Exception {
                    ";
                $fileContent .= $_POST['beforeTestContent'];
                $fileContent .= "}
                                @After
                                public void tearDown() throws Exception {
                    ";
                $fileContent .= $_POST['afterTestContent'];
                $fileContent .= "}
                                @Test
                                public void ";
                $fileContent .= $_POST['subtestName'];
                $fileContent .= "() { 
                    ";
                $fileContent .= $_POST['subtestContent'];
                $fileContent .= "}
                    }";

                $path = 'Projects/' . $projectId . '/tests';
                
                if(!is_dir($path))
                    mkdir($path, 0755, true);

                if(!file_exists($path . '/' . $_POST['testName'] . '.java'))
                {
                    file_put_contents($path . '/' . $_POST['testName'] . '.java', $fileContent);
                    $newTest = new Test();
                    $newTest->setName($_POST['testName']);
                    $newTest->setFullname($_POST['testName'] . ":" . $projectId);
                    $newTest->setDescription("description");
                    
                    $newSubtest = new Subtest();
                    $newSubtest->setName($_POST['subtestName']);
                    $fullname = $_POST['subtestName'] . ":" . $_POST["testName"] . ":" . $projectId;
                    $newSubtest->setFullname($fullname);
                    $newSubtest->setWeight($_POST['weight']);
                    $newSubtest->setKind("kind");
                    $newTest->addSubtest($newSubtest);

                    $projectModel = new ProjectModel();
                    $projectModel->addTests($projectId, array($newTest));

                    $this->setFlash('Le test a été créé');
                    header("Location: index.php?action=userpanel");
                }
                else
                {
                    $this->setFlashError('Un test de ce nom existe déjà !');
                    header("Location: index.php?action=userpanel");
                }
            }
            else
            {
                require 'Views/panel/customTest.php';
            }
        }
        else
        {
            $this->setFlashError('Pas de projet sélectionné !');
            header("Location: index.php");
        }
    }
}