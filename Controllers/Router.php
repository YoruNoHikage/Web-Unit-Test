<?php

require_once 'IndexController.php';
require_once 'UserController.php';
require_once 'StudentController.php';
require_once 'TeacherController.php';

class Router 
{
    
    private $controllers;

    public function __construct()
    {
        $this->controllers = array(new IndexController(), 
                                   new UserController(),
                                   new StudentController(),
                                   new TeacherController());
    }

    public function route()
    {
        try
        {
            if (isset($_GET['action']))
            {
                $method = $_GET['action'] . 'Action';
                $actionPerformed = false;
                foreach($this->controllers as $controller)
                {
                    if (method_exists($controller, $method))
                    {
                        $actionPerformed = true;
                        call_user_func_array(array($controller, $method), array());
                        break;
                    }
                }
                
                if(!$actionPerformed)
                {
                    throw new Exception("Action non valide");
                }
            }
            else 
            {
                $this->controllers[0]->indexAction();
            }
        }
        catch (Exception $e)
        {
            $this->error($e->getMessage());
        }
    }

    // Affiche une erreur
    private function error($message)
    {
        echo "argh " . $message;
    }
}
