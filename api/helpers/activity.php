<?php 

use libs\app\Activity as activity; 
use libs\app\Notification as Notification;

function _activity(array $vars = []){
    activity::register($vars);
}

function _get_description($template){
    
    if(!activity::isRegister()) 
        return false;
    
    return activity::genDescription($template);
   
}

function _notify($notification_service, $vars = []){
    
    $user = _user();
    
    if(!$user || !$user instanceof libs\app\user\UserClient) return false;

    $client_id = $user->getClientArray()['client_id'];
    $user_id = $user->id;

    return Notification::sendMessagesTo($client_id, $user_id, $notification_service, $vars);
}