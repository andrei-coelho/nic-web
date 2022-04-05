<?php

namespace libs\app;

class Route {

    private static $routes;
    private $file;

    private function __construct($file){
        $this->file  = $file;
    }

    public static function register($route, $file){
        self::$routes[$route] = new Route("../api/services/".$file.".php");
    }

    public static function get_file($route){
        return isset(self::$routes[$route]) ? self::$routes[$route]->file : false;
    }

    

}