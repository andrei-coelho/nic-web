<?php 


namespace libs\app;


class Notification {


    private static $url = 'https://fcm.googleapis.com/fcm/send';


    private static function getDevices($client_id, $user_id, $notification_service, $vars = []){
        
        $query = _query(
        "SELECT
             notification_service.nome,
             notification_service.mask_message,
             devices.device_key,
             user.id as `user_id`,
             notification_service.id as notification_service_id

            FROM 
                  devices 
             JOIN user                      ON devices.user_id         = user.id 
             JOIN notification_service_user ON user.id                 = notification_service_user.user_id
             JOIN notification_service      ON notification_service.id = notification_service_user.notification_service_id
             JOIN user_client               ON user_client.user_id     = user.id 
             JOIN client                    ON client.id               = user_client.client_id
            
            WHERE 
                (
                    notification_service.slug = '$notification_service'
                    AND client.id = $client_id
                    AND devices.ativo = 1
                ) 
                AND NOT user.id = $user_id;
        ");

        $notify = [
            "registration_ids" => [],
            "notification" => []
        ];

        if(!$query || $query->rowCount() == 0) return ["notify" => $notify, "to_insert" =>  false];

        $to_insert = [];
        $first = true;
        $agora = date("Y-m-d h:i:s");

        foreach ($query->fetchAllAssoc() as $row) {
            
            if($first){
                $notify["notification"]["title"] = $row['nome'];
                $notify["notification"]["body"] = $row['mask_message'];
                foreach ($vars as $key => $value) 
                    $notify["notification"]["body"] 
                    = str_replace($key, $value, $notify["notification"]["body"]);
                 
            }

            $to_insert = "(".$row['notification_service_id'].", ".$row['user_id'].", \"".$notify["notification"]["body"]."\", \"$agora\"),";
            
            $notify["registration_ids"][] = $row['device_key'];

        }
        
        return ["notify" => $notify, "to_insert" =>  substr($to_insert, 0, -1)];

    }


    public static function sendMessagesTo($client_id, $user_id, $notification_service, $vars = []){

        $data = self::getDevices($client_id, $user_id, $notification_service, $vars);
        if(!$data['to_insert']) return;

        $fields = $data['notify'];
        try {
            _exec("INSERT INTO notification_user (notification_service_id, user_id, `text`, `data`) VALUES ".$data["to_insert"]);
        } catch (\Exception $e) {
            // nada... Só não quero que pare a execução por causa disso
        }
        
        $headers  = [
            'Authorization: key=' . _fmc_key(),
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, self::$url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        
        $status = curl_exec($ch);
        curl_close($ch);

        return $status !== false;

    }

}