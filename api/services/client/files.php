<?php

/**
 * @service: files
 */

 /*
error_reporting(0);
ini_set('display_errors', 0);
*/

use  libs\app\FileManager as file;
use  libs\app\DropBox as dropbox;

include "_upload_file.php";

function _get_client_path($client_id){
    $pathSel = _query(
        "SELECT directory.hash_dir
        FROM directory
        JOIN client_path ON directory.id = client_path.directory_id
        JOIN client ON client.id = client_path.client_id
        WHERE client.id = $client_id AND directory.visible = 1");
    if($pathSel->rowCount() == 0) _error();
    return $pathSel->fecthAssoc()['hash_dir'];
}

function _is_client_folder($hash_dir, $client_id){
    $dirSel = _query(
        "SELECT id FROM directory 
        WHERE hash_dir = '$hash_dir' 
        AND client_id = $client_id");
  
    if($dirSel->rowCount()==0) return false;
    return $dirSel->fetchAssoc()['id'];
}

function _get_file_link_query($hash_file, $client_id, $onlyPublic = false){

    $hoje = date('Y-m-d H:i:s');
    
    $query = 
          "SELECT 
                file_client.id,
                file_client.nome,
                file_client.hash_file as hashId,
                file_client.saved,
                file_client.dropbox_hash_id,
                file_client.mime_type as ext,
                download_link.id as link_id,
                download_link.link,
                (case when(download_link.expire < '$hoje') THEN 1 ELSE 0 END) as expired
           FROM file_client
           JOIN directory     ON directory.id = file_client.directory_id
      LEFT JOIN download_link ON download_link.file_client_id = file_client.id 
          WHERE file_client.hash_file = '$hash_file'
            AND directory.client_id   =  $client_id
            AND file_client.ghost     =  0
    ";
    if($onlyPublic) $query .= " AND file_client.public = 1";
    return $query;
}

function _gen_link($file, $client_path, $client_id = 0){
   
    $user =  _user();
    if($user->is_client()) 
        $client_id = $user->getClientArray()['client_id'];

    $link = false;

    if($file['saved'] && ($file['expired'] == 1 || $file['link'] == "")){
        
        $id     = $file['id'];
        $link   = (new dropbox($client_id))->getTemporaryFileLink($file['hashId']);
        $expire = date("Y-m-d H:i:s", strtotime('+3 hours'));

        if( !isset($file['link_id'])){
            if(!_exec("INSERT INTO download_link(file_client_id, link, expire) 
                VALUES ($id, '$link', '$expire')"))
            _error(500, 'Server error');
        } else
        if( !_exec("UPDATE download_link SET link = '$link', expire = '$expire' WHERE id = ".$file['link_id']))
            _error(500, 'Server error');

    } else {
        $link = $file['link'] != "" ? $file['link'] : false;
    }

    if(!$link)
        $link = _url()."download/".$user->session().'/'.$client_path."/".$file['hashId'].".".$file['ext'];

    return $link;
}

/**
 * @function:get_files_info
 * @pool:manage_files
 */

function get_files_info(){
    $user = _user();
    return _response([
        "used" => $user->getTotalBytes(),
        "max"  => $user->getClientArray()['max_byte']
    ]);
}

/**
 * @function:get_public_link
 * @pool:manage_files,files_basic
 */
function get_public_link($hash_file, $client_id = 0){

    $user =  _user();
    if($user->is_client()){
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
    }

    $sel = _query(_get_file_link_query($hash_file, $client_id, true));

    if($sel->rowCount()==0)_error();
    $file = $sel->fetchAssoc();
    
    return _response(['link' => _gen_link($file, $client_path)]);
}


/**
 * @function:get_link
 * @pool:manage_files
 */
function get_link($hash_file, $client_id = 0){
    
    $user =  _user();
    if($user->is_client()){
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
    }

    $sel = _query(_get_file_link_query($hash_file, $client_id));

    if($sel->rowCount()==0)_error();
    $file = $sel->fetchAssoc();
    
    return _response(['link' => _gen_link($file, $client_path)]);

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
        $client_path = $client_arr['client_path'];
    } 
    if($hash_dir == "") $hash_dir = $client_path;
    
    $table = "file_client";
    $q = _query(
        "SELECT 
            $table.id,
            $table.hash_file as hashId
        FROM  $table 
        JOIN  directory ON directory.id = $table.directory_id
        WHERE $table.hash_file = '$hash_file' AND NOT directory.hash_dir = '$hash_dir'");
    if($q->rowCount()==0){
        $table = "directory";
        $q = _query(
            "SELECT filho.id,
                    filho.hash_dir as hashId
                FROM $table as filho JOIN directory as pai ON filho.directory_id = pai.id
                WHERE filho.hash_dir = '$hash_file' 
                AND NOT pai.hash_dir = '$hash_dir'");
        if($q->rowCount()==0) 
        _error(404, 'O arquivo já está no diretório escolhido');
    }
    
    $file   = $q->fetchAssoc();
    $idFile = $file['id'];
    $hashIdFile = $file['hashId'];

    if($hashIdFile == $hash_dir) _error(404, '');

    $query = _query("SELECT id FROM directory WHERE hash_dir = '$hash_dir'");
    if($query->rowCount()==0) _error();

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

    $dropbox_tk_id = _get_dropbox_tk_id($client_id);
        
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
        $sfiles = _query(
            "SELECT 
                    file_client.id, 
                    file_client.hash_file, 
                    file_client.dropbox_hash_id, 
                    file_client.saved, 
                    file_client.mime_type
               FROM file_client 
               JOIN directory   ON directory.id = file_client.directory_id 
              WHERE 
              file_client.directory_id IN (".$indirs.")");
        _exec("DELETE FROM directory WHERE id IN (".$indirs.")");
        if($sfiles->rowCount() > 0){
            $files = $sfiles->fetchAllAssoc();
            foreach ($files as $file) {
                _remove_file($client_path,$file,$dropbox_tk_id);
            }
        }

    } else {

        $sfiles = _query(
            "SELECT 
                   file_client.id, 
                   file_client.hash_file,
                   file_client.dropbox_hash_id, 
                   file_client.saved, 
                   file_client.mime_type
              FROM file_client 
              JOIN directory   ON directory.id = file_client.directory_id 
              WHERE file_client.hash_file = '$hash_file'
        ");

        if($sfiles->rowCount() > 0){
            $file = $sfiles->fetchAssoc();
            _exec("DELETE FROM file_client WHERE id = ".$file['id']);
            _remove_file($client_path,$file,$dropbox_tk_id);
        }
    }

}


function _remove_file($client_path, $file, $dropbox_tk_id){
    
    $fileRoute = $client_path.'/'.$file['hash_file'].".".$file['mime_type'];
    $fileThumb = '../thumbs/'.$fileRoute;
    
    if(file_exists($fileThumb))unlink($fileThumb);
    
    if($file['saved'] == 0){
        $fileToUp  = '../files_to_upload/'.$fileRoute;
        if(file_exists($fileToUp))unlink($fileToUp);
    } else {
        $hashId = $file['dropbox_hash_id'];
        _exec("INSERT INTO 
        file_to_delete(dropbox_tk_id, dropbox_hash_id)
        VALUES ($dropbox_tk_id, '$hashId')");
    }
    
}

function _get_dropbox_tk_id($client_id){
    $query = _query(
        "SELECT 
            dropbox_tk.id
            FROM client_path
            JOIN dropbox_tk ON dropbox_tk.id = client_path.dropbox_tk_id 
            WHERE client_path.client_id = $client_id
        ");
    if($query->rowCount() > 0) return $query->fetchAssoc()['id'];
    return 0;
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
function remove_tag($tag_id, $client_id = 0){
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

    if(trim($tag) == "") _error(404,'');
        
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

/**
 * @function:edit_dir_name
 * @pool:manage_files
 */
function edit_dir_name($dir_name, $hash_dir, $client_id = 0){
    
    $user = _user();

    if($user->is_client()){
        $client_id = $user->getClientArray()['client_id'];
    }

    if(!_exec("UPDATE directory 
    SET nome = '$dir_name'
    WHERE hash_dir = '$hash_dir' AND client_id = $client_id"))
        _error(404, 'Esta pasta não existe.');

}



/**
 * @function:save_file
 * @pool:manage_files
 */
function save_file($name, $mime, $file, $flag, $hashId = "", $hash_dir = "", $client_id = 0){
    
    $user = _user();
  
    if($user->is_client()){
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
    } else {
        $client_path = _get_client_path($client_id);
    }

    if($hash_dir == "") $hash_dir = $client_path;
        
    if(!($dirId = _is_client_folder($hash_dir, $client_id))) 
        _error(401, "Não autorizado");

    

    if($flag == 'save'  ) return __upload_save  ($user, $name, $mime, $file, $client_path, $dirId);
    if($flag == 'create') return __upload_create($user, $client_path, $name, $mime, $dirId);
    if($flag == 'append') return __upload_append($client_path, $hashId.".".$mime, $file);
    if($flag == 'commit') return __upload_commit($user, $hashId, $mime, $client_path);

}


/**
 * @function:list_all_folders
 * @pool:manage_files
 */

 function list_all_folders($hash_dir = "", $client_id = 0){
    
    $user = _user();
    if($user->is_client()){
      
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
        
    } else {
        $client_path = _get_client_path($client_id);
    }

    if($hash_dir == "") $hash_dir = $client_path;
    return _response(_get_list_folder($hash_dir, $client_id));
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
        $client_path = _get_client_path($client_id);
    }

    if($hash_dir == "") $hash_dir = $client_path;

    return _response(array_merge(
        _get_list_folder($hash_dir, $client_id), 
        _get_list_files($hash_dir, $client_path)
    ));

}


function _get_list_files($hash_dir, $client_path){

    $filesSel = _query(
        "SELECT 
        file_client.nome,
        file_client.hash_file as hashId,
        file_client.mime_type as ext,
        file_client.public    as publico,
        file_client_info.size_bytes as size,
        (CASE WHEN(true) THEN 'file' END) as `type`
        FROM  file_client 
        JOIN  file_client_info ON file_client_info.file_client_id = file_client.id
        JOIN  directory ON directory.id = file_client.directory_id
        WHERE ghost = 0 AND directory.hash_dir = '$hash_dir'
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
                "size"   => $file['size'],
                'novo'   => false,
                'options'=> true,
                'publico'=> $file['publico'] == 1
            ];

            $ffinal['thumb'] = _get_thumb(_user()->session(), $file['ext'], $client_path, $file['hashId']);
            $fsfinal[] = $ffinal;
        }
    }

    return $fsfinal;

}

function _get_list_folder($hash_dir, $client_id){
    
    if(!($dirId = _is_client_folder($hash_dir, $client_id))) 
    _error(401, "Não autorizado");

    $dirsSel = _query(
        "SELECT
        child.nome,
        child.hash_dir as hashId,
        (CASE WHEN(true) THEN 'dir' END) as `type`
        FROM  directory as child JOIN directory as main ON main.id = child.directory_id
        WHERE main.hash_dir = '$hash_dir'
        ORDER BY child.nome ASC;
    ");
    
    return  $dirsSel->rowCount() > 0 ? $dirsSel->fetchAllAssoc() : [];
}


/**
 * @function: publish_file
 * @pool:manage_files
 */
function publish_file ($hash_file, int $val, $client_id = 0){
    
    if($val > 1) _error();
    $user = _user();

    if($user->is_client()){
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
    } 

    $query = _query(
        "SELECT file_client.id 
           FROM file_client 
           JOIN directory ON file_client.directory_id = directory.id
          WHERE directory.client_id = $client_id
            AND file_client.hash_file = '$hash_file'
    ");

    if($query->rowCount() == 0) _error(404, '');
    $fileId = $query->fetchAssoc()['id'];
    if(!_exec("UPDATE file_client SET public = $val WHERE id = $fileId"))
        _error(500, 'Server error');
}


/**
 * @function: list_public_files
 * @pool:manage_files,files_basic
 */
function list_public_files(){

    $user = _user();

    if($user->is_client()){
        $client_arr  = $user->getClientArray();
        $client_path = $client_arr['client_path'];
        $client_id   = $client_arr['client_id'];
    } 

    $filesSel = _query(
        "SELECT 
        file_client.nome,
        file_client.hash_file as hashId,
        file_client.mime_type as ext,
        file_client.public    as publico,
        file_client_info.size_bytes as size,
        (CASE WHEN(true) THEN 'file' END) as `type`
        FROM  file_client 
        JOIN  file_client_info ON file_client_info.file_client_id = file_client.id
        JOIN  directory ON directory.id = file_client.directory_id
        WHERE ghost = 0 
        AND directory.client_id = '$client_id'
        AND file_client.public = 1
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
                "size"   => $file['size'],
                'novo'   => false,
                'options'=> false,
                'publico'=> $file['publico'] == 1
            ];

            $ffinal['thumb'] = _get_thumb(_user()->session(), $file['ext'], $client_path, $file['hashId']);
            $fsfinal[] = $ffinal;
        }
    }

    return _response($fsfinal);

}


function _get_search_files($key_word, $client_id, bool $public = false){
    
    $keysW = explode(" ", str_replace([',', '.', '-', '_', '[', ']', '{', '}', '(', ')'], '', $key_word));
    $strS  = "file_client.nome LIKE '%$key_word%' OR ";
    foreach ($keysW as $w) {
        if(trim($w) != "")
        $strS .= "file_client_tag.nome like '$w' OR ";
    }
    $strS = substr($strS, 0, -3);

    $strPublic = $public ? "AND file_client.public = 1 " : "";

    $query = _query(
        "SELECT 
            file_client.nome,
            file_client.hash_file as hashId,
            file_client.mime_type as ext,
            file_client.public    as publico,
            file_client_info.size_bytes as size,
            (CASE WHEN(true) THEN 'file' END) as `type`
            FROM  file_client 
                JOIN  file_client_info ON file_client_info.file_client_id = file_client.id
                JOIN  file_client_tag ON file_client_tag.file_client_id = file_client.id
                JOIN  directory ON directory.id = file_client.directory_id
            WHERE ghost = 0 
                $strPublic
                AND directory.client_id = $client_id
                AND ( $strS )
            GROUP BY file_client.hash_file"
        );

    return $query;
}


/**
 * @function: search_public_files
 * @pool:manage_files,files_basic
 */

function search_public_files($key_word, $client_id = 0){
    
    $user = _user();
    
    if($user->is_client()){
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
    } else {
        $client_path = _get_client_path($client_id);
    }

    $query = _get_search_files($key_word,$client_id, true);
    
    $fsfinal = [];
    if($query->rowCount() > 0){
       
        $files = $query->fetchAllAssoc();
        
        foreach ($files as $file) {
            
            $ffinal = [
                "nome"   => $file['nome'],
                "hashId" => $file['hashId'],
                "ext"    => ".".$file['ext'],
                "type"   => $file['type'],
                "size"   => $file['size'],
                'novo'   => false,
                'options'=> false,
                'publico'=> $file['publico'] == 1
            ];

            $ffinal['thumb'] = _get_thumb(_user()->session(), $file['ext'], $client_path, $file['hashId']);
            $fsfinal[] = $ffinal;
        }
    }

    return _response($fsfinal);

}


 /**
 * @function: search_all_files
 * @pool:manage_files
 */

function search_all_files($key_word, $client_id = 0){
    
    $user = _user();
    
    if($user->is_client()){
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
    } else {
        $client_path = _get_client_path($client_id);
    }

    $query = _get_search_files($key_word,$client_id);
    
    $fsfinal = [];
    if($query->rowCount() > 0){
        
        $files = $query->fetchAllAssoc();
        
        foreach ($files as $file) {
            
            $ffinal = [
                "nome"   => $file['nome'],
                "hashId" => $file['hashId'],
                "ext"    => ".".$file['ext'],
                "type"   => $file['type'],
                "size"   => $file['size'],
                'novo'   => false,
                'options'=> true,
                'publico'=> $file['publico'] == 1
            ];

            $ffinal['thumb'] = _get_thumb(_user()->session(), $file['ext'], $client_path, $file['hashId']);
            $fsfinal[] = $ffinal;
        }
    }

    return _response($fsfinal);

}