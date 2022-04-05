<?php 

$reqtest  = $request->vars['route'] ? $request->vars['route'] : "error";
$filetest = "../tests/".$reqtest.".php";
include file_exists($filetest) ? $filetest : "../tests/error.php";
die("\n");