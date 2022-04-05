<?php 

namespace libs\app;

class Config {

    private static $instance = false;
    private $data, $url;

    private function __construct(){
        $this->data = Spyc::YAMLLoad('../conf.yaml');
        $this->url  = $this->data[$this->data['type'].'_url'];
        $this->data['database'] = Spyc::YAMLLoad('../database.'.$this->data['type'].'.yaml')['database'];
    }

    private static function instance(){

        if(!self::$instance)
            self::$instance = new Config();

        return self::$instance;
    }

    public static function get(string $key = ""){

        $conf = self::instance()->data;
        
        if($key != "") 
            return (isset($conf[$key]) 
                        ? $conf[$key] 
                        : false);
                        
        return $conf;
    }

    public static function url(){

        return self::instance()->url;
    }

    public static function is_in_production(){

        return self::instance()->data['production'];
    }


}