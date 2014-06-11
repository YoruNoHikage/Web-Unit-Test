<?php

require_once 'Controller.php';

class StudentController extends Controller
{
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
            
            if(!$files)
            {
                $this->setFlashError('Aucun fichier n\'a été envoyé !');
                header("Location: index.php?action=userpanel");
            }

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
                    }
                    else
                    {
                        $this->setFlashError('Projet non uploadé');
                    }
                }
                else
                {
                    $this->setFlashError('Projet non uploadé');
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
                    $this->setFlashError('Le projet est clos !');
                    header("Location: index.php?action=userpanel");
                }
            }
            else
            {
                $this->setFlash('Pas de projet sélectionné');
                header("Location: index.php?action=userpanel");
            }
        }
    }
    
    public function launchTests($projectId, $username)
    {
        $conf = parse_ini_file('conf.ini');

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
            if($conf['system'] === 'windows')
            {
                $cmdCompil = 'javac -cp ./Lib/*;./Projects;./Projects/'. $projectId .'/src/'. $username . ';./Projects/' . $projectId . '/tests ./Projects/Main.java ./Projects/' . $projectId . '/src/' . $username .'/*.java ./Projects/' . $projectId . '/tests/*.java 2>&1';
            }
            else
            {
                $cmdCompil = 'javac -cp Lib/hamcrest-core-1.3.jar:Lib/junit-4.11.jar:Lib/jdbc.jar:Lib/mysql-connector-java-5.1.26-bin.jar:Projects:Projects/' . $projectId . '/src/' . $username . ':Projects/' . $projectId . '/tests Projects/Main.java Projects/' . $projectId . '/src/' . $username . '/Money.java 2>&1';
            }
            exec($cmdCompil, $output);

            //if errors are caught
            if(count($output) > 0)
            {
                //we specify what are JAVA errors
                $error = 'Erreur JAVA : ';
                foreach($output as $outputline)
                {
                    $error .= $outputline;
                }
                $this->setFlashError($error);
                header("Location: index.php?action=userpanel");
            }
            else
            {
                //we launch java program
                if($conf['system'] === 'windows')
                {
                    $cmdLaunch = 'java -cp ./Lib/*;./Projects;./Projects/' . $projectId . '/src/' . $username . ';./Projects/' . $projectId . '/tests Main ' . $projectId . ' ' . $username . ' ' . implode(' ', $testNames) . ' 2>&1';
                }
                else
                {
                    $cmdLaunch = 'java -cp Lib/hamcrest-core-1.3.jar:Lib/junit-4.11.jar:Lib/jdbc.jar:Lib/mysql-connector-java-5.1.26-bin.jar:Projects:Projects/' . $projectId . '/src/' . $username . ':Projects/' . $projectId . '/tests Main ' . implode(' ', $project->getTests()) . ' 2>&1';

                }
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
            $this->setFlashError('Il manque des fichiers nécessaires à la compilation !');
            header("Location: index.php?action=userpanel");
        }
    }
}