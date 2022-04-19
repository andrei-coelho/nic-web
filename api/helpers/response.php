<?php 

use libs\app\Response as Response;

function _error($code = 404, $message = "Not Found"){
    $resp = new Response([], $code, $message);
    echo $resp->response();
    die();
}

function _response(array $data = [], $message = '', $session = false){
    $response = new Response($data, 200, $message);
    if($session) $response->setSession($session);
    if(_user()) $response->setSession(_user()->session());
    return $response;
}