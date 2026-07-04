<?php
/**
 * Plugin Name:  A12 Editorial
 * Description:  CPTs, taxonomias, field groups e regras editoriais do Portal A12.
 *               Toda a estrutura de dados é controlada pelo ACF.
 * Version:      2.0.0
 * Author:       Soyuz Digital Studio
 */

defined( 'ABSPATH' ) || exit;

// Elementor 3.35.x chama is_plugin_active() fora do admin context (bug).
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// ── ACF: path para persistir field groups como JSON no repositório ─────────────
$acf_json_dir = __DIR__ . '/a12-editorial/acf-json';
add_filter( 'acf/settings/save_json', function() use ( $acf_json_dir ) {
	return $acf_json_dir;
} );
add_filter( 'acf/settings/load_json', function( $paths ) use ( $acf_json_dir ) {
	$paths[] = $acf_json_dir;
	return $paths;
} );

// ── Carregamento de classes ──────────────────────────────────────────────────────
require_once __DIR__ . '/a12-editorial/class-term-seeder.php';
require_once __DIR__ . '/a12-editorial/class-upload-date-endpoint.php';
require_once __DIR__ . '/a12-editorial/class-term-seo-endpoint.php';

A12_TermSeeder::init();
A12_UploadDateEndpoint::init();
A12_TermSeoEndpoint::init();

// Ambiente local (HTTP): habilita Application Passwords sem exigir SSL.
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	add_filter( 'wp_is_application_passwords_available', '__return_true' );
}

// Patch isolado: rewrite slug do post_tag nativo (tag → tags).
add_action( 'init', function() {
	global $wp_taxonomies;
	if ( isset( $wp_taxonomies['post_tag'] ) ) {
		$wp_taxonomies['post_tag']->rewrite = [
			'slug'         => 'tags',
			'with_front'   => false,
			'hierarchical' => false,
			'ep_mask'      => EP_TAGS,
		];
	}
}, 999 );

// Permalink de post com núcleo editorial: /%channel%/%category%/%postname%/
add_action( 'init', function() {
	add_rewrite_tag( '%channel%', '([^/]+)', 'channel=' );
}, 20 );

add_filter( 'post_link', function( string $permalink, WP_Post $post ): string {
	if ( false === strpos( $permalink, '%channel%' ) ) {
		return $permalink;
	}

	$terms = get_the_terms( $post, 'channel' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return str_replace( '%channel%', 'sem-nucleo', $permalink );
	}

	$term = reset( $terms );
	if ( ! $term || empty( $term->slug ) ) {
		return str_replace( '%channel%', 'sem-nucleo', $permalink );
	}

	return str_replace( '%channel%', $term->slug, $permalink );
}, 10, 2 );

// MU-plugin não tem activation hook; aplicamos flush apenas uma vez.
add_action( 'init', function() {
	$version_key = 'a12_editorial_rewrite_v3';
	if ( get_option( $version_key ) ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( $version_key, 1, false );
}, 1000 );
