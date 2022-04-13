<?php 

use libs\app\Config as config;

function _is_in_production(){
    return config::get("production");
}

function _url(){
    return _is_in_production() ? config::get("production_url") : config::get("development_url");
}

function _salt(){
    return config::get("salt");
}

function _token(){
    return !_is_in_production() ? config::get("tk_dropbox") : "";
}