<?php 
/**
 * @service: example_here
 */

 /**
 *  @public: false
 *  @pool: permission_pool.slug, permission_pool.slug
 */
function list_user($pass, $email) { }

 /**
 *  @id: 1  # -->  o id será gerado pelo programa e inserido no arquivo automaticamente
 *  @public: false
 *  @pool: permission_pool.slug
 */
function list_user_admin($pass, $email) { }

/**
 *  @public: true
 */
function login($pass, $email) { }

function _check_user(){
    #    ^  
    #    |______ use '_' para funções privadas
    #
    #   esta função não será registrada
}