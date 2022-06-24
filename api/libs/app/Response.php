<?php 

namespace libs\app;

class Response {

    private $data, $code, $message, $error = false;
    private $session = false;

    function __construct(array $data, int $code = 200, string $message = ""){
        
        if($code != 200) $this->error = true;

        $this->data    = $data;
        $this->code    = $code;
        $this->message = $message;
        
    }

    function getData(){
        return $this->data;
    }

    function setSession($session){
        $this->session = $session;
    }

    function response(){
        
        $resp = [
            "error"   => $this->error,
            "code"    => $this->code,
            "message" => $this->message,
            "data"    => $this->data
        ];

        if($this->session) $resp["session"] = $this->session;

        return \json_encode($resp,  
            JSON_PRESERVE_ZERO_FRACTION  | 
            JSON_PARTIAL_OUTPUT_ON_ERROR |
            JSON_UNESCAPED_UNICODE); 
    }

}