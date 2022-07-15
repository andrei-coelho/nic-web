use nic_db;

-- SEXO

	SELECT 
		user_r.sexo,
		count(user_r.id) as total_sexo
		FROM (
			SELECT user_resposta_profile.id, user_resposta_profile.sexo
			  FROM user_resposta_profile
			  JOIN user_resposta ON user_resposta.id = user_resposta_profile.user_resposta_id
			 WHERE user_resposta.pesquisa_id = 5
		   -- AND NOT user_resposta_profile.sexo = null
		) as user_r
	GROUP BY user_r.sexo;
    /*
-- casado

	SELECT 
		user_r.genero,
		count(user_r.id) as total_genero
		FROM (
			SELECT user_resposta_profile.id, user_resposta_profile.genero
			  FROM user_resposta_profile
			  JOIN user_resposta ON user_resposta.id = user_resposta_profile.user_resposta_id
			 WHERE user_resposta.pesquisa_id = 5
		) as user_r
	GROUP BY user_r.genero
    */