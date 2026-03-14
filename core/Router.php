<?php

class Router {
    private array $routes = [];
    private $basePath = 'learn4kids/';

    

    public function post($uri, $action) {
        $this->routes['POST'][$uri] = $action;
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace($this->basePath, '', $uri);
        $uri[strlen($uri)-1] = $uri[strlen($uri)-1]=='/' ? ' ' : $uri[strlen($uri)-1];
        $uri = trim($uri);
        
        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Route not found']);
            return;
        }

        list($controller, $methodName) = explode('@', $this->routes[$method][$uri]);
        /*echo __DIR__ . "/../modules/" . strtolower(str_replace('Controller','',$controller)) . "/$controller.php";
        echo "=======$controller=====";*/
        require_once __DIR__ . "/../modules/" . strtolower(str_replace('Controller','',$controller)) . "/$controller.php";

        $instance = new $controller();
        $instance->$methodName();
    }
}