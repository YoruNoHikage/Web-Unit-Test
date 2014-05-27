<?php
   
   require_once 'Controllers/Router.php';
   
   session_start();
   
   $router = new Router();
   $router->route();