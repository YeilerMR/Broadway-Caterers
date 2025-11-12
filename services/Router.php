<?php 

class Router{
    private $routes = [];

    public function addRoute($method, $path, $callback){
        $this->routes[]=[
            'method' => strtoupper($method),
            'path' => $path,
            'callback' => $callback
        ];
    }

    public function dispatch($requesturi, $requestMethod){
        $path = parse_url($requesturi, PHP_URL_PATH);
        //Quitar prefijo
        $path = str_replace('BC', '', $path);

        if($path === '/index.php'){
            $path = '/';
        }

        foreach($this->routes as $route) {
            if ($route['method'] === strtoupper($requestMethod) && $route['path'] === $path) {
                return call_user_func($route['callback']);
            }
        }

        http_response_code(404);
        require_once ''
    }
}