<?php 

function _unique_hash($slug){
    return md5(uniqid(rand(), true).$slug);
}

function _gen_private_key($user_id = 0){
    return hash('sha512', _salt().($user_id > 0 ? $user_id : _user()->id()).mt_rand(0,1000).date('d-m-Y_h:i:s')."_private_");
}

function _gen_session($id_user){
    return hash('sha256', _salt().$id_user.mt_rand(0,1000).date('d-m-Y_h:i:s')."_session_");
}

function _gen_human_session($user_email){
    $sname = explode('@', $user_email)[0]; 
    sleep(1);
    return 'session-'.trim($sname).'-'.date('dmY-his');
}