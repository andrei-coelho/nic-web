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
function logar(){
    return _response([]);
}

/**
 * @function:forgot
 * @pool:public
 */
function forgot(){
    
}


/**
 * @function:load_me
 * @pool:public
 */
function load_me(){
    
    sleep(1);

    $user = _user();
    if(!$user) _error();

    $user_a = $user->to_array();
    if($user instanceof libs\app\user\UserClient){
        $clie_a = $user->getClientArray();
        $user_a['cliente_nome'] = $clie_a['client_nome'];
        $user_a['cliente_slug'] = $clie_a['client_slug'];
    }

    $id = $user_a['id'];

    $pages = _query(
        "SELECT
        view_page.slug as page_slug,
        view_page.nome as page_nome,
        view_page.icon as page_icon,
        view_subpage.nome as subpage_nome,
        view_subpage.slug as subpage_slug,
        view_subpage.icon as subpage_icon
    FROM view_subpage 
        JOIN view_page ON view_page.id = view_subpage.view_page_id 
        JOIN permission_pool ON permission_pool.id = view_subpage.permission_pool_id
        JOIN user_permission ON user_permission.permission_pool_id = permission_pool.id
        JOIN user ON user.id = user_permission.user_id
    WHERE 
        user.id = $id 
    ORDER BY 
        view_page.main DESC, view_subpage.main DESC;")->fetchAllAssoc();

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

    return _response([
        'user'  => $user_a,
        'pages' => array_values($pagesArray)
    ]);

}