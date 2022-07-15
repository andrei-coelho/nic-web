use nic_db;

# usuário pode escolher receber
SELECT 
	notification_service.id,
	notification_service.nome as notification_nome,
    notification_service.grupo,
    notification_service.icon,
    0 as stats
FROM 
	notification_service 
    WHERE notification_service.permission_pool_id IN (18,16,3,8) # permissões do usuario
UNION ALL
# usuário escolheu
SELECT 
	notification_service.id,
	notification_service.nome as notification_nome,
    notification_service.grupo,
    notification_service.icon,
	1 as stats
FROM 
	notification_service
    JOIN notification_service_user ON notification_service_user.notification_service_id = notification_service.id 
    WHERE notification_service_user.user_id = 14;




    