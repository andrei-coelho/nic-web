use nic_db;

SELECT
	notification_service.nome,
	notification_service.mask_message,
    devices.device_key,
    notification_service.slug,
    user.id as user_id,
    notification_service.id as notification_service_id
FROM 
	devices 
    JOIN user ON devices.user_id = user.id 
    JOIN notification_service_user ON user.id = notification_service_user.user_id
    JOIN notification_service ON notification_service.id = notification_service_user.notification_service_id
    JOIN user_client ON user_client.user_id = user.id 
    JOIN client ON client.id = user_client.client_id
WHERE 
	(
		notification_service.slug = 'pesquisa_criada'
		AND client.id = 1
		AND devices.ativo = 1
	) 
    AND NOT user.id = 12;