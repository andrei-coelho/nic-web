<?php 

/**
 * @service: pesquisas
 */


function _get_options_field($field){
    switch ($field) {
        case 'genero':
            return ['masculino', 'feminino', 'transgênero', 'neutro', 'não-binário', 'cisgênero'];
        case 'sexo':
            return ['M', 'F'];
        case 'cor':
            return ['branca', 'preta', 'parda', 'indígena', 'amarela'];
        case 'escolaridade':
            return ['sem instrução', 'fundamental', 'medio', 'tecnico', 'superior'];
    }
}

function _prefix_table($slug){
    switch ($slug) {
        case 'data'  : return 'user_resposta.data_resposta';
        default: 
            return 
            in_array($slug, ['idade', 'casado', 'sexo', 'filhos', 'genero', 'cor', 'salario']) ?
            "user_resposta_profile.$slug" : 
            "";
    }
}

function _fix_value($value){
    if(is_string($value)) return "'$value'";
    if(is_bool($value)) return $value ? 1 : 0;
    return $value;
}

function _group_values($key, $values){
    if($key == 'idade'){
        $res = [];
        foreach ($values as $value) {
            $step = 5;
            $next = 18;
            while ($value['label'] > $next) $next += $step; 
            $res[$next] = isset($res[$next]) ? $res[$next] + $value['total'] : $value['total'];
        }
        $resp = [];
        foreach ($res as $key => $val) {
            $resp[] = [
                'label' => $key,
                'total' => $val
            ];
        }
        return $resp;
    }
    return $values;
}


function _gen_filter(array $filters, array $not = []){

    /*
      
      se existir 'equals' ele será usado. 
      Se não existir ou 'equals' for 'false', 
      o algoritmo irá procura pelo 'range'.
      Se ambos não existirem, retorna erro.
      
      Exemplo:
       {
          field:'name',
          equals:'value',
          range:{               
              min:'value',
              max:'value'
          }
       }
    */

    $filterStr = "";

    foreach ($filters as $filter) {
        
        if(in_array($filter['field'], $not)) continue;

        $prefix = _prefix_table($filter['field']);

        if(isset($filter['equals'])){
            $filterStr .= " AND ".$prefix." = ". _fix_value($filter['equals']);
            continue;
        }

        if(!isset($filter['range']['min']) || !isset($filter['range']['max'])) {
            continue;
        }

        if($filter['field'] != "data"){
            $max = (int)$filter['range']['max'];
            $min = (int)$filter['range']['min'];
            $max = $min > $max ? false : $max; 
        } else {
            $max = (string)$filter['range']['max'];
            $min = (string)$filter['range']['min'];
            $max = $max != "" ? 
                date('Y-m-d', 
                    DateTime::createFromFormat('d/m/Y', $max)->getTimestamp()
                )." 23:59:59" : false;
            $min = $min != "" ?
                date('Y-m-d', 
                    DateTime::createFromFormat('d/m/Y', $min)->getTimestamp()
                )." 00:00:00" : false;
        }
        
        if($min) $filterStr .= " AND ".$prefix." >= ". _fix_value($min);
        if($max) $filterStr .= " AND ".$prefix." <= ". _fix_value($max);
        
    }

    return $filterStr;
}

/**
 * @function: estatistica_perfil
 * @pool: pesquisas_full
 */
