<?php 

function list_dropbox_tokens(int $dropbox_tk_id = 0){

    $where = $dropbox_tk_id > 0 ? " WHERE dropbox_tk.id = $dropbox_tk_id;" : ";";
    
    $query = _query(
    "SELECT 
        dropbox_tk.id as token_id,
        dropbox_tk.account_email as email,
        dropbox_tk.limit_size,
        (SELECT 
            sum(max_byte_cloud) 
            FROM client_path 
            WHERE dropbox_tk_id = token_id
        ) as total_used
    FROM dropbox_tk 
    $where");
    
    $total = $query->rowCount();
    if($total == 0) _error();

    return _response($dropbox_tk_id == 0 ? $query->fetchAllAssoc() : $query->fetchAssoc());
        
}

function salvar_dropbox_token($email, $senha, $key, $secret, $token, int $espaco){
    
    if(!_exec("INSERT INTO 
        dropbox_tk (refresh_token, account_email, account_senha, app_key, secret_key, limit_size)
        VALUES     ('$token', '$email', '$senha', '$key', '$secret', $espaco)
    ")) _error(500, 'server error');

}

function excluir_dropbox_token(int $dropbox_tk_id){

    try {
        _query("DELETE FROM dropbox_tk WHERE id = $dropbox_tk_id");
    } catch (\Exception $th) {
        _error(500, 'Não é possível excluir a conta Dropbox porque tem clientes usando ela.');
    }

}

function editar_espaco(int $dropbox_tk_id, int $espaco){

    if(!_exec("UPDATE dropbox_tk SET limit_size = $espaco WHERE id = $dropbox_tk_id"))
        _error();
    return _response([], "Dados atualizados com sucesso!");
}
