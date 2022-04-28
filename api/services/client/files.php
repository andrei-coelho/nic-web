<?php 
/**
 * @service: files
 */

use  libs\app\FileManager as file;


function _is_client_folder($hash_dir, $client_id){
    $dirSel = _query(
        "SELECT id FROM directory 
        WHERE hash_dir = '$hash_dir' 
        AND client_id = $client_id");
  
    if($dirSel->rowCount()==0) return false;
    return $dirSel->fetchAssoc()['id'];
}

/**
 * @function:move_file
 * @pool:manage_files
 */
function move_file($hash_file, $hash_dir, $client_id = 0){
    
    $user =  _user();
    if($user->is_client()){
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
    } 

    $table = "file_client";
    $q = _query("SELECT id FROM $table WHERE hash_file = '$hash_file'");
    if($q->rowCount()==0){
        $table = "directory";
        $q = _query("SELECT id FROM $table WHERE hash_dir = '$hash_file' AND visible = 1");
        if($q->rowCount()==0) 
        _error();
    }

    $idFile = $q->fetchAssoc()['id'];

    $query = _query("SELECT id FROM directory WHERE hash_dir = '$hash_dir' AND visible = 1");
    if($query->rowCount()==0)_error();

    $id = $query->fetchAssoc()['id'];

    if(!_exec("UPDATE $table SET directory_id = $id WHERE id = $idFile")) _error(500, "Server Error");

}

/**
 * @function: delete_file
 * @pool:manage_files
 */
function delete_file($hash_file, $type, $client_id = 0){
    
    $user =  _user();
    if($user->is_client()){
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
    }
        
    if($type == 'dir'){
        
        $dirsQ  = _query("SELECT directory.id, directory.hash_dir, directory.directory_id FROM directory WHERE client_id = $client_id");
        $dirs   = $dirsQ->fetchAllAssoc();
        $dirIdx = false;
        $ids    = [];
        
        foreach ($dirs as $dir) {
            if($dir['hash_dir'] ==$hash_file){
                $dirIdx = true;
                $ids[]  = $dir['id'];
                break;
            }
        }
        
        $continue = 1;
        if($dirIdx)
        while($continue > 0){
            $continue = 0;
            foreach ($dirs as $dir) {
                if(in_array($dir['directory_id'], $ids) && !in_array($dir['id'], $ids)){
                    $ids[] = $dir['id'];
                    $continue++;
                }
            }
        }
        // pegar todos os arquivos dos diretórios
        $indirs = implode(',', $ids);
        $sfiles = _query("SELECT id, hash_file, saved, mime_type FROM file_client WHERE directory_id IN (".$indirs.")");
        if($sfiles->rowCount() > 0){
            $files = $sfiles->fetchAllAssoc();
            _exec("DELETE FROM directory WHERE id IN (".$indirs.")");
            foreach ($files as $file) {
                _remove_file($client_path,$file);
            }
        }

    } else {

        $sfiles = _query("SELECT id, hash_file, saved, mime_type FROM file_client WHERE hash_file = '$hash_file'");
        if($sfiles->rowCount() > 0){
            $file = $sfiles->fetchAssoc();
            _exec("DELETE FROM file_client WHERE id = ".$file['id']);
            _remove_file($client_path,$file);
        }
    }

}


function _remove_file($client_path, $file){

    if($file['saved'] == 0){
        $fileRoute = $client_path.'/'.$file['hash_file'].".".$file['mime_type'];
        $fileToUp  = '../files_to_upload/'.$fileRoute;
        $fileThumb = '../thumbs/'.$fileRoute;
        if(file_exists($fileToUp))unlink($fileToUp);
        if(file_exists($fileThumb))unlink($fileThumb);
    } else {
        // coloca os arquivos em epera para serem deletados 
        // no repositório do dropbox
    }
    
}

/**
 * @function:edit_file_name
 * @pool:manage_files
 */
function edit_file_name($hash_file, $nome, $client_id = 0){
    $user =  _user();
    if($user->is_client())
        $client_id = $user->getClientArray()['client_id'];

    if(!_exec(
        "UPDATE file_client 
        SET nome = '$nome'
        WHERE hash_file = '$hash_file'
    ")) _error(500, 'Server error');

    return _response([], "Nome do arquivo alterado");
}


/**
 * @function:create_ghost_file
 * @pool:manage_files
 */
function create_ghost_file($hash_dir, $client_id = 0){
    
    $user = _user();

    if($user->is_client())
        $client_id = $user->getClientArray()['client_id'];

    try {
        if(!($hash = file::create_ghost_file($hash_dir, $client_id)))
            _error(500, "Ocorreu um erro no servidor ao tentar gerar o arquivo");
        return _response(['hash' => $hash]);
    }catch(\Exeption $e){
        _error(400, $e->getMessage());
    }
   
}

/**
 * @function:get_tags
 * @pool:manage_files
 */
function get_tags($hash_file, $client_id = 0){
    
    $user = _user();
    
    if($user->is_client())
        $client_id = $user->getClientArray()['client_id'];

    $sel = _query(
    "SELECT 
            file_client_tag.id as tag_id,
            file_client_tag.nome
        FROM file_client_tag
        JOIN file_client ON file_client_tag.file_client_id = file_client.id
        JOIN directory ON directory.id = file_client.directory_id
        JOIN client ON directory.client_id = client.id
    WHERE hash_file = '$hash_file' AND client_id = $client_id");

    if($sel->rowCount()==0) _error(404,'');
    return _response($sel->fetchAllAssoc());
    
}

/**
 * @function:remove_tag
 * @pool:manage_files
 */
function remove_tag($hash_file, $tag_id, $client_id = 0){
    $user = _user();
    
    if($user->is_client())
        $client_id = $user->getClientArray()['client_id'];

    if(!_exec("DELETE FROM file_client_tag WHERE id = $tag_id"))
        _error(500, 'server error');
}


/**
 * @function:add_tag
 * @pool:manage_files
 */
function add_tag($hash_file, $tag, $client_id = 0){
    
    $user = _user();
    
    if($user->is_client())
        $client_id = $user->getClientArray()['client_id'];

    $selFile = _query(
        "SELECT  file_client.id 
           FROM  file_client 
           JOIN  directory ON directory.id = file_client.directory_id
          WHERE  hash_file = '$hash_file'
            AND  directory.client_id = $client_id
        ");

    if($selFile->rowCount()==0)_error(500,'Server Error');
    $idFile = $selFile->fetchAssoc()['id'];

    if(!($idTag = _exec(
        "INSERT INTO 
        file_client_tag (file_client_id, nome)
        VALUES ($idFile, '$tag')
    ", true))) _error(500,'Server Error');

    return _response(["tag_id"=>$idTag]);
}



/**
 * @function:add_folder
 * @pool:manage_files
 */
function add_folder($name, $hash_dir = "", $client_id = 0){

    $user = _user();
  
    if($user->is_client()){
      
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
        
    } else {
        $pathSel = _query(
            "SELECT directory.hash_dir
            FROM directory
            JOIN client_path ON directory.id = client_path.directory_id
            JOIN client ON client.id = client_path.client_id
            WHERE client.id = $client_id");
        if($pathSel->rowCount() == 0) _error();
        $client_path = $pathSel->fecthAssoc()['hash_dir'];
    }
    
    if($hash_dir == "") $hash_dir = $client_path;
        
    if(!($dirId = _is_client_folder($hash_dir, $client_id))) 
    _error(401, "Não autorizado");

    $hash = _unique_hash($name);

    if(!_exec("INSERT INTO 
                directory(directory_id, nome, hash_dir, client_id)
                VALUES ($dirId, '$name', '$hash', $client_id)"))
        _error(500, "Server Error");
    
    return _response([
        'hashId' => $hash,
        'nome'   => $name,
        'type'   => 'dir',
        'novo'   => true
    ]);

}

/**
 * @function:file_info
 * @pool:manage_files
 */
function file_info($hash_file, $client_id = 0){
    
    $user = _user();
  
    if($user->is_client()){
        $client_id = $user->getClientArray()['client_id'];
    }

    $sel = _query(
        "SELECT 
            file_client_info.createdAt,
            file_client_info.editedAt,
            user.slug
        FROM file_client_info
            JOIN file_client ON file_client.id = file_client_info.file_client_id
            JOIN directory ON file_client.directory_id = directory.id
            JOIN client ON directory.client_id = client.id
            JOIN user ON file_client_info.created_user_id = user.id
        WHERE client.id = $client_id 
        AND file_client.hash_file = '$hash_file';");

    if($sel->rowCount()==0)_error(404, '');
    
    $infoS = $sel->fetchAssoc();

    $infof = [
        'createdAt' => date('d/m/Y H:i:s', strtotime($infoS['createdAt'])),
        'editedAt'  => $infoS['editedAt'] != "" ? date('d/m/Y H:i:s', strtotime($infoS['editedAt'])) : "",
        'createdBy' => $infoS['slug']
    ];

    return _response($infof);

}

function update_file(){
    
}

/**
 * @function:save_file
 * @pool:manage_files
 */
function save_file($name, $mime, $file, $hash_dir = "", $client_id = false){
    
    $user = _user();
  
    if($user->is_client()){
      
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
        
    } else {
        $pathSel = _query(
            "SELECT directory.hash_dir
            FROM directory
            JOIN client_path ON directory.id = client_path.directory_id
            JOIN client ON client.id = client_path.client_id
            WHERE client.id = $client_id AND directory.visible = 1");
        if($pathSel->rowCount() == 0) _error();
        $client_path = $pathSel->fecthAssoc()['hash_dir'];
    }
    
    if($hash_dir == "") $hash_dir = $client_path;
        
    if(!($dirId = _is_client_folder($hash_dir, $client_id))) 
    _error(401, "Não autorizado");
  
    sleep(mt_rand(3, 8));
        
    try {
      
        $hashFile = (new file($client_path, $name, $mime))->upload($file)->hash();
        if(!$hashFile) _error(400, "Ocorreu um erro ao tentar salvar o arquivo");
  
        $idInsert = _exec(
            "INSERT INTO 
            file_client(nome, hash_file, mime_type, directory_id)
            VALUES ('$name', '$hashFile', '$mime', $dirId)", true);
      
        if(!$idInsert) _error(400, "Ocorreu um erro ao tentar salvar o arquivo");
        
        _query(
            "INSERT INTO 
            file_client_info (file_client_id, created_user_id, createdAt)
            VALUES ($idInsert, $user->id, now())");

        return _response([
            'type'   => 'file',
            'hashId' => $hashFile,
            'nome'   => $name,
            'ext'    => $mime,
            'thumb'  => _get_thumb($user->session(), $mime,  $client_path, $hashFile),
            'novo'   => true
        ]);
  
    } catch(Exception $e){
        return _error(400, $e->getMessage());
    }
}

function _get_thumb($session, $ext, $client_path, $hashId){
    $icon = "public/img/icons/$ext.jpg";
    return in_array($ext, ['jpg', 'png']) 
    ? _url().'thumbnail/'.$session.'/'.$client_path.'/'. $hashId.'.'.$ext 
    : (file_exists("../".$icon) 
    ? _url().$icon 
    : _url()."public/img/icons/default.jpg");
}

/**
 * @function: list_all_files
 * @pool:manage_files
 */
function list_all_files($hash_dir = "", $client_id = 0){

    $user = _user();

    if($user->is_client()){
      
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
        
    } else {
        $pathSel = _query(
            "SELECT directory.hash_dir
            FROM directory
            JOIN client_path ON directory.id = client_path.directory_id
            JOIN client ON client.id = client_path.client_id
            WHERE client.id = $client_id AND directory.visible = 1");
        if($pathSel->rowCount() == 0) _error();
        $client_path = $pathSel->fecthAssoc()['hash_dir'];
    }
    
    if($hash_dir == "") $hash_dir = $client_path;
        
    if(!($dirId = _is_client_folder($hash_dir, $client_id))) 
    _error(401, "Não autorizado");

    $contents = [];

    $dirsSel = _query(
        "SELECT
        child.nome,
        child.hash_dir as hashId,
        (CASE WHEN(true) THEN 'dir' END) as `type`
        FROM  directory as child JOIN directory as main ON main.id = child.directory_id
        WHERE main.hash_dir = '$hash_dir' AND child.visible = 1
        ORDER BY child.nome ASC;
    ");
    
    if($dirsSel->rowCount() > 0) 
    $contents = $dirsSel->fetchAllAssoc();

    $filesSel = _query(
        "SELECT 
        file_client.nome,
        file_client.hash_file as hashId,
        file_client.mime_type as ext,
        (CASE WHEN(true) THEN 'file' END) as `type`
        FROM  file_client 
        JOIN  directory ON directory.id = file_client.directory_id
        WHERE ghost = 0 AND directory.hash_dir = '$hash_dir' AND directory.visible = 1
        ORDER BY file_client.id DESC;
    ");
    
    $fsfinal = [];
    
    if($filesSel->rowCount() > 0) {
        $files = $filesSel->fetchAllAssoc();
        foreach ($files as $file) {
            
            $ffinal = [
                "nome"   => $file['nome'],
                "hashId" => $file['hashId'],
                "ext"    => ".".$file['ext'],
                "type"   => $file['type'],
                'novo'   => false
            ];

            $ffinal['thumb'] = _get_thumb($user->session(), $file['ext'], $client_path, $file['hashId']);
            $fsfinal[] = $ffinal;
        }
    }
    
    $contents = array_merge($contents, $fsfinal);
    return _response($contents);

}


/**
 * @function: list_public_files
 * @pool:manage_files,files_basic
 */
function list_public_files(){


}

