<?php 

include "sqli.php";
include "session.php";

use libs\app\user\UserFactory as UserFactory;
use src\Request as request;

function _user(){
    
    $request = new request(['req', 'route', 'func', 'session']);
    
    return 
           ($user = UserFactory::get_user())
        ?  $user 
        : ($request->vars['session'] 
            ? UserFactory::generate_by_session(
                $request->vars['session'], 
                $request->vars['route'], 
                $request->vars['func']
            ) 
            : false
        );
}