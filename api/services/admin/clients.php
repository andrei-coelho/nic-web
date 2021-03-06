<?php 


function list_clients(){

    $query = _query("SELECT * FROM client");
    return _response($query->fetchAllAssoc());

}

function bloquear(int $client_id){
    if(!_exec("UPDATE client SET ativo = 0 WHERE id = $client_id"))
        _error(500, 'server error');
}

function ativar(int $client_id){
    if(!_exec("UPDATE client SET ativo = 1 WHERE id = $client_id"))
        _error(500, 'server error');
}

function get_info(int $client_id){
    $query = _query(
    "SELECT 
    (
        SELECT 
            SUM(file_client_info.size_bytes) as total
        FROM file_client_info
            JOIN file_client ON file_client_info.file_client_id = file_client.id
            JOIN directory ON directory.id = file_client.directory_id
        WHERE directory.client_id = $client_id
    )    as total_used,
        client_path.max_byte_cloud as max_used,
        dropbox_tk.account_email   as email,
        dropbox_tk.id   as token_id
    FROM client
        JOIN client_path ON client.id = client_path.client_id 
        JOIN dropbox_tk  ON dropbox_tk.id = client_path.dropbox_tk_id
    WHERE client.id = $client_id;");

    if($query->rowCount() == 0) _error();
    return _response($query->fetchAssoc());
            
}

function save_client(int $token_id, int $used, $nome, $slug){

    $id = _exec("INSERT INTO 
        client (slug, nome) 
        VALUES ('$slug', '$nome')"
    , true);

    $hash_dir = _unique_hash($slug.$id);

    $idir = _exec("INSERT INTO directory(nome, hash_dir, client_id) VALUES ('raiz', '$hash_dir', $id)", true);

    mkdir('../files_to_upload/'.$hash_dir.'/', 0777, true);

    _exec("INSERT INTO 
            client_path(dropbox_tk_id, client_id, directory_id, max_byte_cloud)
            VALUES ($token_id, $id, $idir, $used)
    ");

    return _response([], "Cliente Criado");

}


function update_client(int $client_id, $nome, $slug, int $size){
    
    if(!_exec("UPDATE client SET nome = '$nome', slug = '$slug' WHERE id = $client_id"))
        error(500, "Erro ao tentar atualizar dados do cliente");
    if(!_exec("UPDATE client_path SET max_byte_cloud = $size WHERE client_id = $client_id"))
        error(500, "Erro ao tentar atualizar dados do cliente");

    return _response([], "Dados atualizados com sucesso!");
}

function open_ghost_session(int $client_id){

    $user     = _user();    
    $userId   = $user->id;
    $id_ghost = $user->ghostId();
    
    if(!$id_ghost){
        $slug = 'ghost@'._unique_hash('ghost'.$userId);
        $id_ghost = _exec("INSERT INTO user (nome, slug, email, senha, ativo) 
                VALUE ('Ghost', '$slug', '$slug', 'no-pass', 1)", true);
        _exec("UPDATE user SET ghost_id = $id_ghost WHERE id = $userId");
    }

    $sess   = _gen_session($userId);
    $hoje   =  date("Y-m-d H:i:s");
    $expire =  date('Y-m-d H:i:s', strtotime($hoje. ' + 2 days'));

    _exec("UPDATE session SET ativo = 0 WHERE user_id = $id_ghost");

    if(!_exec("INSERT 
        INTO session (user_id, hash, expire, ativo) 
        VALUES($id_ghost, '$sess', '$expire', 1)")) 
    _error(500, 'Server problem 1');

    $query = _query("SELECT id FROM user_client WHERE user_id = $id_ghost");
    if($query->rowCount() == 0){
        try {
            _exec(
                "INSERT INTO user_client(user_id, client_id, master, ghost)
                VALUES ($id_ghost, $client_id, 1, 1) ");
            
            $allPermissions = _query("SELECT id FROM permission_pool")->fetchAllAssoc();
            $insertPermissions = "INSERT INTO user_permission (permission_pool_id, user_id) VALUES ";
            
            foreach ($allPermissions as $permission)
                $insertPermissions .= "(".$permission['id'].", $id_ghost),";
            
            $insertPermissions = substr($insertPermissions, 0, -1).";";
            _exec($insertPermissions);

        } catch(\Exception $e){
            _error(500, 'Server problem 2');
        }
    } else {
        $user_client_id = $query->fetchAssoc()['id'];
        if(!_exec("UPDATE user_client SET client_id = $client_id WHERE id = $user_client_id")){
            _error(500, 'Server problem 3');
        }
    }

    return _response(["session"=>$sess]);

}

function exluir(int $client_id){

    $query =  _query(
        "SELECT 
              dropbox_tk.id,
              directory.hash_dir
        FROM  dropbox_tk 
        JOIN  client_path ON dropbox_tk_id = dropbox_tk.id 
        JOIN  directory   ON directory.id = client_path.directory_id
        WHERE client_path.client_id = $client_id
    ");
    if($query->rowCount() == 0) _error();
    
    $response = $query->fetchAssoc();
    $token_id = $response['id'];
    $hash_dir = $response['hash_dir'];

    include "../api/services/client/files.php";

    _remove_dir($client_id, $hash_dir, $hash_dir, $token_id);
    _exec("DELETE FROM client WHERE client.id = $client_id");
    rmdir('../files_to_upload/'.$hash_dir.'/');

}