<?php 

namespace libs\app;

use libs\app\images\EditImage as EditImage;

class FileManager {

    /**
     * @description
     * o $hash é o identificador único do arquivo 
     * no banco de dados e no diretório físico
     */
    private $data, $mime, $hash, $name, $raiz, $size;
    private bool $create_thumb;

    function __construct($client_id, $raiz, $name, $mime, $max_byte, $total){
        $this->create_thumb = in_array($mime, ['jpg', 'jpeg', 'png']);
        $this->client_id = $client_id;
        $this->max_byte  = $max_byte;
        $this->total     = $total;
        $this->raiz      = $raiz;
        $this->name      = $name;
        $this->mime      = $mime;
    }

    public function hash(){
        return $this->hash;
    }

    public function size(){
        return $this->size;
    }

    public function upload($file, $set_thumb = true){
        
        $hash = md5(uniqid(rand(), true).$this->raiz);

        if($file !== ""){
        
            @list($mime_slug, $file) = explode(';', $file);
            @list(, $data) = explode(',', $file);
            $this->data = base64_decode($data);
            $this->size = strlen($this->data);
            
            if($this->size + $this->total > $this->max_byte)
                throw new Exception("O tamanho do arquivo ultrapassa o limite disponível A", 1);
        
        } else {
            $this->data = false;
        }
        
        $pathClient = '../files_to_upload/'.$this->raiz.'/';
        if(!file_exists($pathClient)) 
            mkdir($pathClient, 0777, true);
        
        $slugFile = $hash.'.'.$this->mime;
        $pathFile = $pathClient.$slugFile;

        $statusUP = file_put_contents($pathFile, ($this->data ? $this->data : ''));

        if($this->create_thumb && $set_thumb)
            $this->create_thumbnail($slugFile, $pathFile);
        
        $this->hash = $statusUP !== false ? $hash : false;
       
        return $this;

    }

    public function create_ghost_file($dirId){
        
        $this->upload("", false);
        $nome = $this->name;
        $mime = $this->mime;

        if($this->hash){
            $hash_file = $this->hash;
            _exec(
                "INSERT 
                 INTO   file_client 
                        (nome, mime_type, hash_file, directory_id, ghost) 
                 VALUES ('$nome', '$mime', '$hash_file', $dirId, 1);"
            );

        }
        return $this;
    
    }

    private function create_thumbnail($slugFile, $pathFile){
        
        $pathThumb = '../thumbs/'.$this->raiz.'/';
        if(!file_exists($pathThumb))
            mkdir($pathThumb, 0777, true);

        $pathThumb .= $slugFile;

        try {
            EditImage::from($pathFile)
            ->path($pathThumb)
            ->resize('200x*')
            ->save();
            return true;
        }catch(\Exception $e){
            //echo $e->getMessage();
            return false;
        }
        
    }

    public static function append($client_path, $file, $data){
    
        $file = '../files_to_upload/'.$client_path.'/'.$file;
        $data = base64_decode($data);
        return file_exists($file) && file_put_contents($file, $data, FILE_APPEND);
    
    }

    
    public static function commit($client_id, $hashId, $mime, $client_path, $max_byte, $total){
        
        $slugFile = $hashId.".".$mime;
        
        $pathFile = '../files_to_upload/'.$client_path.'/'.$slugFile;
        $objthis  = new FileManager($client_id, $client_path, $hashId, $mime, $max_byte, $total);

        $objthis->size = strlen(file_get_contents($pathFile));

        if($objthis->size + $objthis->total > $objthis->max_byte){
            throw new \Exception("O tamanho do arquivo ultrapassa o limite disponível", 1);
        }
        
        if(!_exec("UPDATE file_client SET ghost = 0 WHERE hash_file = '$hashId'")){
            throw new \Exception("Ocorreu um erro ao tentar salvar o arquivo, tente novamente mais tarde", 1);
        }

        if($objthis->create_thumb)
            $objthis->create_thumbnail($slugFile, $pathFile);
        return $objthis;
     
    }



}
