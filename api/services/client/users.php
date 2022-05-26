<?php 
/**
 * @service: users
 */

function _create_user($nome, $slug, $email, $senha, $telefone, $cargo, $admin, $ativo){

    $qrySlug = _query("SELECT id FROM user WHERE slug = '$slug'");
    if($qrySlug -> rowCount() > 0) $slug .= ((int)$qrySlug->fetchAssoc()['id'] + 1);

    $encpass = password_hash($senha, PASSWORD_DEFAULT);

    if(!($id = _exec("INSERT INTO 
        user    (nome, slug, email, senha, telefone, cargo, senha_temp, admin, ativo) 
        VALUES ('$nome', '$slug', '$email', '$encpass', '$telefone', '$cargo', '$senha', $admin, $ativo)", 
    true))) _error(500, 'server error 2');

    return $id;

}


function _set_permissions($permissions, $userId, $clean = true) {

    if($clean)
        _exec("DELETE FROM user_permission WHERE user_id = $userId");

    $insert = "INSERT INTO user_permission (permission_pool_id, user_id) VALUES ";

    foreach ($permissions as $permission)
        $insert .= "(".(isset($permission['id']) ? $permission['id'] : $permission[0]).", $userId),";
    $insert = substr($insert, 0, -1).";";
    
    if(!_exec($insert)) _error(500, 'Não foi possível inserir as permissões');

}

function _set_all_permissions($userId) {
    
    $permissions = _query("SELECT id FROM permission_pool")->fetchAllAssoc();
    _set_permissions($permissions, $userId);

}

function _set_pattern_permissions($userId) {
    
    $permissions = _query("SELECT id FROM permission_pool WHERE pattern = 1")->fetchAllAssoc();
    _set_permissions($permissions, $userId);

}


function set_permissions(array $permissions, int $user_id){

    $user  = _user();
    $usera = $user->getClientArray();
    $clid  = $usera['client_id'];

    if(_query(
        "SELECT user.id 
        FROM user 
        JOIN user_client ON user_client.user_id = user.id 
        WHERE user.id = $user_id AND user_client.client_id = $clid")
    ->rowCount()==0) _error(401, "Não autorizado");

    _set_pattern_permissions($user_id);
    if(count($permissions) > 0)
        _set_permissions($permissions, $user_id, false);
    
}

/**
  * @function: get_permissions_user
  * @pool: administrar_contas
  */

function get_permissions_user(int $user_id){

    $user  = _user();
    $usera = $user->getClientArray();
    $clid  = $usera['client_id'];

    $query = _query(
        "SELECT 
                 permission_pool.id,
                 permission_pool.nome,
                 permission_pool.description
        FROM     user_permission 
            JOIN user            ON user.id = user_permission.user_id
            JOIN user_client     ON user.id = user_client.user_id
            JOIN permission_pool ON user_permission.permission_pool_id = permission_pool.id
        WHERE 
            (user.id = $user_id AND user_client.client_id = $clid) 
            AND NOT permission_pool.pattern = 1
        ORDER BY permission_pool.nome ASC"
    );
    $selecteds = $query->fetchAllAssoc();

    $query2 = _query(
        "SELECT 
            permission_pool.id,
            permission_pool.nome,
            permission_pool.description
        FROM permission_pool 
        WHERE NOT permission_pool.pattern = 1"
    );
    $all = $query2->fetchAllAssoc();

    $idsSel = array_column($selecteds, 'id');
    foreach ($all as $k => $permission) {
        $all[$k]['selected'] = in_array($permission['id'], $idsSel);
    }

    return _response($all);

}


/**
  * @function: list_all
  * @pool:contas_basico,administrar_contas
  */
function list_all(){
    
    $user  = _user();
    $usera = $user->getClientArray();
    $clid  = $usera['client_id'];
    
    $query = _query(
        "SELECT
            user.id, 
            user.nome,
            user.slug,
            user.email,
            user.cargo,
            user.telefone,
            user_client.master,
            user.ativo
        FROM 
            user JOIN user_client ON user_client.user_id = user.id
        WHERE 
            user_client.client_id = $clid AND NOT user_client.ghost = 1
        ORDER BY user.nome ASC
    ");

    return _response([
        "is_editor"   => in_array('administrar_contas', array_column($user->getPermissions(), 'slug')),
        "client_slug" => $usera['client_slug'],
        "list"        => $query->fetchAllAssoc()
    ]);
    
}


 /**
  * @function: create_client_user
  * @pool:administrar_contas
  */
function create_client_user($nome, $email, $cargo, $telefone, int $master){
    
    $user    = _user();
    $usera   = $user->getClientArray();
    $clid    = $usera['client_id'];
    $clislug = $usera['client_slug'];

    $pass    = _gen_pass($email);
    $slug    = _slug($nome)."@".$clislug;
    $id      = _create_user($nome, $slug, $email, $pass,  $telefone, $cargo, 0, 0);

    if(!_exec("INSERT INTO 
        user_client (user_id, client_id, master, ghost)
        VALUES ($id, $clid, $master, 0)
    ")) _error(500, 'server error 1');

    if($master == 1){
        _set_all_permissions($id);
    } else {
        _set_pattern_permissions($id);
    }

    if(!_exec("UPDATE user SET ativo = 1 WHERE id = $id"))
        _error(500, "server error 2");

    return _response(["pass"  => $pass]);

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
function update_user(int $user_id, $nome, $email, $cargo, $telefone, int $master, array $permissions = []){

    $slugCli = _user()->getClientArray()['client_slug'];
    $slug    = _slug($nome)."@".$slugCli;

    if(!_exec(
        "UPDATE user SET 
            nome     = '$nome',
            email    = '$email',
            slug     = '$slug',
            cargo    = '$cargo',
            telefone = '$telefone'
        WHERE id = $user_id
    ")) _error(500, "Não foi possível alterar");

    if($master == 1){
        _exec("UPDATE user_client SET master = 1 WHERE user_id = $user_id");
        _set_all_permissions($user_id);
    } else {
        set_permissions($permissions, $user_id);
    }

    return _response([], "Alteração realizada com sucesso.");

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
    
    return _response([
        "pass" => $pass
    ]);

}