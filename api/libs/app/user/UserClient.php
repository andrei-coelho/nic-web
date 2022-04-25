<?php 

namespace libs\app\user;

use libs\app\user\User as User;

class UserClient extends User {

    private $client_nome,
            $user_master,
            $client_slug,
            $client_id;

    public function __construct(
        $id,
        $nome,
        $slug,
        $email,
        $session,
        $session_expire,
        $valid_session,
        # variaveis do cliente
        $client_nome,
        $user_master,
        $client_slug,
        $client_id
        ) {
            $this->id      = $id;
            $this->nome    = $nome;
            $this->slug    = $slug;
            $this->email   = $email;
            $this->session = $session;
            $this->valid   = $valid_session;
            $this->expire  = $session_expire;
            # variaveis do cliente
            $this->client_nome  = $client_nome;
            $this->user_master  = $user_master;
            $this->client_slug  = $client_slug;
            $this->client_id    = $client_id;
            $this->isClient     = true;

    }

    public function getClientArray(){
        return [
            "client_nome"  => $this->client_nome,
            "client_slug"  => $this->client_slug,
            "client_id"    => $this->client_id
        ];
    }
}