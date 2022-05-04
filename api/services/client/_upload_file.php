<?php 

use  libs\app\FileManager as file;

define("UM_MEGA", 1048576);
define("UM_GIGA", 1073741824);

function __upload_save($user, $name, $mime, $file, $client_path, $dirId){
    sleep(1);
    try {
      
        $hashFile = (new file($client_path, $name, $mime))->upload($file)->hash();
        if(!$hashFile) _error(400, "Ocorreu um erro ao tentar salvar o arquivo");
  
        $idInsert = _exec(
            "INSERT INTO 
            file_client(nome, hash_file, mime_type, directory_id)
            VALUES ('$name', '$hashFile', '$mime', $dirId)", true);
      
        if(!$idInsert) _error(400, "Ocorreu um erro ao tentar salvar o arquivo");
        
        _exec(
            "INSERT INTO 
            file_client_info (file_client_id, created_user_id, createdAt)
            VALUES ($idInsert, $user->id, now())");

        return _response([
            'type'   => 'file',
            'hashId' => $hashFile,
            'nome'   => $name,
            'ext'    => $mime,
            'thumb'  => _get_thumb($user->session(), $mime,  $client_path, $hashFile),
            'novo'   => true,
            'options'=> true,
            'publico'=> false
        ]);
  
    } catch(Exception $e){
        return _error(400, $e->getMessage());
    }

}


function __upload_create($client_path, $name, $mime, $dirId){

    return 
    ($hashId = (new file($client_path, $name, $mime))->create_ghost_file($dirId)->hash()) 
    ? _response([
        "hashId" => $hashId
    ])
    : _error(404,'');

}

function __upload_append($client_path, $file, $data){
    return file::append($client_path, $file, $data);
}

function __upload_commit($user, $hashId, $mime, $client_path){
    if(!file::commit($hashId, $mime, $client_path)) _error(500, "Server Error");
    
    $query = _query(
        "SELECT 
            file_client.nome,
            file_client.hash_file as hashId,
            file_client.mime_type as ext,
            file_client.public    as publico,
            (CASE WHEN(true) THEN 'file' END) as `type`
        FROM file_client
            WHERE ghost = 0 
              AND file_client.hash_file = '$hashId' 
        "
    );

    if($query->rowCount() == 0) _error(500, 'Server Error');

    $file            = $query->fetchAssoc();
    $file['thumb']   = _get_thumb($user->session(), $mime,  $client_path, $hashId);
    $file['novo']    = true;
    $file['options'] = true;
    $file['publico'] = false;

    return _response($file);
}