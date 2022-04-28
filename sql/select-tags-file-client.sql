use nic_db;

SELECT 
	file_client_tag.id as tag_id,
    file_client_tag.nome
FROM file_client_tag
JOIN file_client ON file_client_tag.file_client_id = file_client.id
JOIN directory ON directory.id = file_client.directory_id
JOIN client ON directory.client_id = client.id
WHERE hash_file = '85b71f905bc84663e953e73e26c696e7' AND client_id = 1