<?php 

use libs\app\Activity as activity; 

function _activity(array $vars = []){
    activity::register($vars);
}

function _get_description($template){
    
    if(!activity::isRegister()) 
        return false;
    
    return activity::genDescription($template);
   
}