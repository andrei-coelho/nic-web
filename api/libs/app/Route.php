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

    public static function is_public($service, $function){
        return _query(
            "SELECT 
                service_function.slug
            FROM service_function
                JOIN service ON service.id = service_function.service_id
                JOIN permission_func ON permission_func.service_function_id = service_function.id
                JOIN permission_pool ON permission_func.permission_pool_id = permission_pool.id
            WHERE 
                service.slug = '$service' AND 
                service_function.slug = '$function' AND
                permission_pool.slug = 'public';"
        )->rowCount() > 0;
    }
    

}