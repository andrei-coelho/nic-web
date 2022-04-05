<?php 

use libs\app\User as user;

function _is_public(){
    return true;    
}

function _is_authentic(){
    return in_array(_user()->level(), ['admin', 'app', 'client']);
}

function test(string $nome, int $inteiro, float $dinheiro, $test, bool $status){
    return _response([
        "nome"=>$nome,
        "inteiro" => $inteiro,
        "dinheiro" => $dinheiro,
        "test" => $test,
        "status" => $status
    ]);
}