<?php
/**
 * This file stores the configuration functions used all over the plugin.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Handlers
 */

namespace MO_LDAP_CLOUD\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MO_LDAP_CLOUD\Utils\Mo_LDAP_Cloud_Utils;

if ( ! class_exists( 'Mo_LDAP_Cloud_Configuration_Handler' ) ) {
	/**
	 * Mo_ldap_Cloud_Configuration_Handler : Class for the all the plugin configuration functions.
	 */
	class Mo_LDAP_Cloud_Configuration_Handler {
		/**
		 * Utility object.
		 *
		 * @var [object]
		 */
		private $utils;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->utils = new Mo_LDAP_Cloud_Utils();
		}

		/**
		 * Function ldap_login
		 *
		 * @param  string $username : Contains LDAP username value.
		 * @param  string $password : Contains LDAP pass.
		 * @return mixed Returns Authentication Status.
		 */
		public function ldap_login( $username, $password ) {
			$username = stripcslashes( $username );
			$password = stripcslashes( $password );

			if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				return 'OPENSSL_ERROR';
			}

			$url = MO_LDAP_CLOUD_HOST_NAME . '/moas/api/ldap/authenticate';

			$encrypted_username = $this->utils::encrypt( $username );
			$encrypted_password = $this->utils::encrypt( $password );

			$data        = $this->get_login_config( $encrypted_username, $username, $encrypted_password, 'User Login through LDAP' );
			$data_string = wp_json_encode( $data );

			$args     = $this->utils::get_api_argument( $data_string );
			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
				return array(
					'statusCode'    => 'MO_ERROR',
					'statusMessage' => 'Error Connecting to miniOrange.',
				);
			} elseif ( 200 === $response['response']['code'] ) {
				return (array) json_decode( $response['body'] );
			} else {
				return array(
					'statusCode'    => 'FAILED',
					'statusMessage' => 'Unexpected error occurred.',
				);
			}
		}

		/**
		 * Function save_ldap_config : Save LDAP Configuration.
		 *
		 * @return string Returns JSON String.
		 */
		public function save_ldap_config() {
			$url = MO_LDAP_CLOUD_HOST_NAME . '/moas/api/ldap/update-config';

			$fields       = $this->get_encrypted_config( 'Save LDAP Configuration' );
			$field_string = wp_json_encode( $fields );

			$args = $this->utils::get_api_argument( $field_string );

			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return wp_json_encode(
					array(
						'statusCode'    => 'ERROR',
						'statusMessage' => 'Error Connecting to miniOrange.',
					)
				);
			} elseif ( 200 === $response['response']['code'] ) {
				return $response['body'];
			} else {
				return '"statusCode":"ERROR","statusMessage":"Error Connecting to miniOrange."';
			}
		}

		/**
		 * Function test_connection to test LDAP Connection
		 *
		 * @return string Returns JSON String
		 */
		public function test_connection() {
			$url          = MO_LDAP_CLOUD_HOST_NAME . '/moas/api/ldap/test';
			$request_type = 'Test User Connection';

			$fields       = $this->get_encrypted_config( $request_type );
			
			$field_string = wp_json_encode( $fields );

			$args = $this->utils::get_api_argument( $field_string );

			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return wp_json_encode(
					array(
						'statusCode'    => 'ERROR',
						'statusMessage' => 'Error Connecting to miniOrange.',
					)
				);
			} elseif ( 200 === $response['response']['code'] ) {
				return $response['body'];
			} else {
				return wp_json_encode(
					array(
						'statusCode'    => 'ERROR',
						'statusMessage' => $response['body'] . '. Please check your configuration. Also check troubleshooting under LDAP configuration.',
					)
				);
			}
		}

		/**
		 * Function test_authentication to test LDAP user login
		 *
		 * @param  mixed $username : Contain LDAP user username.
		 * @param  mixed $password : Contain LDAP user pwd.
		 * @return string Returns JSON String with status and staus msg.
		 */
		public function test_authentication( $username, $password ) {
			$username = stripcslashes( $username );
			$password = stripcslashes( $password );

			if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				return wp_json_encode(
					array(
						'statusCode'    => 'OPENSSL_ERROR',
						'statusMessage' => '<a href="https://php.net/manual/en/mcrypt.installation.php">PHP mcrypt extension</a> is not installed or disabled.',
					)
				);
			}

			$url          = '';
			$request_type = '';

			$url                = MO_LDAP_CLOUD_HOST_NAME . '/moas/api/ldap/authenticate';
			$request_type       = 'Test User Login';
			$encrypted_username = $this->utils::encrypt( $username );
			$encrypted_password = $this->utils::encrypt( $password );

			$fields       = $this->get_login_config( $encrypted_username, $username, $encrypted_password, $request_type );
			$field_string = wp_json_encode( $fields );

			$args     = $this->utils::get_api_argument( $field_string );
			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return wp_json_encode(
					array(
						'statusCode'    => 'WP_ERROR',
						'statusMessage' => 'Error Connecting to miniOrange.',
					)
				);
			} elseif ( 200 === $response['response']['code'] ) {
				return $response['body'];
			} else {
				return wp_json_encode(
					array(
						'statusCode'    => 'ERROR',
						'statusMessage' => $response['body'] . '. Please check your configuration. Also check troubleshooting under LDAP configuration.',
					)
				);
			}
		}

		/**
		 * Function get_encrypted_config
		 *
		 * @param  mixed $request_type : Contains type of Operation.
		 * @return array Returns array with LDAP store data.
		 */
		public function get_encrypted_config( $request_type ) {

			$dn_attribute        = '';
			$server_name         = ! empty( get_option( 'mo_ldap_server_url' ) ) ? get_option( 'mo_ldap_server_url' ) : '';
			$dn                  = ! empty( get_option( 'mo_ldap_server_dn' ) ) ? get_option( 'mo_ldap_server_dn' ) : '';
			$admin_ldap_password = ! empty( get_option( 'mo_ldap_server_password' ) ) ? get_option( 'mo_ldap_server_password' ) : '';
			$search_base         = ! empty( get_option( 'mo_ldap_search_base' ) ) ? get_option( 'mo_ldap_search_base' ) : '';
			$search_filter       = ! empty( get_option( 'mo_ldap_search_filter' ) ) ? get_option( 'mo_ldap_search_filter' ) : '';
			$customer_id         = ! empty( get_option( 'mo_ldap_admin_customer_key' ) ) ? get_option( 'mo_ldap_admin_customer_key' ) : null;
			$application_name    = ! empty( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';

			$current_user = wp_get_current_user();
			$username     = ! empty( get_option( 'mo_ldap_admin_email' ) ) ? get_option( 'mo_ldap_admin_email' ) : $current_user->user_email;

			$fields = array(
				'customerId'           => $customer_id,
				'ldapAuditRequest'     => array(
					'endUserEmail'    => $username,
					'applicationName' => $application_name,
					'appType'         => 'WP LDAP for Cloud',
					'requestType'     => $request_type,
				),
				'gatewayConfiguration' => array(
					'ldapServer'          => $server_name,
					'bindAccountDN'       => $dn,
					'bindAccountPassword' => $admin_ldap_password,
					'searchBase'          => $search_base,
					'dnAttribute'         => $dn_attribute,
					'ldapSearchFilter'    => $search_filter,
				),
				'isDefault'            => true,
				'activated'            => true,
			);

			return $fields;
		}

		/**
		 * Function get_login_config : Returns user's LDAP login configuration
		 *
		 * @param  mixed $encrypted_username : encrypted username.
		 * @param  mixed $username : enduser username.
		 * @param  mixed $encrypted_password : user's encrypted LDAP pwd.
		 * @param  mixed $request_type : LDAP operation request type.
		 * @return array Returns user LDAP configuration array.
		 */
		public function get_login_config( $encrypted_username, $username, $encrypted_password, $request_type ) {

			$customer_id = get_option( 'mo_ldap_admin_customer_key' ) ? get_option( 'mo_ldap_admin_customer_key' ) : null;

			$profile_attributes = '';
			if ( get_option( 'mo_ldap_email_attribute' ) ) {
				$profile_attributes = $profile_attributes . get_option( 'mo_ldap_email_attribute' ) . ',';
			}
			if ( get_option( 'mo_ldap_phone_attribute' ) ) {
				$profile_attributes = $profile_attributes . get_option( 'mo_ldap_phone_attribute' ) . ',';
			}
			if ( get_option( 'mo_ldap_fname_attribute' ) ) {
				$profile_attributes = $profile_attributes . get_option( 'mo_ldap_fname_attribute' ) . ',';
			}
			if ( get_option( 'mo_ldap_lname_attribute' ) ) {
				$profile_attributes = $profile_attributes . get_option( 'mo_ldap_lname_attribute' ) . ',';
			}
			if ( get_option( 'mo_ldap_nickname_attribute' ) ) {
				$profile_attributes = $profile_attributes . get_option( 'mo_ldap_nickname_attribute' ) . ',';
			}

			$wp_options = wp_load_alloptions();
			foreach ( $wp_options as $option => $value ) {
				if ( strpos( $option, 'mo_ldap_custom_attribute' ) !== false ) {
					$profile_attributes = $profile_attributes . $value . ',';
				}
			}

			$application_name = ! empty( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';

			$fields = array(
				'customerId'        => $customer_id,
				'userName'          => $encrypted_username,
				'password'          => $encrypted_password,
				'ldapAuditRequest'  => array(
					'endUserEmail'    => $username,
					'applicationName' => $application_name,
					'appType'         => 'WP LDAP for Cloud',
					'requestType'     => $request_type,
				),
				'profileAttributes' => $profile_attributes,
			);

			return $fields;
		}
	}
}
