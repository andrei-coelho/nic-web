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

    public function upload($file){
        
        $hash = md5(uniqid(rand(), true).$this->raiz);

        @list($mime_slug, $file) = explode(';', $file);
        @list(, $data) = explode(',', $file);

        $this->data = base64_decode($data);
        
        $pathClient = '../files_to_upload/'.$this->raiz.'/';
        if(!file_exists($pathClient)) {
            mkdir($pathClient, 0777, true);
        }
        
        $slugFile = $hash.'.'.$this->mime;
        $pathFile = $pathClient.$slugFile;

        if(($statusUP = file_put_contents($pathFile, $this->data)) && $this->create_thumb){
            
            $pathThumb = '../thumbs/'.$this->raiz.'/';
            if(!file_exists($pathThumb)) {
                mkdir($pathThumb, 0777, true);
            }

            $pathThumb .= $slugFile;

            try {
                EditImage::from($pathFile)
                ->path($pathThumb)
                ->resize('200x*')
                ->save();
            }catch(\Exception $e){
                echo $e->getMessage();
            }
            
        }

        $this->hash = $statusUP ? $hash : false;
        return $this;

    }

    public function hash(){
        return $this->hash;
    }


    public static function create_ghost_file($hash_dir, $client_id){
        
        $dirIdSel = 
            _query(
            "SELECT id 
                FROM directory 
                WHERE hash_dir = '$hash_dir' AND client_id = $client_id
            ");
        
        if($dirIdSel->rowCount() == 0) throw new \Exception("Diretório não existe", 2);
        
        $dirId     = $dirIdSel->fetchAssoc()['id'];
        $hash_file = md5(uniqid(rand(), true).$hash_dir);

        return 
            _exec(
                "INSERT INTO 
                    file_client (hash_file, directory_id) 
                    VALUES      ('$hash_file', $dirId);"
                ) 
            ? $hash_file
            : false;

    }


    public function update(){
        // recupera o nome do arquivo salvo, apaga, e sava este novo
    }



}
