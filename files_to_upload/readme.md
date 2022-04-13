# files_to_upload

Esta é pasta temporária dos arquivos que os usuários do cliente salvaram para serem armazenadas no dropbox.

## Porque fazer isso?

O dropbox limita um número de arquivos a serem armazenados ao mesmo tempo. Isso evitará erros e irá manter o sistema ativo até que seja possível armazenar os arquivos no dropbox.

## Quem vai armazenar os arquivos?

Esta pasta será acessada pelo módulo 'task' diariamente usando cron (agendador de tarefas) do linux.

Este módulo irá operar de tempos em tempos fazendo as cópias dos arquivos para o dropbox, salvando os dados no banco de dados e deletando os arquivos das pastas dos clientes que já foram copiados.

links úteis:

guia de performance:
https://developers.dropbox.com/pt-br/dbx-performance-guide

documentação para upload de arquivos grandes: https://www.dropbox.com/developers/documentation/http/documentation#files-upload_session-start

Use este método da biblioteca para arquivos grandes
https://github.com/spatie/dropbox-api/blob/4558a26c9bce783fa34d8c9943c39f4a4f7a30f7/src/Client.php#L434

### Para multiplos arquivos:

```php

$client = new Client($key);

$arquivos = [$arquivo1, $arquivo2, $arquivo3];

foreach($arquivos => $arquivo){
    
    $stream = $client->getStream($arquivo);

    $cursor = $client->uploadChunk(
        0, 
        $stream, 
        $chunkSize, 
        null
    );

    while (! $stream->eof()) {
        $cursor = $client->uploadChunk(
            1, 
            $stream, 
            $chunkSize, 
            $cursor
        );
    }
    
    $client->uploadSessionFinish('', $cursor, $path, $mode);

}


