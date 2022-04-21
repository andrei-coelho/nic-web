<?php 
/**
 * @service:test
 */


 /**
  * @function:test
  * @pool:public
  */
function test(){

  sleep(1);

  $data = file_get_contents('php://input');

  list($mime_slug, $data) = explode(';', $data);
  list(, $data) = explode(',', $data);
  $data = base64_decode($data);

  file_put_contents('../files_to_upload/corel.cdr', $data);

  return _response([], 'Arquivo foi salvo com sucesso!');

}
