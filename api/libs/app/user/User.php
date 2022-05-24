<?php 

namespace libs\app\user;

abstract class User {

    public $id; // deixe isso público

    protected $session;
    protected $expire;
    protected $slug;
    protected $email;
    protected $nome;
    protected $valid;
    protected $isClient = false;

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

    public function getPermissions(){
        return _query(
            "SELECT 
                    permission_pool.slug
               FROM 
                    user_permission 
               JOIN permission_pool ON user_permission.permission_pool_id = permission_pool.id
              WHERE user_permission.user_id = ".$this->id)
        ->fetchAllAssoc();
    }

    public function isValidSession(){
        return $this->valid;
    }

    public function session(){
        return $this->session;
    }

    public function is_client(){
        return $this->isClient;
    }

    public function id(){
        return $this->id;
    }

    public function slug(){
        return $this->slug;
    }
}