function estatistica_perfil(int $pesquisa_id, array $fields, array $filters, array $range = []){

    _is_pesquisa_cliente($pesquisa_id);

    $filterStr = _gen_filter($filters);

    try {
        
        $response = [];
        foreach ($fields as $field) {
            $query = _query(
                "SELECT 
                    user_r.$field as label,
                    count(user_r.id) as total
                    FROM (
                        SELECT user_resposta_profile.id, user_resposta_profile.$field
                          FROM user_resposta_profile
                          JOIN user_resposta ON user_resposta.id = user_resposta_profile.user_resposta_id
                         WHERE user_resposta.pesquisa_id = $pesquisa_id 
                           AND user_resposta.response = 1
                         $filterStr
                    ) as user_r
                GROUP BY user_r.$field;");
            $response[$field] = _group_values($field, $query->fetchAllAssoc());
        }
        return _response($response);

    } catch (\Exception $e){
        _error();
    }

}


/**
 * @function: editar_titulo
 * @pool: pesquisas_full
 */
function editar_titulo(int $pesquisa_id, $titulo){

    _is_pesquisa_cliente($pesquisa_id);

    if(!_exec("UPDATE pesquisa SET titulo = '$titulo' WHERE id = $pesquisa_id")) 
        _error();

    return _response([], 'Título editado com sucesso!');
}


/**
 * @function: excluir_pesquisa
 * @pool: pesquisas_full
 */
function excluir_pesquisa(int $pesquisa_id){
    
    _is_pesquisa_cliente($pesquisa_id);

    if(!_exec("UPDATE pesquisa SET ativo = 0 WHERE id = $pesquisa_id")) 
        _error();
}


/**
 * @function: publicar_pesquisa
 * @pool: pesquisas_full
 */
function publicar_pesquisa(int $pesquisa_id){
    
    _is_pesquisa_cliente($pesquisa_id);

    if(!_exec("UPDATE pesquisa SET ativo = 1 WHERE id = $pesquisa_id")) 
        _error();
}


/**
 * @function: finalizar_pesquisa
 * @pool: pesquisas_full
 */
function finalizar_pesquisa(int $pesquisa_id){
    
    _is_pesquisa_cliente($pesquisa_id);

    if(!_exec("UPDATE pesquisa SET ativo = 2 WHERE id = $pesquisa_id")) 
        _error();
}

/**
 * @function: estatistica_votos
 * @pool: pesquisas_full
 */
function estatistica_votos(int $pesquisa_id, array $filters = []){

    _is_pesquisa_cliente($pesquisa_id);

    $filterStr = _gen_filter($filters);

    $query = 
        "SELECT 
            (SELECT 
                count(user_resposta.id) as total_resposta 
                FROM user_resposta 
                LEFT JOIN user_resposta_profile ON user_resposta_profile.user_resposta_id = user_resposta.id
                WHERE user_resposta.pesquisa_id = $pesquisa_id
                AND user_resposta.response = 1
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
            AND user_resposta.response = 1
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

    $pergs = [];

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
 * @pool:pesquisas_basico,pesquisas_full
 */
function list_pesquisas(bool $todas = true){

    $user      = _user();
    $client_id = $user->getClientArray()['client_id'];

    $editor    = in_array('pesquisas_full', array_column($user->getPermissions(), 'slug'));

    $queryStr  = "SELECT
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
    pesquisa.client_id = $client_id ";

    $queryStr  .= "AND ".($todas && $editor ?  "pesquisa.ativo > 0" : "pesquisa.ativo = 1");
        
    $query = _query($queryStr);
    if(!$query) _error();
    
    return _response([
        "is_editor"       => $editor,
        "lista_pesquisas" => $query->fetchAllAssoc()
    ]);
    
}


/**
 * @function:get_new_user_resposta
 * @pool:pesquisas_basico
 */
function get_new_user_resposta(int $pesquisa_id){
    // cria um user_resposta
    _is_pesquisa_cliente($pesquisa_id);

    if(!($user_resposta_id = _exec(
        "INSERT INTO user_resposta (pesquisa_id, data_resposta) 
         VALUES ($pesquisa_id, now())", true)))
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

    return _response([], "Resposta do questionário foi armazenada com sucesso!");

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

    $profileFields = $query->fetchAllAssoc();
    foreach ($profileFields as $key => $profile)
        if($profile['type'] == "string")
            $profileFields[$key]['options'] = _get_options_field($profile['field']);
   
    return _response($profileFields);

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
                options.valor  as `option`,
                options.id     as option_id,
                pergunta.valor as pergunta,
                pergunta.id    as pergunta_id,
                pergunta.type  as `type`
            FROM options
            JOIN pergunta ON options.pergunta_id = pergunta.id
            JOIN pesquisa ON pesquisa.id = pergunta.pesquisa_id 
        WHERE
            pesquisa.id = 9
        ORDER BY 
            pergunta.id, options.id
        ASC
    ");
    if(!$query1) _error(500, 'erro ao tentar recuperar as perguntas');
   
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


    return _response(array_values($perguntas));

}


/**
 * @function:criar_pesquisa
 * @pool:pesquisas_full
 */
function criar_pesquisa($titulo, array $perguntas, array $profile_inputs = []){

    $user      = _user();
    $client_id = $user->getClientArray()['client_id'];
    $user_id   = $user->id;

    if(!($id = _exec("INSERT INTO 
        pesquisa (client_id, created_by, titulo, createdAt, ativo) 
        VALUES   ($client_id, $user_id, '$titulo', now(), 0)
    ", true))) _error(500, 'server error');

    foreach ($perguntas as $pergunta) {
        criar_pergunta($id, $pergunta['title'], $pergunta['type'], $pergunta['options']);
    }

    if(count($profile_inputs) > 0){

        $insert = "INSERT INTO pesquisa_profile_fields (pesquisa_id, field, type) VALUES ";
        foreach ($profile_inputs as $prof) 
            $insert .= "($id, '".$prof['field']."', '".$prof['type']."'),";
        $insert = substr($insert, 0,-1);

        if(!(_exec($insert))) _error();
    }

    if(!_exec("UPDATE pesquisa SET ativo = 1 WHERE id = $id")) 
        _error();

    return _response(['pesquisa_id' => $id]);

}

/**
 * @function:criar_pergunta
 * @pool:pesquisas_full
 */
function criar_pergunta(int $pesquisa_id, $pergunta, $type, array $options, bool $required = true){

    // _is_pesquisa_cliente($pesquisa_id);
    // types 'radio', 'check', 'order'
    if(!in_array($type, ['radio', 'check'])) _error();

    $req = $required ? 1 : 0;

    if(!($pergunta_id = _exec("INSERT INTO pergunta (pesquisa_id, valor, `type`, `required`)
        VALUES ($pesquisa_id, '$pergunta', '$type', $req)", true))) 
        _error(500, 'server error');

    _inserir_options($pergunta_id, array_column($options, 'label'));

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
