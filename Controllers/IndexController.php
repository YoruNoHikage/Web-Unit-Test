<?php
require_once 'Controller.php';

class IndexController extends Controller
{

    public function indexAction()
    {
        require 'Views/index.php';
    }

}