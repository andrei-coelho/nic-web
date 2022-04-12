<?php 
/**
 * @service: service_example
 */

 /**
  * @function: list_user
 *  @pool: manage_accounts
 */
function list_user($pass, $email) { }

 /**
 *  @function: list_user_admin
 *  @pool: manage_accounts
 */
function list_user_admin($pass, $email) { }

/**
 *  @function: login
 *  @pool: public
 */
function login($pass, $email) { }

function _check_user(){
    #    ^  
    #    |______ use '_' para funções privadas
    #
    #   esta função não será registrada e
    #   não pode ser usada por nenhum usuario
}