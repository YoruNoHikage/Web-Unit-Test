<?php
require_once 'Entity/User.php';
require_once 'Entity/Test.php';
require_once 'Entity/Subtest.php';
require_once 'Entity/Result.php';
require_once 'Entity/Project.php';

require 'Model/UserModel.php';
require 'Model/ProjectModel.php';

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
        var_dump($_SESSION);
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
        $this->connectedOnly();
            
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $this->setFlash('Le projet a bien été envoyé !');
            header("Location: index.php?action=userpanel");
        }
        
        require 'Views/panel/uploadsources.php';
    }

    public function uploadTmpAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        $uploadDir = 'Projects/tmp/' .$user->getUsername();
        if(!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $uploadFile = $uploadDir . '/' . basename($_FILES['file']['name']);

        move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile);
    }

    public function newProjectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

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
                $testFuncs = $matches[2];
                array_push($filesToProcess, array('class' => $filenameExploded[0], 'subtests' => $testFuncs));
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') // first form was sent
        {
            //on verif que les champs ont bien ete remplis
            if(isset($_POST['name']) && isset($_POST['due_date']))
            {
                $projectModel = new ProjectModel(); 
                $project = new Project();
                $project->setName($_POST['name']);
                $project->setEnabled(1);
                $project->setDue_date($_POST['due_date']);
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
            require 'Views/panel/newproject.php';
    }
    
    // fired with the second new form
    public function createProjectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);
            
        if($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: index.php?action=newproject");
            
        $this->setFlash('Le projet a bien été ajouté');
        
        header("Location: index.php?action=userpanel");        
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

        $participants = $projectModel->getProjectParticipants($project);

        require 'Views/panel/project.php';
    }
    
    public function editProjectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);
            
        require 'Views/panel/editproject.php';
    }
    
    public function deleteProjectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);
            
        if($_SERVER['REQUEST_METHOD'] == 'POST') // if we confirm the deletion
        {
            // delete action
            $this->setFlash('Le projet a bien été supprimé !');
            header("Location: index.php?action=userpanel");
        }
        else
            require 'Views/panel/deleteproject.php';
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
}