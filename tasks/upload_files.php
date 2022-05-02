<?php 

use libs\app\DropBox as dropbox;

ini_set("memory_limit", "-1");
set_time_limit(0);

$query = _query(
    "SELECT 
        file_client.id,
        file_client.hash_file,
        file_client.mime_type  as ext,
        directory.client_id    as client_id,
        /* a partir daqui */
        dir_main.hash_dir      as client_path,
        dropbox_tk.id          as dropbox_id,
        dropbox_tk.refresh_token,
        dropbox_tk.app_key,
        dropbox_tk.secret_key,
        dropbox_tk.temp_key,
    (CASE 
        WHEN(dropbox_tk.expire_temp_key < '2022-05-01 15:58:03' 
             OR dropbox_tk.expire_temp_key IS NULL) 
               THEN 1 ELSE 0 
     END) as expired
    FROM file_client
        JOIN directory   ON directory.id = file_client.directory_id
        JOIN client      ON directory.client_id = client.id
        JOIN client_path ON client.id = client_path.client_id 
        JOIN dropbox_tk  ON client_path.drobox_tk_id = dropbox_tk.id

        JOIN directory as dir_main  ON dir_main.id = client_path.directory_id

    WHERE
        file_client.ghost = 0
        AND file_client.saved = 0
        ");

if($query->rowCount()==0) exit("Não há registros...");
$files = $query->fetchAllAssoc();

foreach ($files as $file) {
    
    $id   = $file['id'];
    $filer= $file['hash_file'].".".$file['ext'];
    $hash = (new dropbox($file['client_id'], $file))
    ->uploadFile($filer);
    
    if(!$hash) continue;

    _exec(
        "UPDATE 
            file_client 
        SET 
            dropbox_hash_id = '$hash',
            saved = 1
        WHERE id = $id
    ");

    unlink('../files_to_upload/'.$file['client_path'].'/'.$filer);

}