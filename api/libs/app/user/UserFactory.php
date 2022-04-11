<?php 

namespace libs\app\user;

use libs\app\user\ClientUser as ClientUser;
use libs\app\user\UserAdmin as UserAdmin;
use libs\app\user\User as User;

class UserFactory extends User {

    private static $user;

    public static function get_user(){
        return self::$user;
    }

    public static function generate_by_session(string $session, string $slug_service, string $slug_function){
        
        if(self::$user) return self::$user;

        $userSel =
        _query(
            "SELECT 
                user.id            as user_id,
                user.ativo         as user_ativo,
                user.nome          as user_nome,
                user.email         as user_email,
                user.slug          as user_slug,
                client.ativo       as client_ativo,
                client.slug        as client_slug,
                client.nome        as client_nome,
                user_client.master as user_master,
                session.expire     as session_expire,
                user.admin,
                (case when(session.expire < '2022-04-09 20:16') THEN 0 ELSE 1 END) as valid_session
            FROM user 
                JOIN session ON session.user_id = user.id
                    LEFT JOIN user_admin       ON user_admin.user_id = user.id
                    LEFT JOIN user_client      ON user_client.user_id = user.id
                    LEFT JOIN client           ON client.id = user_client.client_id 
                    LEFT JOIN user_permission  ON user.id = user_permission.user_id
                    LEFT JOIN permission_pool  ON permission_pool.id = user_permission.permission_pool_id
                    LEFT JOIN permission_func  ON permission_func.permission_pool_id = permission_pool.id
                    LEFT JOIN service_function ON service_function.id  = permission_func.service_function_id
                    LEFT JOIN service          ON service.id = service_function.service_id
            WHERE 
                ((user.admin = 1)
                OR (service.slug = '$slug_service' AND service_function.slug = '$slug_function'))
                AND session.hash = '$session' AND session.ativo = 1;"
        );

        if($userSel->rowCount() == 0) return false; 
        
        $userRow = $userSel->fetchAssoc();
        if($userRow['user_ativo'] == 0) return false;
        if($userRow['admin'] == 0 && $userRow['client_ativo'] == 0) return false;
        
        self::$user = $userRow['admin'] == 1 
                    ? new UserAdmin(
                        $userRow['user_id'],
                        $userRow['user_nome'],
                        $userRow['user_slug'],
                        $userRow['user_email'],
                        $session,
                        $userRow['session_expire'],
                        ($userRow['valid_session'] == 1)
                    ) 
                    : new UserClient(
                        $userRow['user_id'],
                        $userRow['user_nome'],
                        $userRow['user_slug'],
                        $userRow['user_email'],
                        $session,
                        $userRow['session_expire'],
                        ($userRow['valid_session'] == 1),
                        $userRow['client_nome'],
                        $userRow['user_master'],
                        $userRow['client_slug']
                    );
        
        self::$user->upgradeSession();

        return self::$user;

    }

}