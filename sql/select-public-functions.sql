use nic_db;

SELECT 
	service_function.slug
FROM service_function
	JOIN service ON service.id = service_function.service_id
	JOIN permission_func ON permission_func.service_function_id = service_function.id
	JOIN permission_pool ON permission_func.permission_pool_id = permission_pool.id
WHERE 
	service.slug = 'auth' AND 
    service_function.slug = 'logar' AND
    permission_pool.slug = 'public'