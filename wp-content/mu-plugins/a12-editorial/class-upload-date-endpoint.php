<?php
/**
 * Endpoint REST para controle de pasta de upload durante a migração.
 *
 * Permite ao script upload_wp_media.py forçar a subpasta YYYY/MM
 * correspondente à data de publicação original do post legado,
 * em vez da data corrente do servidor (comportamento padrão do WP).
 *
 * Fluxo de uso pelo upload_wp_media.py:
 *   1. POST   /wp-json/a12/v1/set-upload-date  {"date": "2019-08-12"}
 *   2. POST   /wp-json/wp/v2/media             (upload da mídia — usa subpasta 2019/08)
 *   3. DELETE /wp-json/a12/v1/set-upload-date  (limpa transient)
 *
 * Autenticação: Application Password (WP) com capacidade 'upload_files'.
 * TTL do transient: 5 min (failsafe contra crash do script de migração).
 *
 * Referência: EDITORIAL_TAXONOMY.md — nota de pipeline em upload_wp_media.py.
 */

defined( 'ABSPATH' ) || exit;

class A12_UploadDateEndpoint {

	const TRANSIENT = 'a12_migration_upload_date';
	const NS        = 'a12/v1';
	const ROUTE     = '/set-upload-date';

	public static function init(): void {
		add_action( 'rest_api_init', [ self::class, 'register_routes' ] );
		add_filter( 'upload_dir', [ self::class, 'filter_upload_dir' ] );
	}

	// ------------------------------------------------------------------
	// Routes
	// ------------------------------------------------------------------

	public static function register_routes(): void {
		register_rest_route( self::NS, self::ROUTE, [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ self::class, 'handle_set' ],
				'permission_callback' => [ self::class, 'check_permission' ],
				'args'                => [
					'date' => [
						'required'          => true,
						'type'              => 'string',
						'description'       => 'Data no formato YYYY-MM-DD.',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => [ self::class, 'validate_date' ],
					],
				],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ self::class, 'handle_clear' ],
				'permission_callback' => [ self::class, 'check_permission' ],
			],
		] );
	}

	// ------------------------------------------------------------------
	// Callbacks
	// ------------------------------------------------------------------

	public static function check_permission(): bool {
		return current_user_can( 'upload_files' );
	}

	public static function validate_date( string $value ): bool {
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return false;
		}
		[ $y, $m, $d ] = array_map( 'intval', explode( '-', $value ) );
		return checkdate( $m, $d, $y );
	}

	public static function handle_set( WP_REST_Request $request ): WP_REST_Response {
		$date = $request->get_param( 'date' );
		set_transient( self::TRANSIENT, $date, 5 * MINUTE_IN_SECONDS );
		return new WP_REST_Response( [ 'status' => 'set', 'date' => $date ], 200 );
	}

	public static function handle_clear(): WP_REST_Response {
		delete_transient( self::TRANSIENT );
		return new WP_REST_Response( [ 'status' => 'cleared' ], 200 );
	}

	// ------------------------------------------------------------------
	// Filter
	// ------------------------------------------------------------------

	/**
	 * Reescreve subdir/path/url do upload para a data do transient.
	 * Não tem efeito quando não há transient ativo (uploads normais).
	 */
	public static function filter_upload_dir( array $uploads ): array {
		$date = get_transient( self::TRANSIENT );
		if ( ! $date ) {
			return $uploads;
		}

		$ts = strtotime( $date );
		if ( ! $ts ) {
			return $uploads;
		}

		$subdir = '/' . date( 'Y', $ts ) . '/' . date( 'm', $ts );

		$uploads['subdir'] = $subdir;
		$uploads['path']   = $uploads['basedir'] . $subdir;
		$uploads['url']    = $uploads['baseurl'] . $subdir;

		return $uploads;
	}
}
