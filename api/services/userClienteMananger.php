<?php 

function _is_public(){
    return true;   
}

function _is_authentic(){
    return in_array(_user()->level(), ['admin', 'client']);
}