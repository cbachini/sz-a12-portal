<?php
/**
 * Plugin Name: A12 – Bloquear Indexação (NoIndex)
 * Description: Desativa indexação por robôs de busca quando WP_NOINDEX=true.
 *              Controle via variável de ambiente — sem alterações no banco.
 * Author:      A12
 *
 * Para ativar:  WP_NOINDEX=true  (docker-compose, ECS task definition, etc.)
 * Para reverter: remova ou defina WP_NOINDEX=false
 */

if ( getenv( 'WP_NOINDEX' ) !== 'true' ) {
    return;
}

// 1. Força blog_public = 0 em memória (WordPress adiciona <meta noindex> e robots.txt Disallow:/)
//    Não grava no banco — reversível sem tocar no DB.
add_filter( 'pre_option_blog_public', '__return_zero' );

// 2. Reforça o header HTTP X-Robots-Tag para casos onde o meta tag não é suficiente
add_action( 'send_headers', static function () {
    if ( ! headers_sent() ) {
        header( 'X-Robots-Tag: noindex, nofollow', true );
    }
} );
