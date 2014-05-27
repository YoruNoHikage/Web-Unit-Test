<?php

require_once 'Controller.php';

class Router 
{
    
    private $controller;

    public function __construct()
    {
        $this->controller = new Controller();
    }

    public function route()
    {
        try
        {
            if (isset($_GET['action']))
            {
                $method = $_GET['action'] . 'Action';
                if (method_exists($this->controller, $method))
                {
                    call_user_func_array(array($this->controller, $method), array());
                }
                else
                    throw new Exception("Action non valide");
            }
            else 
            {
                $this->controller->indexAction();
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
