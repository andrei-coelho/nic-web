<?php 

use src\Request as request;

(function(){

    $request = new request(['req', 'file']);

    // includes aqui...
    // ou dentro dos testes

    $filetest = "../tests/".($request->vars['file'] ? $request->vars['file'] : "error").".php";
    include file_exists($filetest) ? $filetest : "../tests/error.php";

})($request);

die("\n");