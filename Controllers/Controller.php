<?php

require_once 'Entity/User.php';

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