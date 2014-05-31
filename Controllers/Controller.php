<?php

require 'Model/UserModel.php';

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
        if($user->getRole != 'teacher') // you have to be a teacher
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
        //$this->connectedOnly();        
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

    public function newProjectAction()
    {
        $user = $this->connectedOnly();
        $this->teacherOnly($user);

        if($_SERVER['REQUEST_METHOD'] == 'POST') // first form was sent
        {
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