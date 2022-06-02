use nic_db;
SELECT 
	count(*) as votos_total, 
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
    -- data > -- WHERE user_resposta.data_resposta >= '2022-06-01 00:00:00' AND user_resposta.data_resposta <= '2022-06-02 23:59:59'
    -- -- WHERE user_resposta_profile.idade > 30
) as respostas 
GROUP BY respostas.option_id 
ORDER BY respostas.pergunta_id, respostas.option_id;