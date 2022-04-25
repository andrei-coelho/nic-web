<?php 
/**
 * @service: files
 */

use  libs\app\FileManager as file;

/**
 * @function: create_ghost_file
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

function update_file(){
    
}

function save_file(){

}

function delete_file(){
    
}