use nic_db;

	SELECT 
		(
			SELECT 
				SUM(file_client_info.size_bytes) as total
			FROM file_client_info
				JOIN file_client ON file_client_info.file_client_id = file_client.id
				JOIN directory ON directory.id = file_client.directory_id
			WHERE directory.client_id = 1
		)                          as total_used,
		client_path.max_byte_cloud as max_used,
        dropbox_tk.id              as token_id,
		dropbox_tk.account_email   as dropbox_acc
    FROM client
		JOIN client_path ON client.id = client_path.client_id 
		JOIN dropbox_tk  ON dropbox_tk.id = client_path.dropbox_tk_id
   WHERE client.id = 1;
    
	