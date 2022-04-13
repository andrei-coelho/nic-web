<?php

ini_set("memory_limit", "-1");
set_time_limit(0);

include "../api/external_libs/vendor/autoload.php";

$client = new Spatie\Dropbox\Client(_token());

//$client->createFolder('teste');

print_r($client->upload('teste/test2.wmv', file_get_contents('../files_to_upload/test.wmv')));

/*
echo "<pre>";
print_r($client->listFolder('Teste')); //lista o conteúdo da pasta Teste
echo "</pre>";
*/
//$client->createFolder('Teste'); // cria um folder


# $client->delete('teste'); // deleta o folder ou um arquivo específico

/*
echo "<pre>";
print_r($client->listFolder('Teste')); //lista o conteúdo da pasta Teste
echo "</pre>";
*/