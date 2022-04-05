<?php 

namespace libs\app;

use libs\app\Response as Response; 
use src\Request as request;

class ResponseFactory {

    public static function genResponseByFunc($func){
        
        $refFunction = new ReflectionFunction($func);
        $parameters = $refFunction->getParameters();
        $vars = request::raw();

        $validParameters = [];

        foreach ($parameters as $parameter) {
            $exists = array_key_exists($parameter->getName(), $vars);
            $type = $parameter->getType() ? $parameter->getType() : 'mixed';
            if (!$exists && !$parameter->isOptional()) _error(400, 'Bad Request B - there are variables that were not sent and that are not optional');
            if(!$exists) continue;
            $validParameters[$parameter->getName()] = request::clean_value($vars[$parameter->getName()], $type);
        }

        $response = $refFunction->invoke(...$validParameters);
        if(!$response || !($response instanceof Response)) return _response([]);

        return $response;
    }

    

}