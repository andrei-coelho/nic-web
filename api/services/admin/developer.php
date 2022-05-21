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

function add_permission($nome){

    $slug = _generate_slug($nome);

    if(!_exec("INSERT INTO 
        permission_pool (nome, slug)
        VALUES ('$nome', '$slug')"
    )) _error();

}


function edit_permission(int $permissionId, $nome){

    $slug = _generate_slug($nome);

    if(!_exec("UPDATE permission_pool 
        SET nome = '$nome', slug = '$slug'
        WHERE id = $permissionId;
    ")) _error();

}