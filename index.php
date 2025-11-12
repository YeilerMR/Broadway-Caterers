<?php 
require_once 'services/Router.php';
require_once 'controller/main.controller.php';

$router = new Router();
$controller = new MainController();

$router->addRoute('GET', '/home', function () use ($controller){
    $controller->showHomePage();
});

$router->addRoute('GET', '/', function () use ($controller){
    $controller->showHomePage();
});
$router->addRoute('GET', '/about-us', function () use ($controller){
    $controller->showAboutUsPage();
});
$router->addRoute('GET', '/service', function () use ($controller){
    $controller->showServicesPage();
});
$router->addRoute('GET', '/licensing', function () use ($controller){
    $controller->showLicensingPage();
});
$router->addRoute('GET', '/contact', function () use ($controller){
    $controller->showContactPage();
});

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);