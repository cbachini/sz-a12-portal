<?php
/**
 * Plugin Name: A12 Preserve Modified Date
 * Description: Quando o header HTTP X-Preserve-Modified: 1 está presente, preserva a data
 *              post_modified original ao fazer updates via REST API (usado durante a migração).
 */

add_filter( 'wp_insert_post_data', function ( array $data, array $postarr ) : array {
    $header = $_SERVER['HTTP_X_PRESERVE_MODIFIED'] ?? '';
    if ( $header !== '1' ) {
        return $data;
    }

    $post_id = (int) ( $postarr['ID'] ?? 0 );
    if ( $post_id <= 0 ) {
        return $data;
    }

    $existing = get_post( $post_id );
    if ( ! $existing ) {
        return $data;
    }

    $data['post_modified']     = $existing->post_modified;
    $data['post_modified_gmt'] = $existing->post_modified_gmt;

    return $data;
}, 999, 2 );
