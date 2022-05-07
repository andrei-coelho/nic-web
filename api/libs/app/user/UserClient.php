<?php 

namespace libs\app\user;

use libs\app\user\User as User;

class UserClient extends User {

    private $client_nome,
            $user_master,
            $client_slug,
            $client_path,
            $template,
            $user_ghost,
            $client_id,
            $service_id;

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
        $client_path,
        $client_id,
        $max_byte,
        $template,
        $user_ghost,
        $service_function_id
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
            $this->client_path  = $client_path;
            $this->isClient     = true;
            $this->max_byte     = $max_byte;
            $this->template     = $template;
            $this->user_ghost   = $user_ghost == 1;
            $this->service_id   = $service_function_id;

    }

    public function getClientArray(){
        return [
            "client_nome"  => $this->client_nome,
            "client_slug"  => $this->client_slug,
            "client_id"    => $this->client_id,
            "client_path"  => $this->client_path,
            "max_byte"     => $this->max_byte,
        ];
    }

    public function getTotalBytes(){
        $total = _query(
            "SELECT 
                    SUM(file_client_info.size_bytes) as total
                FROM file_client_info
                JOIN file_client ON file_client_info.file_client_id = file_client.id
                JOIN directory ON directory.id = file_client.directory_id
            WHERE 
                file_client.ghost = 0 AND
                directory.client_id = $this->client_id;"
        )->fetchAssoc()['total'];
        return $total;
    }


    public function service_function_used(){
        return $this->service_id;
    }

    public function template(){
        return $this->template;
    }

    public function is_ghost(){
        return $this->user_ghost;
    }

}