<?php 

use libs\app\User as user;

function _user(){
    return user::get_user();
}