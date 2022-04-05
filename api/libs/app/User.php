<?php 

namespace libs\app;

class User {

    // client, admin, app
    private $id, $nome, $email, $level, $session, $expire;
    private static $user;

    private function __construct($id, $nome, $email, $level, $session, $expire){
        $this->id      = $id;
        $this->nome    = $nome;
        $this->email   = $email;
        $this->level   = $level;
        $this->session = $session;
        $this->expire  = $expire;
    }

    public static function generate_by_session($key){
        
        if(self::$user) return;

        $data = date('Y-m-d H:i:s');

        $userSel = _query(
            "SELECT 
                users.id, users.nome, users.email, user_types.slug as level, user_sessions.session, user_sessions.expire
            FROM users 
            JOIN user_types ON user_types.id = users.user_type
            JOIN (
                SELECT MAX(user_sessions.id) as max_id, user_sessions.session, user_sessions.user_id, MAX(user_sessions.expire) as expire
                FROM user_sessions
                WHERE user_sessions.expire > '$data'
                GROUP BY user_sessions.user_id
            ) sess_max ON (sess_max.user_id = users.id)
            JOIN user_sessions ON user_sessions.id = sess_max.max_id
            WHERE user_sessions.session = '$key';
        ");
        
        if(!$userSel || $userSel->rowCount() == 0) return false;
        
        $user = $userSel->fetchAssoc();

        $dhoje   = new \DateTime();
        $dexpire = new \DateTime($user['expire']);

        if($dhoje > $dexpire) return false;
        
        $sessionu = $user['session'];
        $expireu  = $user['expire'];

        if($dhoje->diff($dexpire)->h < 1){
            $nsess = _gen_session((int)$user['id']);
            if(!$nsess) return false;
            $sessionu = $nsess[0];
            $expireu  = $nsess[1];
        }
        
        self::$user = new User($user['id'], $user['nome'], $user['email'], $user['level'], $sessionu, $expireu);
        
        return true;
    }

    public static function generate_by_secret($secret){
        
        if(self::$user) return;
        
        $userSel = _query(
            "SELECT 
                users.id, users.nome, users.email, user_types.slug as level
            FROM users 
                JOIN user_types ON user_types.id = users.user_type
                JOIN secret_keys ON secret_keys.user_id = users.id
            WHERE secret_keys.secret_key = '$secret' AND ativo = 1;
        ");
        
        if(!$userSel || $userSel->rowCount() == 0) return false;
        
        $user = $userSel->fetchAssoc();
        self::$user = new User($user['id'], $user['nome'], $user['email'], $user['level'], "null", "0000-00-00 00:00:00");
        
        return true;
    }

    public static function get_user(){
        return self::$user;
    }

    public function id(){
        return $this->id;
    }

    public function level(){
        return $this->level;
    }

    public function nome(){
        return $this->nome;
    }

    public function email(){
        return $this->email;
    }

    public function session(){
        return $this->session;
    }

    public function to_string(){
        return 
        "[id:".$this->id ." | ".
        "nome:".$this->nome ." | ".
        "email:".$this->email." | ".
        "level:".$this->level." | ".
        "session:".$this->session." | ".
        "expire:".$this->expire."]";
    }

    public function to_array(){
        return [
            "id"      => $this->id,
            "nome"    => $this->nome,
            "email"   => $this->email,
            "level"   => $this->level,
            "session" => $this->session,
            "expire"  => $this->expire,
        ];
    }

}