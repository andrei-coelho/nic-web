use nic_db;

SELECT
        pesquisa.id,
        pesquisa.titulo,
        pesquisa.createdAt,
        pesquisa.ativo,
        (
            SELECT count(id) 
            FROM user_resposta 
            WHERE 
            user_resposta.pesquisa_id = pesquisa.id
            AND response = 1
        ) as respostas_total
    FROM pesquisa 
    WHERE 
    pesquisa.client_id = 1
    AND pesquisa.ativo > 0