<?php 
require_once 'services/Router.php';
require_once 'controller/main.controller.php';

$router = new Router();
$controller = new MainController();

$router->addRoute('GET', '/home', function () use ($controller){
    $controller->showHomePage();
});

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);