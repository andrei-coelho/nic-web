<?php 

include "../api/helpers/request.php";
include "../api/autoload.php";
include "../api/helpers/config.php";

$request = _request(['req']);
$req = $request->vars['req'] ? $request->vars['req'] : "home";

if(!_is_in_production() && $req != 'api'
    && file_exists(($file = "../src/".$req.".php"))) 
    include $file;

include ($req == 'api' || $req == 'tasks' ? "../src/$req.php" : (function($req){
    $file = "../pages_html/".$req."_source.html";
    return file_exists($file) ? $file : "../pages_html/error_source.html";
})($req));