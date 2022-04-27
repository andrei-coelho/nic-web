<?php 

function _unique_hash($slug){
    return md5(uniqid(rand(), true).$slug);
}

function _gen_private_key($user_id = 0){
    return hash('sha512', _salt().($user_id > 0 ? $user_id : _user()->id()).mt_rand(0,1000).date('d-m-Y_h:i:s')."_private_");
}

function _gen_session(int $id_user){
    
    $new_session = hash('sha256', _salt().$id_user.mt_rand(0,1000).date('d-m-Y_h:i:s')."_session_");
    
    $hoje = new DateTime();
    $hoje->modify('+1 day');
    $expire = $hoje->format('Y-m-d H:i:s');
    
    $status = _exec("INSERT INTO user_sessions (session, user_id, expire) VALUES ('$new_session', $id_user, '$expire')");
    
    return $status ? [$new_session, $expire] : false;
}

function _gen_human_session($user_email){
    $sname = explode('@', $user_email)[0]; 
    sleep(1);
    return 'session-'.trim($sname).'-'.date('dmY-his');
}