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

function _slug($text, $divider = '-'){
    
    $text = preg_replace('/[-_\t\n]/', ' ', $text);
    $text = preg_replace('/\s{2,}/', ' ', $text);
    
    $list = [
        'Š' => 'S', 'š' => 's', 'Đ' => 'Dj','đ' => 'dj','Ž' => 'Z','ž' => 'z', 'Č' => 'C', '/' => $divider,
        'č' => 'c', 'Ć' => 'C', 'ć' => 'c', 'À' => 'A', 'Á' => 'A','Â' => 'A', 'Ã' => 'A', ' ' => $divider,
        'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E','É' => 'E', 'Ê' => 'E', '.' => $divider,
        'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I','Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O',
        'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U','Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss','à' => 'a', 'á' => 'a','â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e','ê' => 'e', 'ë' => 'e', 'ì' => 'i',
        'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n','ò' => 'o', 'ó' => 'o', 'ô' => 'o',
        'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u','û' => 'u', 'ý' => 'y', 'ý' => 'y',
        'þ' => 'b', 'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r', '!' => $divider, ',' => $divider,
    ];

    $text = strtr($text, $list);
    $text = preg_replace('/-{2,}/', $divider, $text);
    $text = $text[-1] == '-' ? substr($text, 0, -1) : $text;
    $text = $text[0]  == '-' ? substr($text, 1)     : $text;
    $text = strtolower($text);

    return $text;
}