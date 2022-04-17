<?php 
include "../src/Request.php";
use src\Request as request;

function _request(array $arr){
    return new request($arr);
}

function _data($key = false){
    $raw = request::raw();
    return $key && isset($raw[$key]) ? $raw[$key] : false;
}

function _clean_value($value, $type = 'mixed'){
    return request::clean_value($value, $type);
}