<?php 

use libs\sqli\SQLi as sqli;
use libs\sqli\DataBase as db;

db::open_links((function(){
    _error(500, "Internal Server Error - Problem to connect database");
}));

function _query($query) {
    return sqli::query($query);
}

function _exec($sql, $lastInsert = false) {
    return sqli::exec($sql, $lastInsert);
}