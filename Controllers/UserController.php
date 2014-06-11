<?php

require_once 'Controller.php';

require_once 'Entity/User.php';
require_once 'Entity/Project.php';
require_once 'Entity/Test.php';
require_once 'Entity/Result.php';
require_once 'Entity/Subtest.php';

require 'Model/UserModel.php';
require 'Model/ProjectModel.php';

class UserController extends Controller
{
    
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
            header("Location: index.php?action=userpanel");
        }

        //we get the user and his results
        $userModel = new userModel();
        $pupil = $userModel->getUserWithProjectResults($username, $projectId);

        require 'Views/panel/results.php';
    }
}