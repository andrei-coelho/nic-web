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
function other_test(){
  EditImage::from('../files_to_upload/tiftest.tif')
  ->path('../public/img/tiftest.jpg')
  ->resize('150x*')
  ->save();
}


/**
* @function:test
* @pool:public
*/
function test($name, $mime, $file = "", $hash_dir = "", $client_id = false){
  
  $user = _user();
  
  if($user->is_client())
      $client_id = (int) $user->getClientArray()['client_id'];
  try {
    (new file($client_id, $name, $mime))->save($file, $hash_dir);
  } catch(Exception $e){
    return _error(400, $e->getMessage());
  }

}
