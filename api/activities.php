<?php 

(function(){
    
    $user = _user();

    if(!$user->is_client()
        // && $user->is_ghost()
    ) return;

    if(!($description = _get_description($user->template())))
        return;

    $serviceId = $user->service_function_used();

    $idActivity = 
    _exec("INSERT INTO 
        activity(service_function_id, description, moment)
        VALUES  ($serviceId, '$description', NOW())", true);

    $userId = $user->id();

    _exec("INSERT INTO 
        user_activity(user_id, activity_id)
        VALUES ($userId,$idActivity)");

})();