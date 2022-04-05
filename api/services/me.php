<?php 

function _is_public(){
    return false;    
}

function _is_authentic(){
    return in_array(_user()->level(), ['admin','client']);
}

function data(){
    return _response(_user()->to_array());
}