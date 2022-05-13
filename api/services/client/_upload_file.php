<?php 

use  libs\app\FileManager as file;

define("UM_MEGA", 1048576);
define("UM_GIGA", 1073741824);
define("MAX",     214748364800);


function ___can_save_file($user, $file){

    $client    = $user->getClientArray();
    $client_id = $client['client_id'];
    $max_byte  = $client['max_byte'];

    return strlen($file) + $total <= $max_byte;
}


function __upload_save($user, $name, $mime, $file, $client_path, $dirId){

    try {
        
        $client    = $user->getClientArray();
        $totalB    = $user->getTotalBytes();
        $client_id = $client['client_id'];
        $max_byte  = $client['max_byte'];

        $obj = (new file($client_id, $client_path, $name, $mime, $max_byte, $totalB))->upload($file);        

        $size = $obj->size();
        $hashFile = $obj->hash();
        if(!$hashFile) _error(500, "Ocorreu um erro ao tentar salvar o arquivo");
        
        $idInsert = _exec(
            "INSERT INTO 
            file_client(nome, hash_file, mime_type, directory_id)
            VALUES ('$name', '$hashFile', '$mime', $dirId)", true);
      
        if(!$idInsert) _error(500, "Ocorreu um erro ao tentar salvar o arquivo");

        __save_tags($name." ".$mime, $idInsert);
        if(!__save_info($idInsert, $user, $size))
            _error(500, 'Server Error - Não foi possível salvar as informações do arquivo');

        return _response([
            'type'   => 'file',
            'hashId' => $hashFile,
            'nome'   => $name,
            'ext'    => $mime,
            'thumb'  => _get_thumb($user->session(), $mime,  $client_path, $hashFile),
            'novo'   => true,
            'options'=> true,
            'publico'=> false,
            'size'   => $size
        ]);
  
    } catch(\Exception $e){
        return _error(400, $e->getMessage());
    }

}


function __upload_create($user, $client_path, $name, $mime, $dirId){

    $client    = $user->getClientArray();
    $totalB    = $user->getTotalBytes();
    $client_id = $client['client_id'];
    $max_byte  = $client['max_byte'];

    if($hashId = 
        (new file($client_id, $client_path, $name, $mime, $max_byte, $totalB))
        ->create_ghost_file($dirId)->hash()){
            __save_tags($name." ".$mime, $hashId);
            return _response([
                "hashId" => $hashId
            ]);
    }

    _error(404,'');
}

function __upload_append($client_path, $file, $data){
    return file::append($client_path, $file, $data);
}

function __upload_commit($user, $hashId, $mime, $client_path){
    
    $client    = $user->getClientArray();
    $totalB    = $user->getTotalBytes();
    $client_id = $client['client_id'];
    $max_byte  = $client['max_byte'];

    try {
        $obj = file::commit($client_id, $hashId, $mime, $client_path, $max_byte, $totalB);
    } catch (\Exception $e) {
        return _error(400, $e->getMessage());
    }

    $query = _query(
        "SELECT 
            file_client.id,
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

    $file = $query->fetchAssoc();

    if(!__save_info($file['id'], $user, $obj->size()))
        _error(500, 'Server Error - Não foi possível salvar as informações do arquivo');

    $file['thumb']   = _get_thumb($user->session(), $mime,  $client_path, $hashId);
    $file['novo']    = true;
    $file['options'] = true;
    $file['publico'] = false;
    $file['size']    = $obj->size();

    return _response($file);
}


function _get_thumb($session, $ext, $client_path, $hashId){
    
    $icon = "public/img/icons/$ext.jpg";
    $file = false;
    if(in_array($ext, ['jpg', 'png', 'jpeg'])){
        
        if(file_exists('../thumbs/'.$client_path.'/'. $hashId.'.'.$ext)){
            $file = _url().'thumbnail/'.$session.'/'.$client_path.'/'. $hashId.'.'.$ext;
        }

        if(file_exists('../thumbs/'.$client_path.'/'. $hashId.'..'.$ext)){
            $file = _url().'thumbnail/'.$session.'/'.$client_path.'/'. $hashId.'..'.$ext;
        }
    }

    return $file
         ? $file
         : (file_exists("../".$icon) 
         ? _url().$icon 
         : _url()."public/img/icons/default.jpg");
}


function __save_tags($name, $hashFileOrId){

    $name  = str_replace([',', '[', ']', '{', '}', '(', ')'], '', $name);
    $name  = str_replace(['.', '-', '_'], ' ', $name);
    $parts = explode(" ", $name);

    if(!is_numeric($hashFileOrId)){
        $query = _query("SELECT id FROM file_client WHERE hash_file = '$hashFileOrId'");
        if($query->rowCount()==0) return;
        $fileId = $query->fetchAssoc()['id'];
    } else {
        $fileId = $hashFileOrId;
    }
    
    $insert = "INSERT INTO file_client_tag (file_client_id, nome) VALUES ";
    foreach ($parts as $tag)
        if(strlen($tag) > 2)
            $insert .= "($fileId,'$tag'),";
        
    $insert = substr($insert, 0, -1);
    _exec($insert);

    return $fileId;

}


function __save_info($hashFileOrId, $user, $size){

    if(!is_numeric($hashFileOrId)){
        $query = _query("SELECT id FROM file_client WHERE hash_file = '$hashFileOrId'");
        if($query->rowCount()==0) return false;
        $fileId = $query->fetchAssoc()['id'];
    } else {
        $fileId = $hashFileOrId;
    }

    return _exec(
        "INSERT INTO 
        file_client_info (file_client_id, created_user_id, createdAt, size_bytes)
        VALUES ($fileId, $user->id, now(), $size)
    ");
}