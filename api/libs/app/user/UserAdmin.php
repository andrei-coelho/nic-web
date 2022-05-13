<?php 

namespace libs\app\user;

use libs\app\user\User as User;

class UserAdmin extends User {

    private $ghost;

    public function __construct(
        $id,
        $nome,
        $slug,
        $email,
        $session,
        $session_expire,
        $user_ghost_id,
        $valid_session
        ) {
            $this->id      = $id;
            $this->nome    = $nome;
            $this->slug    = $slug;
            $this->email   = $email;
            $this->session = $session;
            $this->expire  = $session_expire;
            $this->ghost   = $user_ghost_id;
            $this->valid   = $valid_session;
    }

    public function ghostId(){
        return $this->ghost;
    }

}