<?php 

(function(){

    include "../api/helpers/user.php";
    $userA = _user_file();
    if(!$userA || !$userA[0]->isValidSession()) 
        exit("Arquivo não existe");

    $file = '../thumbs/'.$userA[1];
    if(!file_exists($file)) exit("Arquivo não existe");

    $imginfo = getimagesize($file);
    header("Content-type: {$imginfo['mime']}");
    readfile($file);

    exit();

})();