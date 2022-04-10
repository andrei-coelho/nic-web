use nic_db;

/*
 # Verifica se o usuário tem permissão para acessar a função
 # retorna o usuário 
*/
# service = files
# function = upload
SELECT 
	user.id as user_id,
    user.ativo as user_ativo,
    user.nome,
    user.email,
    user.slug,
	client.ativo as client_ativo,
    client.slug as client_slug,
    client.nome as client_nome,
    user_client.master as user_master,
	session.expire as session_expire,
	(case when(session.expire < '2022-04-09 20:16') THEN 0 ELSE 1 END) as valid_session
FROM user 
	JOIN session ON session.user_id = user.id
	JOIN user_client ON user_client.user_id = user.id
	JOIN client ON client.id = user_client.client_id 
		LEFT JOIN user_permission ON user.id = user_permission.user_id
		LEFT JOIN permission_pool ON permission_pool.id = user_permission.permission_pool_id
		LEFT JOIN permission_func ON permission_func.permission_pool_id = permission_pool.id
        LEFT JOIN service_function ON service_function.id  = permission_func.service_function_id
        LEFT JOIN service ON service.id = service_function.service_id
WHERE 
	(( user_client.master = 1) 
    OR (service.slug = 'tasks' AND service_function.slug = 'upload_file' AND NOT service_function.slug = 'logar'))
    AND session.hash = 'session-andrei2' AND session.ativo = 1;
    
     
	