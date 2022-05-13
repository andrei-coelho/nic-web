use nic_db;

SELECT 
	 SUM(file_client_info.size_bytes) as total
	FROM file_client_info
	JOIN file_client ON file_client_info.file_client_id = file_client.id
	JOIN directory ON directory.id = file_client.directory_id
WHERE directory.client_id = 1;