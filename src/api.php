<?php 

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');


use libs\app\Route as route;
use libs\app\ResponseFactory as ResponseFactory;

(function(){
    
    include "../api/helpers/response.php";
    include "../api/routes.php";

    $request = _request(['req', 'service', 'func']);
    
    if(!$request->vars['service'] || !$request->vars['func']) 
        _error(400, 'Bad Request');

    include "../api/helpers/user.php";
    $user = _user();

    if($user && !$user->isValidSession()) 
        _error(440, 'Session Expired');

    if(!$user && !route::is_public($request->vars['service'], $request->vars['func']))
        _error(401, 'Unauthorized');

    if(!($file = route::get_file($request->vars['service'])) 
    || !(include $file)
    || !function_exists($request->vars['func']))
        _error(404, 'Not Found');
    
    echo (ResponseFactory::genResponseByFunc($request->vars['func']))->response();
    
})();

exit;


