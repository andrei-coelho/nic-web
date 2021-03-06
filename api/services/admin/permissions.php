<?php 

function _generate_slug($nome){

    $slug  = _slug($nome, "_");
    $query = _query("SELECT id FROM permission_pool WHERE slug = '$slug' ORDER BY id DESC LIMIT 1");
    
    if($query ->rowCount() > 0){
        $id    = (int)$query->fetchAssoc()['id'] + 1;
        $slug .= "-".$id;
    }

    return $slug;
}

function add_permission($nome, $description, $slug = ""){

    $slug = $slug == "" ? _generate_slug($nome) : $slug;

    if(($lastId = _exec("INSERT INTO 
        permission_pool (nome, slug, description, ativo)
        VALUES ('$nome', '$slug', '$description', 1)"
    , true)) == false) _error(500, 'Erro ao tentar criar a nova permissão');

    $query  = _query("SELECT user_id FROM user_client WHERE master = 1");
    $allIds = $query->fetchAllAssoc();
    $insert = "INSERT INTO user_permission (permission_pool_id, user_id) VALUES ";
    
    foreach ($allIds as $id) 
        $insert .= "($lastId, ".$id['user_id']."),";
    $insert = substr($insert, 0, -1);

    if(!_exec($insert)) _error(500, 'Não foi possível inserir a permissão para todos os usuários master.');

}


function edit_permission(int $permissionId, $nome){

    $slug = _generate_slug($nome);

    if(!_exec("UPDATE permission_pool 
        SET nome = '$nome', slug = '$slug'
        WHERE id = $permissionId;
    ")) _error();

}