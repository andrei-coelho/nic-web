<?php 

namespace libs\app;

class FileManager {

    /**
     * @description
     * o $hash é o identificador único do arquivo 
     * no banco de dados e no diretório físico
     */
    private $data, $mime, $hash, $name;
    private $thumb;
    private $raiz;
    private bool $create_thumb;

    function __construct($client_id, $name, $mime){

        $pathsSel = _query(
            "SELECT 
                directory.hash_dir as path
                FROM directory 
                JOIN client_path ON directory.id = client_path.directory_id 
                WHERE 
                    client_path.client_id = $client_id
            ");
        
        
        if(!$pathsSel || $pathsSel->rowCount() == 0)
             throw new \Exception("O cliente não tem um diretório raiz cadastrado", 1);
        $path = $pathsSel->fetchAssoc();
        
        $this->create_thumb = in_array($mime, ['jpg', 'jpeg', 'png']);

        $icon = $mime.'.jpg';
        $this->thumb = file_exists('img/icons/'.$icon) ? $icon : 'file.default.jpg';

        $this->name = $name;
        $this->mime = $mime;
        $this->raiz = $path['path'];
        
    }


    private function upload($file, $hash_dir = "", $hashFile = ""){
        
        if($hash_dir == "") $hash_dir = $this->raiz;

        $this->hash = $hash_file != "" ? $hash_file : md5(uniqid(rand(), true).$hash_dir);

        @list($mime_slug, $file) = explode(';', $file);
        @list(, $data) = explode(',', $file);

        $this->data = base64_decode($data);
        
        $pathClient = '../files_to_upload/'.$this->raiz.'/';
        if(!file_exists($pathClient)) {
            mkdir($pathClient, 0777, true);
        }
        
        $slugFile = $this->hash.'.'.$this->mime;
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
                ->resize('150x*')
                ->save();
            }catch(\Exception $e){
                return false;
            }
            

        }

        return $statusUP ? $this->hash : false;

    }

    public function save($file, $hash_dir){
        
        // pega o id do diretorio virtual verificando se é um diretorio do cliente
        // tenta fazer o upload (se nao conseguir retorna erro)
        // salva o arquivo com o id do diretorio
        echo $this->upload($file, $hash_dir);
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


    


    public function download(){
        
    }


    public function to_array(){
        
    }


}
