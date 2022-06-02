<?php 

/**
 * @service: pesquisas
 */

function _prefix_table($slug){
    switch ($slug) {
        case 'data'  : return 'user_resposta.data_resposta';
        default: 
            return 
            in_array($slug, ['idade', 'casado', 'sexo', 'filhos', 'genero', 'cor', 'renda_mensal']) ?
            "user_resposta_profile.$slug" : 
            "";
    }
}

function _fix_value($value){
    if(is_string($value)) return "'$value'";
    if(is_bool($value)) return $value ? 1 : 0;
    return $value;
}

/**
 * @function: estatistica_perfil
 * @pool: pesquisas_full
 */
function estatistica_perfil(int $pesquisa_id, string $field){

    _is_pesquisa_cliente($pesquisa_id);

    try {
        $query = _query(
        "SELECT 
            user_r.$field,
            count(user_r.id) as total_$field
            FROM (
                SELECT user_resposta_profile.id, user_resposta_profile.$field
                  FROM user_resposta_profile
                  JOIN user_resposta ON user_resposta.id = user_resposta_profile.user_resposta_id
                 WHERE user_resposta.pesquisa_id = $pesquisa_id 
               AND NOT user_resposta_profile.$field = null
            ) as user_r
        GROUP BY user_r.$field;");

        return _response($query->fetchAllAssoc());

    } catch (\Exception $e){
        _error();
    }

}

/**
 * @function: estatistica_votos
 * @pool: pesquisas_full
 */
function estatistica_votos(int $pesquisa_id, array $filters = []){

    _is_pesquisa_cliente($pesquisa_id);

    /**
     * 
     * se existir 'equals' ele será usado. 
     * Se não existir ou 'equals' for 'false', 
     * o algoritmo irá procura pelo 'range'.
     * Se ambos não existirem, retorna erro.
     * 
     * Exemplo:
     *  {
     *     field:'name',
     *     equals:'value',
     *     range:{               
     *         min:'value',
     *         max:'value'
     *     }
     *  }
     */

    $filterStr = "";

    foreach ($filters as $filter) {
        
        $filterStr .= " AND ";
        $prefix = _prefix_table($filter['field']);

        if(isset($filter['equals'])){
            $filterStr .= $prefix." = ". _fix_value($filter['equals']);
            continue;
        }

        if(isset($filter['min'])){
            $filterStr .= $prefix." >= ". _fix_value($filter['min']);
            continue;
        }

        if(isset($filter['max'])){
            $filterStr .= $prefix." <= ". _fix_value($filter['max']);
            continue;
        }
       
        if(isset($filter['range'])){
            if(!isset($filter['range']['min']) || !isset($filter['range']['min'])) 
                _error();
            $filterStr .= 
                "$prefix >= ". _fix_value($filter['range']['min']).
                " AND ".
                "$prefix <= ". _fix_value($filter['range']['max']);
            continue;
        }

        _error();
    }

    $query = 
        "SELECT 
            (SELECT 
                count(user_resposta.id) as total_resposta 
                FROM user_resposta 
                LEFT JOIN user_resposta_profile ON user_resposta_profile.user_resposta_id = user_resposta.id
                WHERE user_resposta.pesquisa_id = $pesquisa_id
                $filterStr
            ) as total_pessoas,
            count(*) as votos_option, 
            respostas.option_id, 
            respostas.pergunta_id
        FROM (
            SELECT  
                options.id     as option_id,
                pergunta.id    as pergunta_id
            FROM resposta
                 JOIN options       ON options.id = resposta.option_id 
                 JOIN pergunta      ON pergunta.id = options.pergunta_id
                 JOIN user_resposta ON user_resposta.id = resposta.user_resposta_id
            LEFT JOIN user_resposta_profile ON user_resposta_profile.user_resposta_id = user_resposta.id
            WHERE user_resposta.pesquisa_id = $pesquisa_id
            $filterStr
        ) as respostas 
        GROUP BY respostas.option_id 
        ORDER BY respostas.pergunta_id, respostas.option_id;
    ";

    $perguntasQuery = _query(
        "SELECT 
                options.valor      as option_valor,
                options.id         as option_id,
                pergunta.valor     as pergunta_valor,
                pergunta.id        as pergunta_id,
                pesquisa.titulo    as pesquisa_titulo,
                pesquisa.createdAt as criado_em
        FROM    options
           JOIN pergunta ON pergunta.id = options.pergunta_id 
           JOIN pesquisa ON pesquisa.id = pergunta.pesquisa_id
        WHERE   pesquisa.id = $pesquisa_id AND pesquisa.ativo = 1;"
    );

    if(!$perguntasQuery || $perguntasQuery->rowCount() == 0 || !($estats = _query($query))) 
        _error();

    $perguntas = $perguntasQuery->fetchAllAssoc();
    
    $response = [
        "total_pessoas" => 0,
        "titulo"        => $perguntas[0]['pesquisa_titulo'],
        "criado_em"     => date('d/m/Y', strtotime($perguntas[0]['criado_em'])),
        "perguntas"     => []
    ];

    foreach ($perguntas as $pergunta) {
        if(!isset($response['perguntas']["".$pergunta['pergunta_id']])){
            $response['perguntas']["".$pergunta['pergunta_id']] = [
                'valor'   => $pergunta['pergunta_valor'],
                'options' => []
            ];
        }
        $response['perguntas']["".$pergunta['pergunta_id']]['options']["".$pergunta['option_id']] = [
            "valor" => $pergunta['option_valor'],
            "votos" => 0
        ];
    }

    if($estats ->rowCount() == 0) return _response($response);

    $list  = $estats->fetchAllAssoc();
    $first = true;
    foreach ($list as $votos) {
        if($first){
            $response['total_pessoas'] = $votos['total_pessoas'];
            $first = false;
        }
        $response['perguntas']["".$votos['pergunta_id']]['options']["".$votos['option_id']]['votos'] =  $votos['votos_option'];
    }

    return _response($response);

}

/**
 * @function:list_pesquisas
 * @pool:pesquisas_basico
 */
function list_pesquisas(){

    $user      = _user();
    $client_id = $user->getClientArray()['client_id'];

    $query = _query(
        "SELECT
            pesquisa.id,
            pesquisa.titulo,
            pesquisa.createdAt,
            (
                SELECT count(id) 
                FROM user_resposta 
                WHERE 
                user_resposta.pesquisa_id = pesquisa.id
                AND response = 1
            ) as respostas_total
        FROM pesquisa 
        WHERE pesquisa.ativo = 1;
    ");
    if(!$query) _error();
    return _response($query->fetchAllAssoc());
    
}


/**
 * @function:get_new_user_resposta
 * @pool:pesquisas_basico
 */
function get_new_user_resposta(int $pesquisa_id){
    // cria um user_resposta
    _is_pesquisa_cliente($pesquisa_id);

    if(!($user_resposta_id = _exec(
        "INSERT INTO user_resposta (pesquisa_id) 
         VALUES ($pesquisa_id)", true)))
        _error(500, 'server error');
    
    return _response(["user_resposta_id" => $user_resposta_id]);
}

/**
 * @function:salvar_resposta
 * @pool:pesquisas_basico
 */
function salvar_resposta(
        int $user_resposta_id, 
        int $pesquisa_id, 
        array $options, 
        array $profile_values = [],
        array $inputs = []
    ){

    if(count($options) == 0) _error();

    _is_user_resposta_pesquisa($user_resposta_id, $pesquisa_id);

    $query = _query(
        "SELECT 
             options.id as id 
        FROM options 
            JOIN pergunta ON options.pergunta_id = pergunta.id
            JOIN pesquisa ON pesquisa.id = pergunta.pesquisa_id 
        WHERE pesquisa.id = $pesquisa_id
    ");

    if(!$query) _error();
    
    $ids = array_column($query->fetchAllAssoc(), 'id');
    
    $insert = "INSERT INTO resposta (option_id, user_resposta_id) VALUES ";
    foreach ($options as $option){
        if(!in_array($option['id'], $ids)) _error();
        $insert .= "(".$option['id'].", $user_resposta_id),";
    }
    $insert = substr($insert, 0, -1);

    if(!_exec($insert)) 
        _error(500, 'server error');

    if(count($inputs) > 0)
        _salvar_dados_cadastro($user_resposta_id, $pesquisa_id, $inputs);

    if(count($profile_values) > 0)
        _salvar_profile($user_resposta_id, $pesquisa_id, $profile_values);
    
    if(!_exec("UPDATE user_resposta SET response = 1 WHERE id = $user_resposta_id"))
        _error(500, 'server error');

}

function _salvar_profile(int $user_resposta_id, int $pesquisa_id, array $profile_values){

    $insert = "INSERT INTO user_resposta_profile ";
    $fields = "( user_resposta_id, ";
    $values = "( $user_resposta_id,";
    
    foreach ($profile_values as $prof) {
        $fields .= $prof['field'].",";
        $values .= 
            (
                ! in_array($prof['type'], ['int', 'bool', 'float']) 
                ? "'".$prof['value']."'" 
                : ($prof['type'] == 'bool' ? (int) $prof['value'] : $prof['value'])
            ).",";
    }
    
    $insert .= substr($fields, 0, -1).")"." VALUES ".substr($values, 0, -1).")";

    if(!_exec($insert)) 
      _error(500, 'server error');

}

function _salvar_dados_cadastro(int $user_resposta_id, int $pesquisa_id, array $inputs){
    
    $insert = "INSERT INTO user_resposta_cad_value (user_resposta_id, user_resposta_cad_field_id, valor) VALUES ";
    foreach ($inputs as $input) {
        $input_id = $input['id'];
        $value    = $input['value'];
        $insert  .= "($user_resposta_id, $input_id, '$value'),";
    }
    
    $insert = substr($insert, 0, -1);
    if(!_exec($insert)) _error(500, 'server error');

}

/**
 * @function:get_inputs_fields
 * @pool:pesquisas_basico
 */
function get_inputs_fields(int $pesquisa_id){

    _is_pesquisa_cliente($pesquisa_id);

    $query = _query(
        "SELECT id,label,slug
        FROM user_resposta_cad_field WHERE pesquisa_id = $pesquisa_id
    ");
    if(!$query) _error();
    return _response($query->fetchAllAssoc());

}

/**
 * @function:get_profile_fields
 * @pool:pesquisas_basico
 */
function get_profile_fields(int $pesquisa_id){
    
    _is_pesquisa_cliente($pesquisa_id);

    $query = _query("SELECT field, type FROM pesquisa_profile_fields WHERE pesquisa_id = $pesquisa_id");
    if(!$query) _error();
    return _response($query->fetchAllAssoc());

}

/**
 * @function:get_perguntas
 * @pool:pesquisas_basico
 */
function get_perguntas(int $pesquisa_id, $not_keys = true){
    // tabela user_resposta_cad_field

    _is_pesquisa_cliente($pesquisa_id);

    $query1 = _query(
        "SELECT
                 options.valor  as option,
                 options.id     as option_id,
                 pergunta.valor as pergunta,
                 pergunta.id    as pergunta_id,
                 pergunta.type  as type
            FROM options
            JOIN pergunta ON options.pergunta_id = pergunta.id
            JOIN pesquisa ON pesquisa.id = pergunta.pesquisa_id 
        WHERE
            pesquisa.id = $pesquisa_id 
        ORDER BY 
            pergunta.id, options.id
        ASC
    ");
    if(!$query1) _error();
   
    $query2 = _query("SELECT field, type FROM pesquisa_profile_fields WHERE pesquisa_id = $pesquisa_id");
    if(!$query2) _error();
    
    $final = [];
    $final['profile_inputs'] = $query2->fetchAllAssoc();

    $options   = $query1->fetchAllAssoc();
    $perguntas = [];
   
    foreach ($options as $option) {
        
        $k = "".$option['pergunta_id'];
        
        if(!isset($perguntas[$k])){
            $perguntas[$k]['pergunta']['id']   = (int)$option['pergunta_id'];
            $perguntas[$k]['pergunta']['val']  = $option['pergunta'];
            $perguntas[$k]['pergunta']['type'] = $option['type'];
        }

        $perguntas[$k]['options'][] = [
            'id'  => $option['option_id'],
            'val' => $option['option']
        ];
    }

    $final['perguntas'] = array_values($perguntas);

    return _response($final);

}


/**
 * @function:criar_pesquisa
 * @pool:pesquisas_full
 */
function criar_pesquisa($titulo, array $profile_inputs = []){

    $user      = _user();
    $client_id = $user->getClientArray()['client_id'];
    $user_id   = $user->id;

    if(!($id = _exec("INSERT INTO 
        pesquisa (client_id, created_by, titulo, createdAt, ativo) 
        VALUES   ($client_id, $user_id, '$titulo', now(), 0)
    ", true))) _error(500, 'server error');

    if(count($profile_inputs) > 0){

        $insert = "INSERT INTO pesquisa_profile_fields (pesquisa_id, field, type) VALUES ";
        foreach ($profile_inputs as $prof) 
            $insert .= "($id, '".$prof['field']."', '".$prof['type']."'),";
        $insert = substr($insert, 0,-1);

        if(!(_exec($insert))) _error();
    }

    return _response(['pesquisa_id' => $id]);

}

/**
 * @function:criar_cadastro_pesquisa
 * @pool:pesquisas_full
 */
function criar_cadastro_pesquisa(int $pesquisa_id, array $inputs){
    // tabela user_resposta_cad_field
    /*
        EXEMPLO: 
        $inputs = [ 'Nome', 'E-mail'];
        // transformar em slug e salvar
    */

    _is_pesquisa_cliente($pesquisa_id);

    $insert = "INSERT INTO user_resposta_cad_field (pesquisa_id, label, slug) VALUES ";

    foreach ($inputs as $label) {
        $slug    = _slug($label, "_");
        $insert .= "($pesquisa_id, '$label', '$slug'),";
    }

    $insert = substr($insert, 0 , -1);
    if(!_exec($insert)) _error(500, 'server error');

}


/**
 * @function:criar_pergunta
 * @pool:pesquisas_full
 */
function criar_pergunta(int $pesquisa_id, $pergunta, $type, array $options, bool $required = true){

    _is_pesquisa_cliente($pesquisa_id);
    // types 'radio', 'check', 'order'
    if(!in_array($type, ['radio', 'check', 'order'])) _error();

    $req = $required ? 1 : 0;

    if(!($pergunta_id = _exec("INSERT INTO pergunta (pesquisa_id, valor, `type`, `required`)
        VALUES ($pesquisa_id, '$pergunta', '$type', $req)", true))) 
        _error(500, 'server error');

    _inserir_options($pergunta_id, $options);

}


function _inserir_options(int $pergunta_id, array $options){

    $insert = "INSERT INTO options (pergunta_id, valor) VALUES ";
    
    foreach ($options as $option) 
        $insert .= "($pergunta_id, '$option'),";
    $insert = substr($insert, 0, -1);

    if(!_exec($insert)) _error(500, 'server error');

}

function _is_pesquisa_cliente(int $pesquisa_id){
    
    $user      = _user();
    $client_id = $user->getClientArray()['client_id'];
    
    if(_query(
        "SELECT pesquisa.id 
           FROM pesquisa
           JOIN client ON client.id = pesquisa.client_id
          WHERE client.id = $client_id AND pesquisa.id = $pesquisa_id
    ")->rowCount() == 0) _error(401, "Não autorizado");
    
}

function _is_user_resposta_pesquisa(int $user_resposta_id, int $pesquisa_id){

    $user      = _user();
    $client_id = $user->getClientArray()['client_id'];

    if(_query(
        "SELECT pesquisa.id 
           FROM pesquisa
           JOIN client        ON client.id = pesquisa.client_id
           JOIN user_resposta ON user_resposta.pesquisa_id = pesquisa.id
          WHERE 
                client.id        = $client_id  
            AND pesquisa.id      = $pesquisa_id     
            AND user_resposta.id = $user_resposta_id
    ")->rowCount() == 0) _error(401, "Não autorizado");

}
