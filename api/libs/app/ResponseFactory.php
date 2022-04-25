<?php 

namespace libs\app;

use libs\app\Response as Response; 

class ResponseFactory {

    public static function genResponseByFunc($func){
        
        $refFunction = new \ReflectionFunction($func);
        $parameters = $refFunction->getParameters();
        $vars = _data();

        $validParameters = [];

        foreach ($parameters as $parameter) {
            $exists = array_key_exists($parameter->getName(), $vars);
            $type = $parameter->getType() ? $parameter->getType() : 'mixed';
            if (!$exists && !$parameter->isOptional()) _error(400, 'Bad Request Vars - there are variables that were not sent and that are not optional');
            if(!$exists) continue;
            $validParameters[$parameter->getName()] = _clean_value($vars[$parameter->getName()], $type);
        }

        $values = array_values($validParameters);

        $response = $refFunction->invoke(...$values);
        if(!$response || !($response instanceof Response)) return _response([]);

        return $response;
    }

    

}