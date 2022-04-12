<?php 

spl_autoload_register(function($class) {

    $file = "../api/".str_replace("\\", "/", $class).".php";
        
    if(file_exists($file) && !class_exists($class)){
        include $file;
        return;
    } 

    throw new Exception("O arquivo $file não existe", 1);

});