<?php
/**
 * Plugin Name: A12 Ensure pt-BR
 * Description: Garante pt-BR no core e também nas traduções de plugins/temas.
 * Version: 1.2.0
 */

define( 'A12_PTBR_LOCALE', 'pt_BR' );
define( 'A12_PTBR_TRANSIENT', 'a12_ptbr_ok' );

/**
 * Instala/atualiza traducoes pt-BR para core, plugins e temas.
 */
function a12_ensure_ptbr( bool $force = false ): void {
    // Verifica a cada 6h para auto-recuperar rapido apos restart de container.
    if ( ! $force && get_transient( A12_PTBR_TRANSIENT ) ) {
        return;
    }

    // Garante WPLANG correto no banco e auto-update de traducoes ligado.
    if ( get_option( 'WPLANG' ) !== A12_PTBR_LOCALE ) {
        update_option( 'WPLANG', A12_PTBR_LOCALE );
    }
    if ( get_option( 'auto_update_translation' ) !== '1' ) {
        update_option( 'auto_update_translation', '1' );
    }

    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/translation-install.php';
    require_once ABSPATH . 'wp-admin/includes/update.php';

    // 1) Core pt_BR.
    $lang_file = WP_CONTENT_DIR . '/languages/' . A12_PTBR_LOCALE . '.mo';
    $needs_install = ! file_exists( $lang_file );

    if ( $needs_install ) {
        wp_download_language_pack( A12_PTBR_LOCALE );
    }

    // 2) Traduções de plugins e temas.
    // Atualiza metadados e instala pacotes de tradução pendentes.
    wp_update_plugins();
    wp_update_themes();

    $translations = wp_get_translation_updates();
    if ( ! empty( $translations ) ) {
        $skin     = new Automatic_Upgrader_Skin();
        $upgrader = new Language_Pack_Upgrader( $skin );
        $upgrader->bulk_upgrade( $translations );
    }

    // Recarrega textdomains com locale correto.
    if ( function_exists( 'switch_to_locale' ) ) {
        switch_to_locale( A12_PTBR_LOCALE );
    }

    // Marca como verificado por 6h.
    set_transient( A12_PTBR_TRANSIENT, true, 6 * HOUR_IN_SECONDS );
}

// Checagem periodica normal.
add_action( 'init', function () {
    a12_ensure_ptbr( false );
}, 1 );

// Reage imediatamente a plugin/tema novo para evitar painel em ingles.
add_action( 'activated_plugin', function () {
    delete_transient( A12_PTBR_TRANSIENT );
    a12_ensure_ptbr( true );
}, 20 );

add_action( 'switch_theme', function () {
    delete_transient( A12_PTBR_TRANSIENT );
    a12_ensure_ptbr( true );
}, 20 );

// Reage a updates de plugin/tema/core.
add_action( 'upgrader_process_complete', function () {
    delete_transient( A12_PTBR_TRANSIENT );
    a12_ensure_ptbr( true );
}, 20 );
