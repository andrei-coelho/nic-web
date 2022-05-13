use nic_db;

SELECT 
	dropbox_tk.refresh_token,
	dropbox_tk.app_key,
    dropbox_tk.secret_key,
    dropbox_tk.temp_key,
    (CASE WHEN(dropbox_tk.expire_temp_key < '2022-05-01 15:58:03') THEN 1 ELSE 0 END) as expired
	FROM dropbox_tk
	JOIN client_path ON client_path.drobox_tk_id = dropbox_tk.id
	WHERE
		client_path.client_id = 2
	