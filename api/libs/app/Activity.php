<?php 

namespace libs\app;

class Activity {

    private $vars;
    private static $instance;
    private static $template;

    public function __construct(array $vars){
        $this->vars = $vars;
    }

    public static function register(array $vars){
        if(!self::$instance)
        self::$instance = new Activity($vars);
    }

    public static function genDescription($template){
        
        if(self::$template) return self::$template;
        if(!self::$instance) return $template;

        self::$template = $template;
        foreach (self::$instance->vars as $slug => $value)
            self::$template = str_replace("$".$slug, '"'.$value.'"', self::$template);
        
        return self::$template;
    }

    public static function isRegister(){
        return self::$instance !== null;
    }

}