<?php 

/**
 * @service:notification
 */


function _get_user(){
    $user = _user();
    return !$user ? _error(404, '') : $user;
}


/**
 * @function:get_count_new
 * @pool:public
 */
function get_count_new(){

    $user = _get_user();
    $query = _query("SELECT count(id) as total FROM notification_user WHERE lido = 0 AND user_id = ".$user->id());
    
    return !$query ? _error(404, '') : _response($query->fetchAssoc());
}


/**
 * @function:get_list
 * @pool:public
 */
function get_list(){
    
    $user = _get_user();
    $id = $user->id();
    
    $query = _query(
        "SELECT 
            notification_service.grupo as `local`,
            notification_service.icon,
            notification_service.redirect,
            notification_user.data,
            notification_user.text,
            (CASE WHEN notification_user.lido = 0 THEN 1 ELSE 0 END) as `new`
        FROM 
            notification_user 
            JOIN notification_service ON notification_service.id = notification_user.notification_service_id
            WHERE notification_user.user_id = $id
        ORDER BY notification_user.id DESC;
    ");

    if(!$query) _error(404, '');
    $res = $query->fetchAllAssoc();

    _exec("UPDATE notification_user SET lido = 1 WHERE user_id = $id");

    return _response($res);
}

