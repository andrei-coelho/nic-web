<?php 

namespace auto_save;

use auto_save\FileService as File;

class ServiceFactory {

    public static $files;
    private static $regex = '/@(\w+):\s?([^\s]+)/';

    private function __construct(){}

    public static function commit(){
        // deleta todos os registros
        
        $status1 = _exec("DELETE FROM permission_func");
        $status2 = _exec("DELETE FROM service_function");
        $status3 = _exec("DELETE FROM service");

        $routesPath = "../api/routes.php";
        $routesSplited = explode('#', file_get_contents($routesPath));
        $content = "#!\n";
        
        foreach (self::$files as $file) {
            $data = $file->getDataService();
            $content .= "Route::register('".$data['slug']."', '".$data['path']."');\n";
            $file->commit();
        }
        
        file_put_contents($routesPath, $routesSplited[0].$content);

    }

    public static function genServiceByToken(string $path, string $tokenService, array $tokensFunc){
    
        $fileO = self::readTkService($path, $tokenService);
        if(!$fileO) return false;
        self::readTkFunc($fileO, $tokensFunc);
        self::$files[] = $fileO;
    }

    private static function readTkService($path, $tokenService){
        
        $path = substr($path, 16, strlen($path));
        preg_match_all(self::$regex, $tokenService, $res);
        
        foreach ($res[1] as $k => $chave) {
            if($chave == "service"){
                $slugP = substr($path, 0, strlen($path) - 4);
                $slugE = explode('/', $slugP);
                $slug  = "";
                for ($i=0; $i < count($slugE) -1; $i++) { 
                    $slug .= $slugE[$i]."_";
                }
                $slug = substr($slug, 0, -1)."@".$res[2][$k];
                return new File($path, $slug);
            }
        }

        return false;

    }

    private static function readTkFunc($obj, $tokensFunc){

        foreach ($tokensFunc as $token) {
            
            preg_match_all(self::$regex, $token, $res);

            $slug = false;
            $pool = [];

            foreach ($res[1] as $k => $chave) {
                if($chave == "function"){
                    if(!$slug) $slug = $res[2][$k];
                    continue;
                }
                if($chave == "pool"){
                    $pool = explode(',', $res[2][$k]);
                    break;
                }
            }

            $obj->setFunction($slug, $pool);
        }

    }

}