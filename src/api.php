<?php 

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

use libs\app\Config as Config;
use libs\app\Route as Route;
use libs\app\User as user;


if(!$request->vars['route'] || !$request->vars['func']) _error(400, 'Bad Request A');


/**
 * Inclui o arquivo pela rota
 */
include "../api/routes.php";
$file = Route::get_file($request->vars['route']);
if(!$file || !file_exists($file)) _error(404, 'Not Found - A');
include $file; // arquivo incluido aqui


/**
 * Se não for uma requisição pública é necessário gerar um usuário
 * e verificar se ele tem as credenciais necessárias
 */
if(!_is_public()){
    
    if((!$request->vars['session'] || $request->vars['session'] == 'null') && !$request->vars['secret'])
        _error(401, 'Unauthorized - A'); 
    
    $user = $request->vars['secret'] ? 
    user::generate_by_secret($request->vars['secret']) :
    user::generate_by_session($request->vars['session']) ;
    
    if(!$user || !_is_authentic()) _error(401, 'Unauthorized - B');

}


/**
 * Se a função não estiver definida irá gerar um erro 404
 */
$func = $request->vars['func'];
if($func[0] == "_") $func = substr($func, 1, strlen($func) - 1);
if(!function_exists($func)) _error(404, 'Not Found - B'); 


/**
 * Mostra a resposta em JSON
 */
echo (ResponseFactory::genResponseByFunc($func))->response();
