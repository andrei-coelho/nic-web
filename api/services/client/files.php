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
 * @function:add_folder
 * @pool:manage_files
 */
function add_folder($name, $hash_dir = "", $client_id = 0){

    $user = _user();

    if($user->is_client()){
        $cliArr = $user->getClientArray();
        $client_id = $cliArr['client_id'];
        if($hash_dir == "") $hash_dir = $cliArr['client_path'];
    }
    
    if(!($dirId = _is_client_folder($hash_dir, $client_id))) 
        _error(401, "Não autorizado");

    $hash = _unique_hash($name);

    if(!_exec("INSERT INTO 
                directory(directory_id, nome, hash_dir, client_id)
                VALUES ($dirId, '$name', '$hash', $client_id)"))
        _error(500, "Server Error");
    
    return _response(['hashId' => $hash]);

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
      
        $client_arr = $user->getClientArray();
        $client_id  = $client_arr['client_id'];
  
        if($hash_dir == "") $hash_dir = $client_arr['client_path'];
        
        if(!($dirId = _is_client_folder($hash_dir, $client_id))) 
        _error(401, "Não autorizado");
        
    }
  
    sleep(mt_rand(3, 8));
        
    try {
      
        $hashFile = (new file($hash_dir, $name, $mime))->upload($file)->hash();
        if(!$hashFile) _error(400, "Ocorreu um erro ao tentar salvar o arquivo");
  
        $insert = _exec(
        "INSERT INTO 
         file_client(nome, hash_file, mime_type, directory_id)
         VALUES ('$name', '$hashFile', '$mime', $dirId)");
      
        if(!$insert) _error(400, "Ocorreu um erro ao tentar salvar o arquivo");
        return _response(['hash_file' => $hashFile]);
  
    } catch(Exception $e){
        return _error(400, $e->getMessage());
    }
}

/**
 * @function: list_all_files
 * @pool:manage_files
 */
function list_all_files($hash_dir = "", $client_id = 0){

    sleep(2);

    $user = _user();

    if($user->is_client()){
      
        $client_arr  = $user->getClientArray();
        $client_id   = $client_arr['client_id'];
        $client_path = $client_arr['client_path'];
        
        if($hash_dir == "") $hash_dir = $client_path;
        
        if(!($dirId = _is_client_folder($hash_dir, $client_id))) 
        _error(401, "Não autorizado");
        
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

    $contents = [];

    $dirsSel = _query(
        "SELECT
        child.nome,
        child.hash_dir as hashId,
        (CASE WHEN(true) THEN 'dir' END) as `type`
        FROM  directory as child JOIN directory as main ON main.id = child.directory_id
        WHERE main.hash_dir = '$hash_dir'
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
            ];

            $icon = "public/img/icons/".$file['ext'].".jpg";

            $ffinal['thumb'] 
                = in_array($file['ext'], ['jpg', 'png']) 
                ? _url().'thumbnail/'.$user->session().'/'.$client_path.'/'.$file['hashId'].'.'.$file['ext'] 
                : (file_exists("../".$icon) 
                ? _url().$icon 
                : _url()."public/img/icons/default.jpg");

            $fsfinal[] = $ffinal;
        }
    }
    
    
    $contents = array_merge($contents, $fsfinal);

    return _response($contents);

}

/**
 * @function: delete_file
 * @pool:manage_files
 */
function delete_file(){
    
}

/**
 * @function: list_public_files
 * @pool:manage_files,files_basic
 */
function list_public_files(){


}

