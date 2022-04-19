use nic_db;

SELECT 
	user.id
FROM user 
	JOIN session ON session.user_id = user.id
	JOIN user_client ON user.id = user_client.user_id 
WHERE 
	session.hash = 'session-gustavo' AND session.ativo = 1