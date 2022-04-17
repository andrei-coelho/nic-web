<?php 

namespace libs\app\user;

abstract class User {

    protected $session;
    protected $expire;
    protected $slug;
    protected $email;
    protected $nome;
    protected $id;
    protected $valid;

    protected function upgradeSession(){
        // atualiza sessão do usuário se for necessário
        //echo "fez upgrade";
    }

    public function to_array(){
        return [
            "id"      => $this->id,
            "nome"    => $this->nome,
            "email"   => $this->email,
            "slug"    => $this->slug,
            "session" => $this->session,
            "expire"  => $this->expire
        ];
    }

    public function isValidSession(){
        return $this->valid;
    }

    public function session(){
        return $this->session;
    }
}