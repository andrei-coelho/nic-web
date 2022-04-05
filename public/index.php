<?php 

include "../src/Request.php";
$request = new src\Request(['req', 'route', 'func', 'session']);

if($request->vars['req'] != 'api') {
    $req = $request->vars['req'] ? $request->vars['req'] : "home";
    $file = $request->vars['req']."_source.html";
    include (file_exists($file) ? $file : "error_404_source.html");
    exit;
}

include "../api/autoload.php";
include "../api/helpers/config.php";
include "../api/helpers/response.php";
include "../api/helpers/sqli.php";
include "../api/helpers/user.php";
include "../api/helpers/session.php";

if(!_is_in_production() && $request->vars['req'] == 'test') include "../src/test.php";

include "../src/api.php";