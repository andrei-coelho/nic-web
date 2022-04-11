<?php 

include "../src/Request.php";
$request = new src\Request(['req']);

include "../api/autoload.php";
include "../api/helpers/config.php";

$req = $request->vars['req'] ? $request->vars['req'] : "home";

if(!_is_in_production() && $req != 'api'
    && file_exists(($file = "../src/".$req.".php"))) 
    include $file;

include ($req == 'api' ? "../src/api.php" : (function($req){
    $file = "../pages_html/".$req."_source.html";
    return file_exists($file) ? $file : "../pages_html/error_source.html";
})($req));