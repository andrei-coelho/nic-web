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

/**
 * @function:save_device
 * @pool:public
 */
function save_device($device){

    $user = _get_user();
    $id = $user->id();
    _save_device($id, $device);

}

function _save_device($id, $device){

    if(!change_user_device($id, $device)){
        try {
            _exec("INSERT INTO devices (user_id, device_key) VALUES ($id, '$device')");    
        } catch (\Exception $th1) {
            desativar_device($device);
        }
    }

}

function change_user_device($id, $device){

    $getDevice = _query("SELECT user_id, ativo FROM devices WHERE device_key = '$device'");

    if($getDevice->rowCount() > 0){
        
        $deviceS = $getDevice->fetchAssoc();

        $sets = "";
        if($deviceS['ativo'] == 0)
            $sets .= " ativo = 1,";
        $sets = " user_id = $id ";
        
        if(!_exec("UPDATE devices SET $sets WHERE device_key = '$device'")) 
            return false;

        return true;
    }

    return false;

}

function desativar_device($device){
    try {
        _exec("UPDATE devices SET ativo = 0 WHERE device_key = '$device'"); 
    } catch (\Exception $th2) {
        return false;
    }
    return true;
}
