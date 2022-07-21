<?php 

use libs\app\Config as config;

function _is_installed(){
    return config::get("installed");
}

function _is_in_production(){
    return config::get("type") == 'prod';
}

function _url(){
    return _is_in_production() ? config::get("prod_url") : config::get("dev_url");
}

function _salt(){
    return config::get("salt");
}

function _refresh_token(){
    return !_is_in_production() ? config::get("refresh_tk") : "";
}

function _fmc_key(){
    return config::get("fmc_key");
}