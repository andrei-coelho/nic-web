use nic_db;

/* 1 giga = 1073741824 bytes */

SELECT 
	dropbox_tk.id as token_id,
	dropbox_tk.account_email,
    dropbox_tk.limit_size,
    (SELECT 
		sum(max_byte_cloud) 
        FROM client_path 
        WHERE dropbox_tk_id = token_id
	) as total_used
FROM dropbox_tk;