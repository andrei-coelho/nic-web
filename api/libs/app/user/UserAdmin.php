<?php 

namespace libs\app\user;

use libs\app\user\User as User;

class UserAdmin extends User {

    public function __construct(
        $id,
        $nome,
        $slug,
        $email,
        $session,
        $session_expire,
        $valid_session
        ) {
            $this->id      = $id;
            $this->nome    = $nome;
            $this->slug    = $slug;
            $this->email   = $email;
            $this->session = $session;
            $this->expire  = $session_expire;
            $this->valid   = $valid_session;
    }
    

}