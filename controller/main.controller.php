<?php 

class MainController{
    public function showHomePage(){
        require_once __DIR__ . '/../includes/header.html';
        require_once __DIR__ . '/../views/index.html';
        require_once __DIR__ . '/../includes/footer.html';


    }
}