<?php
/**
 * Plugin Name: A12 Link Rewrite
 * Description: Reescreve links internos legados (a12.com) para o domínio atual do WordPress.
 *              Funciona em qualquer ambiente (local, staging, produção) via home_url().
 * Version:     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reescreve hrefs que apontem para a12.com para o home_url() atual.
 */
function a12_rewrite_legacy_links( string $content ): string {
    $home = rtrim( home_url(), '/' );
    return str_replace(
        [ 'https://www.a12.com', 'https://a12.com', 'http://www.a12.com' ],
        $home,
        $content
    );
}

// Renderização PHP clássica (tema)
add_filter( 'the_content', 'a12_rewrite_legacy_links' );

// REST API — campo content.rendered
add_filter( 'rest_prepare_post', function ( $response, $post, $request ) {
    $data = $response->get_data();
    if ( ! empty( $data['content']['rendered'] ) ) {
        $data['content']['rendered'] = a12_rewrite_legacy_links( $data['content']['rendered'] );
        $response->set_data( $data );
    }
    return $response;
}, 10, 3 );
