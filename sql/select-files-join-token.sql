use nic_db;

SELECT 
		file_client.id,
		file_client.hash_file,
		file_client.mime_type  as ext,
        directory.hash_dir     as client_path,
        directory.client_id    as client_id,
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
		JOIN client_path ON directory.id = client_path.directory_id 
		JOIN dropbox_tk  ON client_path.drobox_tk_id = dropbox_tk.id
   WHERE
		file_client.ghost = 0
        
