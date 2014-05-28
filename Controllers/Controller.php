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
    
    public function indexAction()
    {
        require 'Views/index.php';
    }

    public function signInAction()
    {
        if($this->getSession('username')) // if already connected
        {
            echo $this->getSession('username');
            $this->setFlashError('Vous êtes déjà connecté !');
            header("Location: index.php");
        }
        
        $usermodel = new UserModel();
        $user = $usermodel->getOneUserBy($_POST['username']);
        if($user != null)
        {
            $this->setSession('username', $user->getUsername());
            $this->setSession('role', $user->getRole());
            $this->setSession('group', '');
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
        if(!$this->getSession('username')) // you have to be connected
        {
            $this->setFlashError('Vous devez être connecté !');
            header("Location: index.php");
        }
            
        require 'Views/panel/index.php';
    }
    
    public function uploadSourcesAction()
    {
        if(!$this->getSession('username')) // you have to be connected
        {
            $this->setFlashError('Vous devez être connecté !');
            header("Location: index.php");
        }
            
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $this->setFlash('Le projet a bien été envoyé !');
            header("Location: index.php?action=userpanel");
        }
        
        require 'Views/panel/uploadsources.php';
    }

    public function newProjectAction()
    {
        if(!$this->getSession('username')) // you have to be connected
        {
            $this->setFlashError('Vous devez être connecté !');
            header("Location: index.php");
        }

        if($this->getSession('role') != 'teacher') // you have to be a teacher
        {
            $this->setFlashError('Vous n\'avez pas les droits nécessaires !');
            header("Location: index.php");
        }

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
        if(!$this->getSession('username')) // you have to be connected
        {
            $this->setFlashError('Vous devez être connecté !');
            header("Location: index.php");
        }
        
        if($this->getSession('role') != 'teacher') // you have to be a teacher
        {
            $this->setFlashError('Vous n\'avez pas les droits nécessaires !');
            header("Location: index.php");
        }
            
        if($_SERVER['REQUEST_METHOD'] != 'POST')
            header("Location: index.php?action=newproject");
            
        $this->setFlash('Le projet a bien été ajouté');
        
        header("Location: index.php?action=userpanel");        
    }
    
    public function projectAction()
    {
        if(!$this->getSession('username')) // you have to be connected
        {
            $this->setFlashError('Vous devez être connecté !');
            header("Location: index.php");
        }
        
        if($this->getSession('role') != 'teacher') // you have to be a teacher
        {
            $this->setFlashError('Vous n\'avez pas les droits nécessaires !');
            header("Location: index.php");
        }
            
        require 'Views/panel/project.php';
    }
    
    public function editProjectAction()
    {
        if(!$this->getSession('username')) // you have to be connected
        {
            $this->setFlashError('Vous devez être connecté !');
            header("Location: index.php");
        }
        
        if($this->getSession('role') != 'teacher') // you have to be a teacher
        {
            $this->setFlashError('Vous n\'avez pas les droits nécessaires !');
            header("Location: index.php");
        }
            
        require 'Views/panel/editproject.php';
    }
    
    public function deleteProjectAction()
    {
        if(!$this->getSession('username')) // you have to be connected
        {
            $this->setFlashError('Vous devez être connecté !');
            header("Location: index.php");
        }
        
        if($this->getSession('role') != 'teacher') // you have to be a teacher
        {
            $this->setFlashError('Vous n\'avez pas les droits nécessaires !');
            header("Location: index.php");
        }
            
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