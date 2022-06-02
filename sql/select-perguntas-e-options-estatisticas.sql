use nic_db;

SELECT 
		options.valor      as option_valor,
		options.id         as option_id,
		pergunta.valor     as pergunta_valor,
        pergunta.id        as pergunta_id,
        pesquisa.titulo    as pesquisa_titulo,
        pesquisa.createdAt as criado_em
FROM    options
		JOIN pergunta ON pergunta.id = options.pergunta_id 
		JOIN pesquisa ON pesquisa.id = pergunta.pesquisa_id
WHERE   pesquisa.id = 5;
		