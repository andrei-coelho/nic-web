<?php 


function list_clients(){

    $query = _query("SELECT * FROM client");
    return _response($query->fetchAllAssoc());

}


function get_info($client_id){
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