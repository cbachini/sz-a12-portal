<?php
/**
 * This file contains class for customer registration and login with miniOrange.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Main
 */

namespace MO_LDAP_CLOUD\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MO_LDAP_CLOUD\Utils\Mo_LDAP_Cloud_Utils;

if ( ! class_exists( 'MO_LDAP_Cloud_Customer_Setup_Handler' ) ) {

	/**
	 * MO_LDAP_Cloud_Customer_Setup_Handler : class to handle customer related actions.
	 */
	class MO_LDAP_Cloud_Customer_Setup_Handler {
		/**
		 * Utility object.
		 *
		 * @var [object]
		 */
		private $utils;

		/**
		 * Variable email
		 *
		 * @var mixed
		 */
		public $email;

		/**
		 * Variable phone
		 *
		 * @var mixed
		 */
		public $phone;

		/**
		 * Variable transaction_id
		 *
		 * @var mixed
		 */
		public $transaction_id;

		/**
		 * Variable default_customer_key
		 *
		 * @var string
		 */
		private $default_customer_key = '16555';

		/**
		 * Variable default_api_key
		 *
		 * @var string
		 */
		private $default_api_key = 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';

		/**
		 * Variable support_email
		 *
		 * @var string
		 */
		private $support_email = 'ldapsupport@xecurify.com';

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->utils = new Mo_LDAP_Cloud_Utils();
		}

		/**
		 * Function create_customer to register new user
		 *
		 * @param string $password : password.
		 * @return string
		 */
		public function create_customer( $password ) {
			$url = MO_LDAP_CLOUD_HOST_NAME . '/moas/rest/customer/add';

			$this->email  = ! empty( get_option( 'mo_ldap_admin_email' ) ) ? get_option( 'mo_ldap_admin_email' ) : '';
			$this->phone  = ! empty( get_option( 'mo_ldap_admin_phone' ) ) ? get_option( 'mo_ldap_admin_phone' ) : '';
			$fname        = get_option( 'mo_ldap_admin_fname' );
			$lname        = get_option( 'mo_ldap_admin_lname' );
			$company_name = get_option( 'mo_ldap_admin_company' );

			$fields = array(
				'companyName'    => $company_name,
				'areaOfInterest' => 'WP LDAP for Cloud Plugin',
				'firstname'      => $fname,
				'lastname'       => $lname,
				'email'          => $this->email,
				'phone'          => $this->phone,
				'password'       => $password,
			);

			$field_string = wp_json_encode( $fields );

			$args     = $this->utils::get_api_argument( $field_string );
			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => '-1',
						'message'    => 'Error establishing connection.',
					)
				);
			} elseif ( 200 !== $response['response']['code'] ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => null,
						'message'    => $response['body'],
					)
				);
			}

			return $response['body'];
		}

		/**
		 * Function get_customer_key
		 *
		 * @param string $password : password.
		 * @return string
		 */
		public function get_customer_key( $password ) {
			$url         = MO_LDAP_CLOUD_HOST_NAME . '/moas/rest/customer/key';
			$this->email = ! empty( get_option( 'mo_ldap_admin_email' ) ) ? get_option( 'mo_ldap_admin_email' ) : '';

			$fields = array(
				'email'    => $this->email,
				'password' => $password,
			);

			$field_string = wp_json_encode( $fields );
			$args         = $this->utils::get_api_argument( $field_string );
			$response     = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => '-1',
						'message'    => 'Error establishing connection.',
					)
				);
			} elseif ( 200 !== $response['response']['code'] ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => null,
						'message'    => $response['body'],
					)
				);
			}

			return $response['body'];
		}

		/**
		 * Function submit_contact_us
		 *
		 * @param  mixed $q_email : user email.
		 * @param  mixed $q_phone : user phone number.
		 * @param  mixed $query : query.
		 * @return string
		 */
		public function submit_contact_us( $q_email, $q_phone, $query ) {
			$url = MO_LDAP_CLOUD_HOST_NAME . '/moas/rest/customer/contact-us';

			$fname        = empty( get_option( 'mo_ldap_admin_fname' ) ) ? '' : get_option( 'mo_ldap_admin_fname' );
			$lname        = empty( get_option( 'mo_ldap_admin_lname' ) ) ? '' : get_option( 'mo_ldap_admin_lname' );
			$company_name = empty( get_option( 'mo_ldap_admin_company' ) ) ? '' : get_option( 'mo_ldap_admin_company' );

			$query  = '[miniOrange LDAP Cloud Plugin]: ' . $query;
			$fields = array(
				'firstName' => $fname,
				'lastName'  => $lname,
				'company'   => $company_name,
				'email'     => $q_email,
				'ccEmail'   => $this->support_email,
				'phone'     => $q_phone,
				'query'     => $query,
			);

			$field_string = wp_json_encode( $fields );
			$args         = $this->utils::get_api_argument( $field_string );

			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => '-1',
						'message'    => 'There was an error in sending query.',
					)
				);
			}

			return $response['body'];
		}

		/**
		 * Function send_otp_token
		 *
		 * @return string
		 */
		public function send_otp_token() {
			$url = MO_LDAP_CLOUD_HOST_NAME . '/moas/api/auth/challenge';

			$username = get_option( 'mo_ldap_admin_email' );

			$fields       = array(
				'customerKey' => $this->default_customer_key,
				'email'       => $username,
				'authType'    => 'EMAIL',
			);
			$field_string = wp_json_encode( $fields );
			$args         = $this->utils::get_api_argument( $field_string );
			$response     = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => '-1',
						'message'    => 'Error establishing connection.',
					)
				);
			} elseif ( 200 !== $response['response']['code'] ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => null,
						'message'    => $response['body'],
					)
				);
			}

			return $response['body'];
		}

		/**
		 * Function validate_otp_token
		 *
		 * @param  mixed $transaction_id : transaction id.
		 * @param  mixed $otp_token : OTP token.
		 * @return string
		 */
		public function validate_otp_token( $transaction_id, $otp_token ) {
			$url = MO_LDAP_CLOUD_HOST_NAME . '/moas/api/auth/validate';

			$fields = '';
			$fields = array(
				'txId'  => $transaction_id,
				'token' => $otp_token,
			);

			$field_string = wp_json_encode( $fields );
			$args         = $this->utils::get_api_argument( $field_string );
			$response     = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => '-1',
						'message'    => 'Error establishing connection.',
					)
				);
			} elseif ( 200 !== $response['response']['code'] ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => null,
						'message'    => $response['body'],
					)
				);
			}

			return $response['body'];
		}

		/**
		 * Function check_customer
		 *
		 * @return string
		 */
		public function check_customer() {
			$url          = MO_LDAP_CLOUD_HOST_NAME . '/moas/rest/customer/check-if-exists';
			$this->email  = ! empty( get_option( 'mo_ldap_admin_email' ) ) ? get_option( 'mo_ldap_admin_email' ) : '';
			$fields       = array(
				'email' => $this->email,
			);
			$field_string = wp_json_encode( $fields );
			$args         = $this->utils::get_api_argument( $field_string );
			$response     = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => '-1',
						'message'    => 'Error establishing connection.',
					)
				);
			} elseif ( 200 !== $response['response']['code'] ) {
				return wp_json_encode(
					array(
						'status'     => 'ERROR',
						'ERROR_CODE' => null,
						'message'    => $response['body'],
					)
				);
			}

			return $response['body'];
		}

		/**
		 * Function send_email_alert
		 *
		 * @param  mixed $email : user email.
		 * @param  mixed $phone : user phone.
		 * @param  mixed $subject : email subject.
		 * @param  mixed $message : query.
		 * @return string
		 */
		public function send_email_alert( $email, $phone, $subject, $message ) {

			$url = MO_LDAP_CLOUD_HOST_NAME . '/moas/api/notify/send';

			$customer_key = $this->default_customer_key;
			$api_key      = $this->default_api_key;

			$current_time_in_milli_sec = self::get_timestamp();
			$string_to_hash            = $customer_key . $current_time_in_milli_sec . $api_key;
			$hash_value                = hash( 'sha512', $string_to_hash );
			$from_email                = $email;

			global $user;
			$user = wp_get_current_user();

			$subject     = $subject . ' ' . $email;
			$query       = $message;
			$server_name = ! empty( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
			$company     = ! empty( get_option( 'mo_ldap_admin_company' ) ) ? get_option( 'mo_ldap_admin_company' ) : $server_name;

			$content = '<div >First Name: ' . $user->user_firstname . '<br><br>Last  Name: ' . $user->user_lastname . '   <br><br>Company: <a href="' . $company . '" target="_blank" >' . $company . '</a><br><br>Phone Number: ' . $phone . '<br><br>Email: <a href="mailto:' . $from_email . '" target="_blank">' . $from_email . '</a><br><br>' . $query . '</div>';

			$fields = array(
				'customerKey' => $customer_key,
				'sendEmail'   => true,
				'email'       => array(
					'customerKey' => $customer_key,
					'fromEmail'   => $email,
					'bccEmail'    => $this->support_email,
					'fromName'    => 'miniOrange',
					'toEmail'     => $this->support_email,
					'toName'      => $this->support_email,
					'subject'     => $subject,
					'content'     => $content,
				),
			);

			$field_string = wp_json_encode( $fields );

			$headers = array(
				'Content-Type'  => 'application/json',
				'Customer-Key'  => $customer_key,
				'Timestamp'     => $current_time_in_milli_sec,
				'Authorization' => $hash_value,
			);

			$args = array(
				'method'      => 'POST',
				'body'        => $field_string,
				'timeout'     => '10000',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => $headers,
			);

			$response = wp_remote_post( $url, $args );
			if ( is_wp_error( $response ) ) {
				return wp_json_encode(
					array(
						'status'  => 'ERROR',
						'message' => 'Error Connecting to miniOrange. Please contact at info@xecurify.com or ldapsupport@xecurify.com for more details',
					)
				);
			}
			return $response['body'];
		}

		/**
		 * Function get_timestamp
		 *
		 * @return string
		 */
		public function get_timestamp() {
			$url = MO_LDAP_CLOUD_HOST_NAME . '/moas/rest/mobile/get-timestamp';

			$response = wp_remote_post( $url );
			if ( is_wp_error( $response ) ) {
				$current_time_in_milli_sec = round( microtime( true ) * 1000 );
				$current_time_in_milli_sec = number_format( $current_time_in_milli_sec, 0, '', '' );
				return $current_time_in_milli_sec;
			} else {
				return $response['body'];
			}
		}
	}
}
