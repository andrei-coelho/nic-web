use nic_db;

SELECT 
	file_client.nome,
	file_client.hash_file as hashId,
	file_client.mime_type as ext,
	file_client.public    as publico,
	(CASE WHEN(true) THEN 'file' END) as `type`
FROM file_client
    JOIN file_client_tag ON file_client_tag.file_client_id = file_client.id
    JOIN  directory ON directory.id = file_client.directory_id
	WHERE ghost = 0 
		AND directory.client_id = 1
		AND file_client.public = 1 
		AND (
			file_client.nome LIKE '%minion%' OR
			file_client_tag.nome like 'minion' OR 
			file_client_tag.nome like 'minions' OR
			file_client_tag.nome like 'vivo'
		)
    GROUP BY file_client.hash_file
    