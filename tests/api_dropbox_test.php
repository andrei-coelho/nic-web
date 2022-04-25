<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

include "../api/external_libs/vendor/autoload.php";

$appKey    = '1e4k5ox2hkwqeu5';
$appSecret = 'xstazbgegcr63rx';

//use Spatie\Dropbox\TokenProvider as TokenProvider;
//$tokenProvider = new  AutoRefreshingDropBoxTokenService ( _token() );
//$client = new  Spatie\Dropbox\Client ( $tokenProvider );
//$client = new Spatie\Dropbox\Client(array($appKey , $appSecret));

function getToken($key, $secret, $refreshToken) {
    try {
        $client = new \GuzzleHttp\Client();
        $res = $client->request("POST", "https://{$key}:{$secret}@api.dropbox.com/oauth2/token", [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
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

$client = new Spatie\Dropbox\Client( getToken($appKey, $appSecret, _refresh_token()));
echo "<pre>";
print_r($client->listFolder('Teste')); //lista o conteúdo da pasta Teste
echo "</pre>";

//$client->createFolder('teste');
// curl  \-d grant_type=authorization_code \-u 1e4k5ox2hkwqeu5:1e4k5ox2hkwqeu5
//print_r($client->upload('teste/corel2.cdr', file_get_contents('../files_to_upload/corel.cdr')));
//$client->createFolder('Teste'); // cria um folder
# $client->delete('teste'); // deleta o folder ou um arquivo específico

/*
echo "<pre>";
print_r($client->listFolder('Teste')); //lista o conteúdo da pasta Teste
echo "</pre>";
*/