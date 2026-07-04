<?php
/**
 * REST endpoint para enriquecer termos e gravar metadados Yoast SEO por termo.
 *
 * Endpoints:
 *   POST   /wp-json/a12/v1/term-enrich
 *          Body: {
 *            taxonomy      : string,   // e.g. "channel", "post_tag", "category"
 *            term_id       : int,
 *            description   : string,   // HTML com uso, sinônimos, notas
 *            yoast_focuskw : string,   // frase-chave de foco
 *            yoast_desc    : string,   // meta descrição (~155 chars)
 *            yoast_title   : string    // título SEO (opcional)
 *          }
 *
 *   GET    /wp-json/a12/v1/term-enrich?taxonomy=channel&term_id=10
 *          Retorna os metadados Yoast gravados para o termo.
 *
 * Yoast armazena metadados de termos em wp_options['wpseo_taxonomy_meta']:
 *   [ taxonomy => [ term_id => [ wpseo_focuskw, wpseo_title, wpseo_desc, ... ] ] ]
 */

defined( 'ABSPATH' ) || exit;

class A12_TermSeoEndpoint {

	public static function init(): void {
		add_action( 'rest_api_init', [ self::class, 'register_routes' ] );
	}

	public static function register_routes(): void {
		register_rest_route( 'a12/v1', '/term-enrich', [
			[
				'methods'             => 'POST',
				'callback'            => [ self::class, 'handle_enrich' ],
				'permission_callback' => [ self::class, 'check_permission' ],
				'args'                => [
					'taxonomy'      => [ 'required' => true,  'type' => 'string' ],
					'term_id'       => [ 'required' => true,  'type' => 'integer' ],
					'description'   => [ 'required' => false, 'type' => 'string', 'default' => '' ],
					'yoast_focuskw' => [ 'required' => false, 'type' => 'string', 'default' => '' ],
					'yoast_desc'    => [ 'required' => false, 'type' => 'string', 'default' => '' ],
					'yoast_title'   => [ 'required' => false, 'type' => 'string', 'default' => '' ],
				],
			],
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'handle_get' ],
				'permission_callback' => [ self::class, 'check_permission' ],
				'args'                => [
					'taxonomy' => [ 'required' => true,  'type' => 'string' ],
					'term_id'  => [ 'required' => true,  'type' => 'integer' ],
				],
			],
		] );
	}

	public static function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	// ------------------------------------------------------------------

	public static function handle_enrich( WP_REST_Request $request ): WP_REST_Response {
		$taxonomy    = sanitize_key( $request->get_param( 'taxonomy' ) );
		$term_id     = (int) $request->get_param( 'term_id' );
		$description = wp_kses_post( $request->get_param( 'description' ) );
		$focuskw     = sanitize_text_field( $request->get_param( 'yoast_focuskw' ) );
		$yoast_desc  = sanitize_text_field( $request->get_param( 'yoast_desc' ) );
		$yoast_title = sanitize_text_field( $request->get_param( 'yoast_title' ) );

		// Valida taxonomy e term
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return new WP_REST_Response( [ 'error' => "Taxonomia não encontrada: {$taxonomy}" ], 400 );
		}
		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) || ! $term ) {
			return new WP_REST_Response( [ 'error' => "Termo {$term_id} não encontrado em {$taxonomy}" ], 404 );
		}

		$updated = [];

		// ── 1. Descrição do termo ────────────────────────────────────────────
		if ( $description !== '' ) {
			$result = wp_update_term( $term_id, $taxonomy, [ 'description' => $description ] );
			if ( is_wp_error( $result ) ) {
				return new WP_REST_Response( [ 'error' => $result->get_error_message() ], 500 );
			}
			$updated[] = 'description';
		}

		// ── 2. Yoast SEO por termo ───────────────────────────────────────────
		if ( $focuskw !== '' || $yoast_desc !== '' || $yoast_title !== '' ) {
			$yoast_meta = get_option( 'wpseo_taxonomy_meta', [] );

			if ( ! isset( $yoast_meta[ $taxonomy ] ) ) {
				$yoast_meta[ $taxonomy ] = [];
			}
			if ( ! isset( $yoast_meta[ $taxonomy ][ $term_id ] ) ) {
				$yoast_meta[ $taxonomy ][ $term_id ] = [];
			}

			if ( $focuskw !== '' ) {
				$yoast_meta[ $taxonomy ][ $term_id ]['wpseo_focuskw'] = $focuskw;
				$updated[] = 'yoast_focuskw';
			}
			if ( $yoast_desc !== '' ) {
				$yoast_meta[ $taxonomy ][ $term_id ]['wpseo_desc'] = $yoast_desc;
				$updated[] = 'yoast_desc';
			}
			if ( $yoast_title !== '' ) {
				$yoast_meta[ $taxonomy ][ $term_id ]['wpseo_title'] = $yoast_title;
				$updated[] = 'yoast_title';
			}

			update_option( 'wpseo_taxonomy_meta', $yoast_meta );
		}

		return new WP_REST_Response( [
			'ok'       => true,
			'term_id'  => $term_id,
			'taxonomy' => $taxonomy,
			'slug'     => $term->slug,
			'updated'  => $updated,
		], 200 );
	}

	// ------------------------------------------------------------------

	public static function handle_get( WP_REST_Request $request ): WP_REST_Response {
		$taxonomy = sanitize_key( $request->get_param( 'taxonomy' ) );
		$term_id  = (int) $request->get_param( 'term_id' );

		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) || ! $term ) {
			return new WP_REST_Response( [ 'error' => 'Termo não encontrado' ], 404 );
		}

		$yoast_meta = get_option( 'wpseo_taxonomy_meta', [] );
		$term_yoast = $yoast_meta[ $taxonomy ][ $term_id ] ?? [];

		return new WP_REST_Response( [
			'term_id'      => $term_id,
			'taxonomy'     => $taxonomy,
			'slug'         => $term->slug,
			'name'         => $term->name,
			'description'  => $term->description,
			'yoast_focuskw'=> $term_yoast['wpseo_focuskw'] ?? '',
			'yoast_desc'   => $term_yoast['wpseo_desc'] ?? '',
			'yoast_title'  => $term_yoast['wpseo_title'] ?? '',
		], 200 );
	}
}
