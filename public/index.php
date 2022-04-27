<?php 

include "../api/helpers/request.php";
include "../api/autoload.php";
include "../api/helpers/config.php";

$request = _request(['req']);
$req = $request->vars['req'] ? $request->vars['req'] : "home";
$dev_modules = ['devtool', 'test'];

if(!_is_in_production() && in_array($req, $dev_modules)
    && file_exists(($file = "../src/".$req.".php"))) 
    include $file;

include (file_exists("../src/$req.php") && !in_array($req, $dev_modules)  ? "../src/$req.php" : (function($req){
    $file = "../pages_html/".$req."_source.html";
    return file_exists($file) ? $file : "../pages_html/error_source.html";
})($req));