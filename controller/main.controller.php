<?php 

class MainController{
    public function showHomePage(){
        $this->render('index');
    }
    public function showAboutUsPage(){
        $this->render('about');
    }
    public function showServicesPage(){
        $this->render('services');
    }
    public function showLicensingPage(){
        $this->render('licensing');
    }
    public function showContactPage(){
        $this->render('contact');
    }

    private function render($viewName){
        require_once __DIR__ . '/../includes/header.html';
        require_once __DIR__ . '/../views/' . $viewName . '.html';
        require_once __DIR__ . '/../includes/footer.html';
    }
}