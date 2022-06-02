<?php 

namespace auto_save;

class FileService {

    private $functions = [];
    private $path, $slug;

    function __construct(string $path, string $slug){
        $this->path = $path;
        $this->slug = $slug;
    }

    public function setFunction(string $slug, array $pool, $template){
        
        $this->functions[] = [
            "slug" => $slug,
            "pool" => $pool,
            "template" => $template
        ];
    }

    public function show(){
        print_r($this->functions);
    }

    public function getDataService(){
        return [
            "slug" => $this->slug, 
            "path" => substr($this->path, 0, -4)
        ];
    }

   public function commit(){
        
        $poolsValue = [];

        foreach ($this->functions as $fun) 
            foreach ($fun['pool'] as $pool) 
                $poolsValue[$pool] = 0;

        $slugs = "";
        foreach ($poolsValue as $slug => $id) 
            $slugs .= " slug = '" .$slug. "' OR ";
        $slugs = substr( $slugs, 0, -4).";";

        if($slugs == ";") return;

        $perm_q = _query("SELECT slug, id FROM permission_pool WHERE $slugs");
        $ids_pr = $perm_q->fetchAllAssoc();
        
        foreach ($ids_pr as $id_pr) $poolsValue[$id_pr['slug']] = $id_pr['id'];

        $idService  = _exec("INSERT INTO service (slug, route, ativo) VALUES ('".$this->slug."', '".$this->path."', 1)", true);
        
        $slugsFun = [];

        foreach ($this->functions as $fun) {
            
            $slugFun = $fun['slug'];
            $funcId  = _exec("INSERT INTO service_function (slug, service_id) VALUES('$slugFun', $idService)", true);
            
            foreach ($fun['pool'] as $pool_slug) {
                $idPool = $poolsValue[$pool_slug];
                _exec("INSERT INTO permission_func (permission_pool_id, service_function_id ) VALUES ($idPool, $funcId)");
            }

            if($template = $fun['template'])
                _exec("INSERT INTO 
                    act_serv_func_reg(service_function_id, template)
                    VALUES ($funcId, '$template')
                ");

            $slugsFun[] = [
                'name' => $slugFun,
                'pools' => implode(',',$fun['pool']),
                'template' => $template
            ];

        }

        echo "Service: ".$this->slug. " commited <br> \n";
        echo "- Functions: <br> \n <pre>";
        print_r($slugsFun);
        echo "\n </pre>";
   }

}