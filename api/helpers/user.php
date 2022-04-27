<?php 

include "sqli.php";
include "session.php";

use libs\app\user\UserFactory as UserFactory;
use src\Request as request;

function _user_file(){
    
    $req = _request(['req', 'session', 'client_dir', 'file']);
    if(!$req->vars['file'] || !$req->vars['client_dir'] || !$req->vars['session']) 
        return false;

    $path = $req->vars['client_dir'];
    $file = $req->vars['file'];

    $user = UserFactory::generate_by_session (
        $req->vars['session'], 
        'file_tools', 
        'get'
    );
    
    if(!$user) return false;

    $hash_file = explode('.', $file)[0];

    if($user->is_client() &&
        (   _query(
                "SELECT id 
                FROM directory 
                WHERE hash_dir = '$path' AND client_id = ".$user->getClientArray()['client_id']
            )->rowCount() == 0 || 
            _query(
                "SELECT id
                FROM file_client 
                WHERE hash_file = '$hash_file'")->rowCount() == 0
        )
    ) return false;

    return [$user , $path.'/'.$req->vars['file']];
}


function _user(){
    
    if($user = UserFactory::get_user())
        return $user;
    
    $request = new request(['req', 'route', 'func', 'sess']);

    if( !($session = _data('session')) && 
        !($session = $request->vars['sess']))
        return false;

    return UserFactory::generate_by_session (
        $session, 
        $request->vars['route'], 
        $request->vars['func']
    );

}