<?php 

namespace libs\app\user;

use libs\app\user\User as User;

class UserClient extends User {

    private $client_nome,
            $user_master,
            $client_slug;

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
        $client_slug
        ) {
            $this->id      = $id;
            $this->nome    = $nome;
            $this->slug    = $slug;
            $this->email   = $email;
            $this->session = $session;
            $this->valid   = $valid_session;
            # variaveis do cliente
            $this->client_nome  = $client_nome;
            $this->user_master  = $user_master;
            $this->client_slug  = $client_slug;
    }

    public function getClientArray(){
        return [
            "client_nome"  => $this->client_nome,
            "user_master"  => $this->user_master,
            "client_ativo" => $this->client_ativo,
            "client_slug"  => $this->client_slug,
        ];
    }
}