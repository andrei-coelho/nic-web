use nic_db;

SELECT 
	notification_service.grupo as `local`,
    notification_service.icon,
    notification_service.redirect,
	notification_user.data,
    notification_user.text,
    (CASE WHEN notification_user.lido = 0 THEN 1 ELSE 0 END) as `new`
FROM 
	notification_user 
    JOIN notification_service ON notification_service.id = notification_user.notification_service_id
    WHERE notification_user.user_id = 14
ORDER BY notification_user.id DESC;