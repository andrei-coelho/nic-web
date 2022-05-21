<?php 
/**
 * @service: users
 */

function _create_user($nome, $slug, $email, $senha, $admin, $ativo){

    $qrySlug = _query("SELECT id FROM user WHERE slug = '$slug'");
    if($qrySlug -> rowCount() > 0) $slug .= ((int)$qrySlug->fetchAssoc()['id'] + 1);

    $encpass = password_hash($senha, PASSWORD_DEFAULT);
    if(!($id = _exec("INSERT INTO 
        user    (nome, slug, email, senha, senha_temp, admin, ativo) 
        VALUES ('$nome', '$slug', '$email', '$encpass', '$senha', $admin, $ativo)", 
    true))) _error(500, 'server error 2');

    return $id;

}

function _set_all_permissions($userId){
    
    $permissions = _query("SELECT id FROM permission_pool")->fetchAllAssoc();
    $insert      = "INSERT INTO user_permission (permission_pool_id, user_id) VALUES ";

    foreach ($permissions as $permission)
        $insert .= "(".$permission['id'].", $userId),";
    $insert = substr($insert, 0, -1).";";
    
    if(!_exec($insert)) _error();

}


 /**
  * @function: create_client_user
  * @pool:administrar_contas
  */
function create_client_user($nome, $email, int $client_id, int $master = 0){
    
    $pass    = _gen_pass($email);
    
    $select  = _query("SELECT slug FROM client WHERE id = $client_id");
    
    if($select->rowCount() == 0) _error(404, 'Cliente não existe');
    
    $slugCli = $select->fetchAssoc()['slug'];
    $slug    = _slug($nome, "-")."@".$slugCli;

    $id      = _create_user($nome, $slug, $email, $encpass, 0, 0);

    if(!_exec("INSERT INTO 
        user_client (user_id, client_id, master, ghost)
        VALUES ($id, $client_id, $master, 0)
    ")) _error(500, 'server error 1');

    if($master == 1){
        _set_all_permissions($id);
    }

    _exec("UPDATE user SET ativo = 1 WHERE id = $id");

    return _response([
        "nome"   => $nome,
        "email"  => $email,
        "slug"   => $slug,
        "senha"  => $pass,
        "master" => $master
    ]);

}


 /**
  * @function: delete_user
  * @pool:administrar_contas
  */
function delete_user(int $user_id){
    try {
        _exec("DELETE FROM user WHERE id = $user_id");
    } catch (\Exception $th) {
        _error(500, 'Não foi possível deletar o usuário');
    }
}


 /**
  * @function: update_user
  * @pool:administrar_contas
  */
function update_user(int $user_id, $nome){

}


 /**
  * @function: ativar_user
  * @pool:administrar_contas
  */
function ativar_user(int $user_id, int $status = 1){
    if(!_exec("UPDATE user SET ativo = $status WHERE id = $user_id"))
        _error(500, 'server error');
}


 /**
  * @function: reset_pass
  * @pool:administrar_contas
  */
function reset_pass(int $user_id){
    
    $pass    = _gen_pass($user_id);
    $encpass = password_hash($pass, PASSWORD_DEFAULT);
    
    if(!_exec("UPDATE user SET senha = '$encpass', senha_temp = '$pass' WHERE id = $user_id"))
        _error(500, 'server error');
}