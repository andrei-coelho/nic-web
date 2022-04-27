<?php 

(function(){

    include "../api/helpers/user.php";
    $userA = _user_file();
    if(!$userA || !$userA[0]->isValidSession()) 
    exit("Arquivo não existe");

    $file = '../files_to_upload/'.$userA[1];
    if(!file_exists($file)) exit("Arquivo não existe");

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: 0");
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Content-Length: ' . filesize($file));
    header('Pragma: public');

    flush();

    readfile($file);

    exit();
})();