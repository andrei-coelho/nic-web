use nic_db;

#select todos os arquivos

SELECT 
	file_client.nome,
	file_client.hash_file as hashId,
    file_client.mime_type as ext,
    (CASE WHEN(true) THEN 'file' END) as `type`
FROM file_client 
JOIN directory ON directory.id = file_client.directory_id
WHERE ghost = 0 AND directory.hash_dir = '8297f4fd889d0da2ef4abbfe04a021b1';

SELECT
	child.nome,
    child.hash_dir as hashId,
    (CASE WHEN(true) THEN 'dir' END) as `type`
    FROM  directory as child JOIN directory as main ON main.id = child.directory_id
    WHERE main.hash_dir = 'hasslugraizteste'
    
    
    