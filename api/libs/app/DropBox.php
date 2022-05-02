<?php 

namespace libs\app;

include "../api/external_libs/vendor/autoload.php";

class Dropbox {

    private $refresh_token, $app_key, $secret_key;
    private $client_id, $dropbox_id;
    private $client_path;
    private $temp_key;
    private $client; // client spatie

    private static $temporary_tokens = [];

    function __construct(int $client_id, $tokens = false){
        
        $this->client_id = $client_id;

        if(!$tokens) $tokens = $this->getTokens();
        if(!$tokens) return;

        $this->refresh_token = $tokens['refresh_token'];
        $this->app_key       = $tokens['app_key'];
        $this->secret_key    = $tokens['secret_key'];
        $this->client_path   = $tokens['client_path'];
        $this->dropbox_id    = $tokens['dropbox_id'];
        
        if(!isset(self::$temporary_tokens[$client_id])){
            $this->temp_key = $tokens['expired'] == 1 
                        ? $this->getTemporaryToken()
                        : $tokens['temp_key'];
            self::$temporary_tokens[$client_id] = $this->temp_key;
            if($tokens['expired'] == 1)
                $this->save_temporary_key();
        } else {
            $this->temp_key = self::$temporary_tokens[$client_id];
        }

        $this->client = new \Spatie\Dropbox\Client($this->temp_key);        
        
    }

    private function getTokens(){
        $hoje  = date("Y-m-d H:i:s");
        $query = _query(
            "SELECT 
                    dropbox_tk.id as dropbox_id,
                    dropbox_tk.refresh_token,
                    dropbox_tk.app_key,
                    dropbox_tk.secret_key,
                    dropbox_tk.temp_key,
                    directory.hash_dir as client_path,
                    (CASE WHEN(dropbox_tk.expire_temp_key < '$hoje') THEN 1 ELSE 0 END) as expired
               FROM dropbox_tk
               JOIN client_path ON client_path.drobox_tk_id = dropbox_tk.id
               JOIN directory   ON directory.id = client_path.directory_id
              WHERE
                    client_path.client_id = ".$this->client_id);
        if($query->rowCount()== 0) return false;
        return $query->fetchAssoc();
    }

    private function getTemporaryToken(){
        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request("POST", "https://{$this->app_key}:{$this->secret_key}@api.dropbox.com/oauth2/token", [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->refresh_token,
                ]
            ]);
            if ($res->getStatusCode() == 200) {
                return json_decode($res->getBody(), TRUE)['access_token'];
            } else {
                return false;
            }
        }
        catch (Exception $e) {
            // $this->logger->error("[{$e->getCode()}] {$e->getMessage()}");
            return false;
        }
    }

    private function save_temporary_key(){
        
        $expire = date("Y-m-d H:i:s", strtotime('+3 hours'));
        _exec(
            "UPDATE dropbox_tk 
            SET 
            temp_key = '$this->temp_key',
            expire_temp_key = '$expire'
            WHERE id = $this->dropbox_id");
    }

    public function getTemporaryFileLink($hash_file){
        
        $query = _query("SELECT dropbox_hash_id FROM file_client WHERE hash_file = '$hash_file'");
        
        return $query->rowCount() > 0 
        ? $this->client->getTemporaryLink($query->fetchAssoc()['dropbox_hash_id'])
        : "";
        
    }

    public function uploadFile($file_name){
        try {
            $file = "../files_to_upload/$this->client_path/$file_name";
            if(file_exists($file)){
                $response = $this->client->upload($file_name, file_get_contents($file));
                return $response['id'];
            } else {
                echo $file."<br>";
                return false;
            }
        } catch(\Exception $e){
            return false;
        }
    }


}