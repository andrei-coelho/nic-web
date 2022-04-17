<?php 

include "sqli.php";
include "session.php";

use libs\app\user\UserFactory as UserFactory;
use src\Request as request;

function _user(){
    
    $request = new request(['req', 'route', 'func']);
    $session = _data('session');
    if(!$session) return false;

    return 
           ($user = UserFactory::get_user())
        ?  $user 
        : UserFactory::generate_by_session(
            $session, 
            $request->vars['route'], 
            $request->vars['func']
        );
}