# getpath client

SELECT directory.hash_dir
FROM directory
JOIN client_path ON directory.id = client_path.directory_id
JOIN client ON client.id = client_path.client_id
WHERE client.id = 1