<?php 

/**
 * @service: pesquisas
 */


/**
 * @function:salvar_dados_cadastro
 * @pool: pesquisas_basico
 */
function iniciar_respostas(){
    // cria um user_resposta
}

/**
 * @function:salvar_resposta
 * @pool: pesquisas_basico
 */
function salvar_resposta(int $user_resposta_id, array $options){

}

/**
 * @function:salvar_dados_cadastro
 * @pool: pesquisas_basico
 */
function salvar_dados_cadastro(array $inputs){
    // tabela user_resposta_cad_value
    /*
        EXEMPLO: 
        $inputs = [
            'id'    => 1,
            'value' => 'value'
        ];

    */
}

/**
 * @function:get_inputs_fields
 * @pool: pesquisas_basico
 */
function get_inputs_fields(int $pesquisa_id){
    // tabela user_resposta_cad_field
    /*
        EXEMPLO: 
        $inputs = [
            'id'    => 1,
            'label' => 'E-mail',
            'slug'  => 'email'
        ];

    */
}


/**
 * @function:criar_pesquisa
 * @pool:pesquisas_full
 */
function criar_pesquisa($titulo){

    $user      = _user();
    $client_id = $user->getClientArray()['client_id'];
    $user_id   = $user->id;

    if(!($id = _exec("INSERT INTO 
        pesquisa (client_id, created_by, titulo, createdAt, ativo) 
        VALUES   ($client_id, $user_id, '$titulo', now(), 0)
    ", true))) _error(500, 'server error');

    return _response(['pesquisa_id' => $id]);

}

/**
 * @function: criar_cadastro_pesquisa
 * @pool:pesquisas_full
 */
function criar_cadastro_pesquisa(int $pesquisa_id, array $inputs){
    // tabela user_resposta_cad_field
    /*
        EXEMPLO: 
        $inputs = [ 'Nome', 'E-mail'];
        // transformar em slug e salvar
    */

    if(!_is_pesquisa_cliente($pesquisa_id)) _error(401, "Não autorizado");

    $insert = "INSERT INTO user_resposta_cad_field (pesquisa_id, label, slug) VALUES ";

    foreach ($inputs as $label) {
        $slug    = _slug($label, "_");
        $insert .= "($pesquisa_id, '$label', '$slug'),";
    }

    $insert = substr($insert, 0 , -1);
    if(!_exec($insert)) _error(500, 'server error');

}


/**
 * @function: criar_pergunta
 * @pool:pesquisas_full
 */
function criar_pergunta(int $pesquisa_id, $pergunta, $type, array $options, bool $required = true){

    if(!_is_pesquisa_cliente($pesquisa_id)) _error(401, "Não autorizado");
    // types 'radio', 'check', 'order'
    if(!in_array($type, ['radio', 'check', 'order'])) _error();

    $req = $required ? 1 : 0;

    if(!($pergunta_id = _exec("INSERT INTO pergunta (pesquisa_id, valor, type, required)
        VALUES ($pesquisa_id, '$pergunta', '$type', $req)", true))) 
        _error(500, 'server error');

    

}


function _inserir_options(int $pergunta_id, array $options){
    
}


function _is_pesquisa_cliente(int $pesquisa_id):bool{
    
    $user      = _user();
    $client_id = $user->getClientArray()['client_id'];

    return _query(
        "SELECT pesquisa.id 
           FROM pesquisa
           JOIN client ON client.id = pesquisa.client_id
          WHERE client.id = $client_id AND pesquisa.id = $pesquisa_id
    ")->rowCount() > 0;
}
