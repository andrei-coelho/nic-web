<?php 

include "ServiceFactory.php";
include "FileService.php";

use auto_save\ServiceFactory  as Factory;


$files = get_files_service('../api/services', [], '');
$services = [];

foreach ($files as $file) {
    register_file($file);
}

Factory::commit();

function get_files_service($dir, $res = []){
    
    // pega os arquivos dos serviços a serem analisados
    $scan = scandir($dir);

    foreach ($scan as $key => $value){

        if (!in_array($value, [".",".."])){

            $d = $dir . DIRECTORY_SEPARATOR . $value;
            
            if (is_dir($d)){
                $res = get_files_service($d, $res);
                continue;
            }

            $res[] =  $d;
        }
    }

    return $res;
}

function register_file($file){
    
    $content = file_get_contents($file);
    $tokens  = token_get_all($content);

    $servcTk = false;
    $functTk = [];

    foreach ($tokens as $token) {
        if(T_DOC_COMMENT == $token[0]){
            if(!$servcTk) {
                $servcTk = $token[1];
                continue;
            }
            $functTk[] = $token[1];
        }
    }


    if(!$servcTk) return false;

    Factory::genServiceByToken($file, $servcTk, $functTk);
    
}