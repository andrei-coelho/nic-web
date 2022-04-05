<?php 

if(!$request->vars['route'] || trim($request->vars['route']) == '')
    die("Error: file name is empty");
    
die("Error: file '".$request->vars['route']."' dont exists");