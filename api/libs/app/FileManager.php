<?php 

namespace libs\app;

use libs\app\images\EditImage as EditImage;

class FileManager {

    /**
     * @description
     * o $hash é o identificador único do arquivo 
     * no banco de dados e no diretório físico
     */
    private $data, $mime, $hash, $name, $raiz;
    private bool $create_thumb;

    function __construct($raiz, $name, $mime){
        $this->create_thumb = in_array($mime, ['jpg', 'jpeg', 'png']);
        $this->raiz = $raiz;
        $this->name = $name;
        $this->mime = $mime;
    }

    public function hash(){
        return $this->hash;
    }

    public function upload($file, $set_thumb = true){
        
        $hash = md5(uniqid(rand(), true).$this->raiz);

        if($file !== ""){
            @list($mime_slug, $file) = explode(';', $file);
            @list(, $data) = explode(',', $file);
            $this->data = base64_decode($data);
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

    public static function commit($hashId, $mime, $client_path){
        
        if(_exec("UPDATE file_client SET ghost = 0 WHERE hash_file = '$hashId'")){
            
            $slugFile = $hashId.".".$mime;
            
            $pathFile = '../files_to_upload/'.$client_path.'/'.$slugFile;
            $objthis  = new FileManager($client_path, $hashId, $mime);

            return $objthis->create_thumb 
            ? $objthis->create_thumbnail($slugFile, $pathFile)
            : true; 
            
        }

        return false;
    }



}
