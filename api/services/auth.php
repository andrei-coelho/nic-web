<?php 

/**
 * @service:auth
 */


// $passEnc = password_hash($pass, PASSWORD_DEFAULT);
// $status = password_verify($pass, $user['senha']);

/**
 * @function:logar
 * @pool:public
 */
function logar($email, $senha, $device){

    sleep(1);

    $userSel = _query(
        "SELECT 
            user.id,
            user.senha
        FROM user 
        WHERE 
        user.email ='$email' AND user.ativo = 1;"
    );

    if($userSel->rowCount() == 0) _error(404, 'O email está errado, não foi cadastrado ou está bloqueado');
    $user = $userSel->fetchAssoc();
    //$status = $user['senha'] == $senha;
    $status = password_verify($senha, $user['senha']);
    //http://localhost:3000/accounts@add_user
    if(!$status) _error(404, 'A senha enviada não é a mesma cadastrada');
    
    $user_id = $user['id'];

    if(!_exec("UPDATE 
        session SET ativo = 0 
        WHERE user_id = $user_id")) 
    _error(500, 'Server problem');
   
    $hoje   =  date("Y-m-d H:i:s");
    $expire =  date('Y-m-d H:i:s', strtotime($hoje. ' + 2 days'));
    $sess   = _gen_session($user_id);
   
    if(!_exec("INSERT 
        INTO session (user_id, hash, expire, ativo) 
        VALUES($user_id, '$sess', '$expire', 1)")) 
    _error(500, 'Server problem');

    if($device != ""){
        include "notification.php";
        _save_device($user_id, $device);
    }

    return _response([], '', $sess);
   
}


/**
 * @function:forgot
 * @pool:public
 */
function forgot(){
    
}


/**
 * @function:refresh_user_client
 * @pool:public
 */
function refresh_user_client(){
    sleep(1);
    $user = _user();
    if(!$user || !$user instanceof libs\app\user\UserClient) _error();
    // altera a sessão antiga para uma nova
}


/**
 * @function:refresh_user_admin
 * @pool:public
 */
function refresh_user_admin(){
    sleep(1);
    $user = _user();
    if(!$user || !$user instanceof libs\app\user\UserAdmin) _error();
    // altera a sessão antiga para uma nova
}


/**
 * @function:load_me
 * @pool:public
 * @template: $user_slug conectou-se
 */
function load_me(){
    
    sleep(1);

    $user = _user();
    if(!$user) _error(401, 'Não é um usuário');

    $response = [];

    _activity([
        "user_slug"=>$user->slug()
    ]);

    $user_a = $user->to_array();

    if($user instanceof libs\app\user\UserClient){
        $clie_a = $user->getClientArray();
        $user_a['client_nome'] = $clie_a['client_nome'];
        $user_a['client_slug'] = $clie_a['client_slug'];
        $user_a['client_path'] = $clie_a['client_path'];

        $id = $user_a['id'];

        $pages = _query(
            "SELECT
                view_page.slug       as page_slug,
                view_page.nome       as page_nome,
                view_page.icon       as page_icon,
                view_subpage.nome    as subpage_nome,
                view_subpage.slug    as subpage_slug,
                view_subpage.icon    as subpage_icon
            FROM view_subpage 
                JOIN view_page       ON view_page.id = view_subpage.view_page_id 
                JOIN permission_pool ON permission_pool.id = view_subpage.permission_pool_id
                JOIN user_permission ON user_permission.permission_pool_id = permission_pool.id
                JOIN user            ON user.id = user_permission.user_id
            WHERE 
                user.id = $id 
            ORDER BY 
                view_page.main    DESC, 
                view_subpage.main DESC,
                view_subpage.id   DESC;
        ")->fetchAllAssoc();

        $pagesArray = [];

        foreach ($pages as $page) {
            if(!isset($pagesArray[$page['page_slug']]))
                $pagesArray[$page['page_slug']] = [
                    'nome' => $page['page_nome'],
                    'icon' => $page['page_icon'],
                    'slug' => $page['page_slug'],
                    'subpages' => []
                ];
            
            $pagesArray[$page['page_slug']]['subpages'][] = [
                'nome' => $page['subpage_nome'],
                'icon' => $page['subpage_icon'],
                'slug' => $page['subpage_slug']
            ];
        }

        $response['pages'] = array_values($pagesArray);
    }

    $response['user'] = $user_a;
    return _response($response);

}   

/**
 * @function:get_me
 * @pool:public
 */

function get_me(){
    $user = _user();
    if(!$user) _error();
    return _response($user->to_array());
}


/**
 * @function:change_me
 * @pool:public
 */

 function change_me($nome, $oldPass = "", $newPass = ""){

    sleep(1);

    $user = _user();
    if(!$user) _error();
    $id = $user->id;

    if($oldPass == ""){
        if(!_exec("UPDATE user SET nome = '$nome' WHERE id = $id"))
            _error(500, 'server error');
        return;
    }
    
    $query = _query("SELECT senha FROM user WHERE id = $id");
    if($query->rowCount() == 0) _error();
    $senha = $query->fetchAssoc()['senha'];
   
    if(!password_verify($oldPass, $senha)) _error(500, "Senha incorreta!");

    $passEnc = password_hash($newPass, PASSWORD_DEFAULT);
    
    if(!_exec("UPDATE user SET nome = '$nome', senha = '$passEnc' WHERE id = $id"))
         _error(500, 'server error');

}