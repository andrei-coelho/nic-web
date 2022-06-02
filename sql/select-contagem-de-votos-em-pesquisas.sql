use nic_db;
SELECT 
	(SELECT 
		count(user_resposta.id) as total_resposta 
        FROM user_resposta 
        WHERE user_resposta.pesquisa_id = 5
	) as total_pessoas,
	count(*) as votos_option, 
	respostas.option_id, 
	respostas.option_valor,
    respostas.pergunta_id,
	respostas.pergunta_valor
FROM (
	SELECT  
		options.valor  as option_valor,
        options.id     as option_id,
        pergunta.valor as pergunta_valor,
        pergunta.id    as pergunta_id
    FROM resposta
    JOIN options       ON options.id = resposta.option_id 
    JOIN pergunta      ON pergunta.id = options.pergunta_id
    JOIN user_resposta ON user_resposta.id = resposta.user_resposta_id
    LEFT JOIN user_resposta_profile ON user_resposta_profile.user_resposta_id = user_resposta.id
    WHERE user_resposta.pesquisa_id = 5
    -- data > -- AND user_resposta.data_resposta >= '2022-06-01 00:00:00' AND user_resposta.data_resposta <= '2022-06-02 23:59:59'
    -- -- AND user_resposta_profile.idade > 30
) as respostas 
GROUP BY respostas.option_id 
ORDER BY respostas.pergunta_id, respostas.option_id;