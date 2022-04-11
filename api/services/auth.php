<?php 

// $passEnc = password_hash($pass, PASSWORD_DEFAULT);

function logar(){

}

function login($pass, $email){
    
    $selUser = _query(
        "SELECT 
		    users.nome, users.id, users.email, users.senha, user_types.slug as level
        FROM users JOIN user_types ON user_types.id = users.user_type
        WHERE users.email = '$email';
    ");
    
    if(!$selUser) _error(500, 'Ocorreu um problema ao tentar realizar o login. Tente novamente mais tarde.');
    if($selUser->rowCount() == 0) _error(404, 'Usuário não existe ou a senha está incorreta');
    
    $user = $selUser->fetchAssoc();
    
    $status = password_verify($pass, $user['senha']);
    
    if($status){
        $sess = _gen_session($user['id']);
        if(!$sess) _error(500, 'Falha no servidor ao tentar gerar uma sessão');
    }
    unset($user['senha']);
    return $status ? _response($user, $sess[0]) : _error(404, 'Usuário não existe ou a senha está incorreta');

}

function forgot(){
    
}