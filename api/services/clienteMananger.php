<?php 

function _is_public(){
    return true;    
}

function _is_authentic(){
    return in_array(_user()->level(), ['admin']);
}

function _check_cliente(int $id_cliente){
    $client = _query("SELECT * FROM clientes WHERE id = $id_cliente AND ativo = 1");
    if(!$client) _error(404, "Cliente não existe ou está bloqueado");
}

/**
 * listagem
 */

function list_clientes(int $ativo = 1, int $page = 1, int $limit = 10, $orderBY = 'id', bool $desc = true, $search = ''){
    // retorna a lista de clientes
    $query = "SELECT * FROM clientes WHERE ";
    if( $search != '') $query .= "(nome LIKE '%$search%' OR site LIKE '%$search%') AND ";
    $query .= "ativo = $ativo ORDER BY $orderBY ".($desc ? "DESC" : "ASC")." LIMIT ".(($page - 1) * $limit).",".$limit;
    
    $listaQ = _query($query);
    if(!$listaQ) _error(500, "Ocorreu um erro ao tentar realizar uma consulta");
    
    return _response($listaQ->fetchAllAssoc());
}

function list_user_cliente(int $id_cliente, int $ativo = 1){
    // retorna a lista de usuarios do cliente
    $q = _query(
        "SELECT 
            users.nome, users.email, user_types.slug as level
        FROM users_cliente 
            JOIN users ON users.id = users_cliente.user_id
            JOIN clientes ON clientes.id = users_cliente.cliente_id
            JOIN user_types ON user_types.id = users.user_type
        WHERE 
            users_cliente.ativo = $ativo 
            AND clientes.id = $id_cliente 
            AND user_types.slug = 'cliente'
        ORDER BY users_cliente.id DESC;
    ");
    if(!$q) _error(500, "Ocorreu um erro ao tentar realizar uma consulta");
    return _response($q->fetchAllAssoc());
}

function list_app_cliente(int $id_cliente, int $ativo = 1){
    // retorna a lista de aplicações que são usadas pelo cliente para acesso a api
    $q = _query(
        "SELECT 
            users.nome, user_types.slug as level
        FROM secret_keys 
            JOIN users ON users.id = secret_keys.user_id
            JOIN clientes ON clientes.id = secret_keys.cliente_id
            JOIN user_types ON user_types.id = users.user_type
        WHERE 
            secret_keys.ativo = $ativo 
            AND clientes.id = $id_cliente 
            AND user_types.slug = 'app'
        ORDER BY secret_keys.id DESC;
    ");
    if(!$q) _error(500, "Ocorreu um erro ao tentar realizar uma consulta");
    return _response($q->fetchAllAssoc());
}

/**
 * geração
 */

function _gen_user($nome, $email, $pass, int $tipo){
    $userId = _exec("INSERT INTO users (nome, email, senha, user_type) VALUES ('$nome', '$email', '$pass', $tipo)", true);
    if(!$userId) _error(500, "Erro ao tentar gerar usuário. O e-mail pode estar sendo utilizado em outra conta.");
    return $userId;
}

function gen_cliente($nome, $site){
    // gera um cliente
    return _exec("INSERT INTO clientes(nome, site) VALUES ('$nome', '$site')") 
    ? _response() : _error(500, "Não foi possível gerar um novo cliente"); 
}

function gen_user_cliente(int $id_cliente, $nome, $email, $senha){
    // gera um usuario para um cliente
    _check_cliente($id_cliente);
    $userId = _gen_user($nome, $email, password_hash($senha, PASSWORD_DEFAULT));

    return _exec("INSERT INTO users_cliente(user_id, cliente_id, ativo) VALUES ($userId, $id_cliente, 1)") ? 
        _response() : _error(500, "Erro ao tentar gerar usuário para o cliente");
}

function gen_app_user(int $id_cliente, $nome){
    // gera um usuario para aplicações do cliente
    // gera e retorna uma chave secreta para ser usado em uma aplicação com esse usuário
    _check_cliente($id_cliente);
    $userId = _gen_user($nome, $nome.'@cliente_'.$id_cliente.".app", '-----', 1);
    $privtK = _gen_private_key($userId);
    return _exec("INSERT INTO secret_keys(cliente_id, user_id, secret_key, ativo) VALUES ($id_cliente, $userId, '$privtK', 1)") ? 
        _response() : _error(500, "Erro ao tentar gerar uma chave para um app do cliente");
}


/**
 * edição
 */

function edit_user_cliente(int $id_user_cliente, $nome, $email, $senha){
    // edita um usuario para um cliente
}


/**
 * bloqueio
 */

function block_app_user($secret_key){
    // bloqueia uma aplicação de um cliente
    return _exec("UPDATE secret_keys SET ativo = 0 WHERE secret_key = '$secret_key'") 
    ? _response() : _error(500, "Não foi possível realizar as mudanças");
}

function block_user_cliente(int $id_user_cliente){
    // bloqueia um usuario de um cliente
    return _exec("UPDATE users_cliente SET ativo = 0 WHERE id = $id_user_cliente") 
    ? _response() : _error(500, "Não foi possível realizar as mudanças");
}

function block_cliente(int $id_cliente){
    // bloqueia um cliente
    return _exec("UPDATE clientes SET ativo = 0 WHERE id = $id_cliente") 
    ? _response() : _error(500, "Não foi possível realizar as mudanças");
}