<?php 
include "../src/Request.php";
use src\Request as request;

function _request(array $arr){
    return new request($arr);
}

function _data($key = false){
    $raw = request::raw();
    if(!$key) return $raw;
    return $key && isset($raw[$key]) ? $raw[$key] : false;
}

function _clean_value($value, $type = 'mixed'){
    return request::clean_value($value, $type);
}

function _slug($text, $divider){
  $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
  $text = preg_replace('~[^-\w]+~', '', $text);
  $text = trim($text, $divider);
  $text = preg_replace('~-+~', $divider, $text);
  $text = strtolower($text);
  return $text;
}