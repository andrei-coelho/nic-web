<?php 

use libs\app\DropBox as dropbox;

ini_set("memory_limit", "-1");
set_time_limit(0);

$hoje  = date("Y-m-d H:i:s");
$query = _query(
    "SELECT 
        file_to_delete.id,
        file_to_delete.dropbox_hash_id,
        /* a partir daqui */
        directory.client_id    as client_id,
        directory.hash_dir     as client_path,
        dropbox_tk.id          as dropbox_id,
        dropbox_tk.refresh_token,
        dropbox_tk.app_key,
        dropbox_tk.secret_key,
        dropbox_tk.temp_key,
    (CASE 
        WHEN(dropbox_tk.expire_temp_key < '$hoje' 
             OR dropbox_tk.expire_temp_key IS NULL) 
               THEN 1 ELSE 0 
     END) as expired
    
    FROM file_to_delete
        JOIN dropbox_tk  ON file_to_delete.dropbox_tk_id = dropbox_tk.id
        JOIN client_path ON client_path.dropbox_tk_id = dropbox_tk.id 
        JOIN directory   ON directory.id = client_path.directory_id 
    
    WHERE 
        file_to_delete.deleted = 0
");

$filesToDelete = $query->fetchAllAssoc();
foreach ($filesToDelete as $file) {
    if((new dropbox($file['client_id'], $file))
    ->deleteFile($file['dropbox_hash_id']))
        _exec("UPDATE file_to_delete SET deleted = 1 WHERE id = ".$file['id']);
    sleep(1);
}

return true;