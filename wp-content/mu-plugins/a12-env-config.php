<?php
/**
 * Must-Use Plugin: A12 Environment Config
 *
 * Carregado automaticamente pelo WordPress antes de qualquer plugin.
 * Responsável por configurações globais do ambiente.
 *
 * @package A12
 */

// Previne acesso direto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define o ambiente atual com base na variável de ambiente do container
define( 'A12_ENV', getenv( 'A12_ENV' ) ?: 'local' );

// Em container (qualquer ambiente não-local), bloqueia modificações de arquivos
// pelo painel admin. Core, plugins e temas só mudam via rebuild da imagem Docker.
if ( A12_ENV !== 'local' ) {
    define( 'DISALLOW_FILE_MODS', true );
    define( 'AUTOMATIC_UPDATER_DISABLED', true );
    define( 'WP_AUTO_UPDATE_CORE', false );

    // Padrão de idioma do portal: pt_BR.
    // Mantém o idioma explícito, evitando voltar para en_US após rebuild/redeploy.
    if ( ! defined( 'WPLANG' ) ) {
        define( 'WPLANG', 'pt_BR' );
    }
}
// Em ambiente local, ativa saída de erros PHP no log do WordPress
if ( A12_ENV === 'local' ) {
    ini_set( 'display_errors', '0' );
    ini_set( 'log_errors', '1' );
}

// Local Upload Proxy: redireciona uploads ausentes para o site de produção
// Evita 404 de mídia em ambiente local sem cópia completa dos uploads
if ( A12_ENV === 'local' ) {
    add_filter( 'wp_get_attachment_url', function( $url ) {
        $upload_dir = wp_upload_dir();
        $base_url   = $upload_dir['baseurl'];
        $base_dir   = $upload_dir['basedir'];

        // Normaliza esquema para comparação (http/https podem divergir no admin)
        $url_normalized      = preg_replace( '#^https?://#', '//', $url );
        $base_url_normalized = preg_replace( '#^https?://#', '//', $base_url );

        // Se a URL não pertence ao uploads local, devolve sem modificar
        if ( strpos( $url_normalized, $base_url_normalized ) === false ) {
            return $url;
        }

        $relative   = str_replace( $base_url_normalized, '', $url_normalized );
        $local_path = $base_dir . $relative;

        // Se o arquivo não existe localmente, aponta para produção
        if ( ! file_exists( $local_path ) ) {
            return 'https://www.a12.com/wp-content/uploads' . $relative;
        }

        return $url;
    } );
}
