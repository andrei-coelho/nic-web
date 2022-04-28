use nic_db;


SELECT 
	file_client_info.createdAt,
    file_client_info.editedAt,
    user.slug
    FROM file_client_info
    JOIN file_client ON file_client.id = file_client_info.file_client_id
    JOIN directory ON file_client.directory_id = directory.id
    JOIN client ON directory.client_id = client.id
    JOIN user ON file_client_info.created_user_id = user.id
    WHERE client.id = 1 AND file_client.hash_file = 'c87fc5a21a472fda43dd59deb2b45522';