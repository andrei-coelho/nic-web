<?php 
/**
 * @service:test
 */
use libs\app\FileManager as file;
use libs\app\images\EditImage as EditImage;


function create($nome, $mime_type){
    
}


/**
* @function:other_test
* @pool:public
*/
function _test(){
  EditImage::from('../files_to_upload/power_girl.png')
  ->path('../public/img/testing.png')
  ->resize('150x*')
  ->save();
}


/**
* @function:test
* @pool:public
*/
function test($name, $mime, $file, $hash_dir = "", $client_id = false){

  $user = _user();
  
  if($user->is_client()){
    
    $client_arr = $user->getClientArray();
    $client_id  = $client_arr['client_id'];

    if($hash_dir == "") $hash_dir = $client_arr['client_path'];

    $dirSel = _query(
      "SELECT id FROM directory 
      WHERE hash_dir = '$hash_dir' 
      AND client_id = $client_id");

    if($dirSel->rowCount()==0) _error(401, "Não autorizado");
    $dirId = $dirSel->fetchAssoc()['id'];
      
  }

  sleep(mt_rand(3, 8));
      
  try {
    
    $hashFile = (new file($hash_dir, $name, $mime))->upload($file)->hash();
    if(!$hashFile) _error(400, "Ocorreu um erro ao tentar salvar o arquivo");

    $insert = _exec(
      "INSERT INTO 
       file_client(nome, hash_file, mime_type, directory_id)
       VALUES ('$name', '$hashFile', '$mime', $dirId)");
    
    if(!$insert) _error(400, "Ocorreu um erro ao tentar salvar o arquivo");
    return _response(['hash_file' => $hashFile]);

  } catch(Exception $e){
    return _error(400, $e->getMessage());
  }

}

/**
* @function:notification_test
* @pool:public
*/
function notification_test(){

  $user = _user();

  _notify("arquivo_criado", [
    "@user_sender" => $user->slug(),
    "@nome_arquivo" => "arquivo_nome"."."."jpg"
  ]);

}