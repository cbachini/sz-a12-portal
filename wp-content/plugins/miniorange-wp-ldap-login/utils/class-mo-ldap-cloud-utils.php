<?php
/**
 * Generalized utility functions used by the plugin.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Utils
 */

namespace MO_LDAP_CLOUD\Utils;

use MO_LDAP_CLOUD\Handlers\Mo_LDAP_Cloud_Configuration_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mo_LDAP_Cloud_Utils' ) ) {
	/**
	 * Mo_LDAP_Cloud_Utils : Utility class.
	 */
	class Mo_LDAP_Cloud_Utils {
		/**
		 * Variable p_code with premium plan code name
		 *
		 * @var string
		 */
		public static $p_code = 'UHJlbWl1bSBQbGFuIC0gV1AgTERBUCBDbG91ZA==';

		/**
		 * Function is_customer_registered : Check if customer logged in.
		 *
		 * @return bool
		 */
		public static function is_customer_registered() {
			$email        = get_option( 'mo_ldap_admin_email' );
			$customer_key = get_option( 'mo_ldap_admin_customer_key' );

			if ( empty( $email ) || empty( $customer_key ) || ! is_numeric( trim( $customer_key ) ) ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Function success_message : Displays success message.
		 *
		 * @return void
		 */
		public function success_message() {
			$class       = 'error';
			$message     = get_option( 'mo_ldap_message' );
			$esc_allowed = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'b'      => array(),
				'h1'     => array(),
				'h2'     => array(),
				'h3'     => array(),
				'h4'     => array(),
				'h5'     => array(),
				'h6'     => array(),
				'i'      => array(),
			);
			echo "<div class='" . esc_attr( $class ) . "' style='display: block;'> <p>" . wp_kses( $message, $esc_allowed ) . '</p></div>';
		}

		/**
		 * Function error_message : Displays error message.
		 *
		 * @return void
		 */
		public function error_message() {
			$class       = 'updated';
			$message     = get_option( 'mo_ldap_message' );
			$esc_allowed = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'b'      => array(),
				'h1'     => array(),
				'h2'     => array(),
				'h3'     => array(),
				'h4'     => array(),
				'h5'     => array(),
				'h6'     => array(),
				'i'      => array(),
			);
			echo "<div class='" . esc_attr( $class ) . "'> <p>" . wp_kses( $message, $esc_allowed ) . '</p></div>';
		}

		/**
		 * Function show_error_message : handles the error messages to be displayed.
		 *
		 * @return void
		 */
		public function show_error_message() {
			remove_action( 'admin_notices', array( $this, 'error_message' ) );
			add_action( 'admin_notices', array( $this, 'success_message' ) );
		}
		/**
		 * Function mo_ldap_cloud_auth_report_update : Add log to user auth report.
		 *
		 * @param  mixed $username : Username of user who attempted login.
		 * @param  mixed $status : Status of Login.
		 * @param  mixed $ldap_error : LDAP error message.
		 * @return void
		 */
		public function mo_ldap_cloud_auth_report_update( $username, $status, $ldap_error ) {
			if ( strcmp( get_option( 'mo_ldap_cloud_local_user_report_log' ), '1' ) === 0 ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'cloud_user_report';
				$wpdb->insert( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Inserting data into a custom table.
					$table_name,
					array(
						'user_name'   => $username,
						'time'        => current_time( 'mysql' ),
						'ldap_status' => $status,
						'ldap_error'  => $ldap_error,

					)
				);
				wp_cache_delete( 'mo_ldap_cloud_user_report_cache' );
				wp_cache_delete( 'mo_ldap_cloud_user_report_count_cache' );
				wp_cache_delete( 'wp_user_reports_pagination_cache' );
			}
		}
		/**
		 * Function mo_ldap_is_user_logs_empty : Check if user auth logs exist or not.
		 *
		 * @return bool
		 */
		public static function mo_ldap_is_user_logs_empty() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'cloud_user_report';

			$mo_user_report_table_exist = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) === $table_name; //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Fetching data from a custom table.
			if ( $mo_user_report_table_exist ) {
				$wp_user_reports_count_cache = wp_cache_get( 'mo_ldap_cloud_user_report_count_cache' );
				if ( $wp_user_reports_count_cache ) {
					$user_count = $wp_user_reports_count_cache;
				} else {
					$table_name = $wpdb->prefix . 'cloud_user_report';
					$user_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s', $table_name ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Fetching data from a custom table. Table name is safe as it uses wpdb->prefix.
					wp_cache_set( 'mo_ldap_cloud_user_report_count_cache', $user_count );
				}
				if ( 0 === $user_count ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Function check_empty_or_null : check if variable empty/null
		 *
		 * @param  mixed $value : variable to be checked if null.
		 * @return bool
		 */
		public static function check_empty_or_null( $value ) {
			if ( ! isset( $value ) || empty( $value ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Function show_success_message : handles the success messages to be displayed.
		 *
		 * @return void
		 */
		public function show_success_message() {
			remove_action( 'admin_notices', array( $this, 'success_message' ) );
			add_action( 'admin_notices', array( $this, 'error_message' ) );
		}

		/**
		 * Function encrypt : Encrypt String
		 *
		 * @param  mixed $str : string to be encrypted.
		 * @return string
		 */
		public static function encrypt( $str ) {
			if ( ! self::is_extension_installed( 'openssl' ) ) {
				return '';
			}

			$key       = get_option( 'mo_ldap_customer_token' );
			$method    = 'AES-128-ECB';
			$str_crypt = openssl_encrypt( $str, $method, $key, OPENSSL_RAW_DATA || OPENSSL_ZERO_PADDING );
			return base64_encode( $str_crypt ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- function not being used to obfuscate the code
		}

		/**
		 * Function decrypt : Decrpt String
		 *
		 * @param  mixed $value : String to be decypted.
		 * @return string
		 */
		public static function decrypt( $value ) {
			if ( ! self::is_extension_installed( 'openssl' ) ) {
				return '';
			}

			$str_in  = base64_decode( $value ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- function not being used to obfuscate the code
			$key     = get_option( 'mo_ldap_customer_token' );
			$method  = 'AES-128-ECB';
			$iv_size = openssl_cipher_iv_length( $method );
			$data    = substr( $str_in, $iv_size );
			return openssl_decrypt( $data, $method, $key, OPENSSL_RAW_DATA || OPENSSL_ZERO_PADDING );
		}
		/**
		 * Securely unserialize data from WordPress options
		 * Prevents PHP Object Injection by restricting allowed classes
		 *
		 * @param string $option_value The serialized data to unserialize.
		 * @param array  $allowed_classes Array of allowed classes for unserialization (default: empty = no objects).
		 * @return array|false Returns array on success, empty array on failure
		 */
		public static function secure_unserialize_option( $option_value, $allowed_classes = array() ) {
			if ( empty( $option_value ) || ! is_string( $option_value ) ) {
				return array();
			}
			if ( ! preg_match( '/^[aOs]:[0-9]+:/', $option_value ) ) {
				return array();
			}
			if ( empty( $allowed_classes ) && preg_match( '/^O:[0-9]+:/', $option_value ) ) {
				return array();
			}
			try {
				$options      = array(
					'allowed_classes' => $allowed_classes,
				);
				$unserialized = maybe_unserialize( $option_value );
				if ( is_serialized( $option_value ) && false === $unserialized ) {
					error_log( 'SECURITY: Failed to unserialize option value safely.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
				if ( false !== $unserialized && is_array( $unserialized ) ) {
					return $unserialized;
				}
			} catch ( \Exception $e ) {
				error_log( 'SECURITY: Secure unserialize exception: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			return array();
		}

		/**
		 * Function is_extension_installed : Check if PHP extension installed
		 *
		 * @param  mixed $name : Name of PHP extension.
		 * @return bool
		 */
		public static function is_extension_installed( $name ) {
			$all_loaded_extensions = array_map( 'strtolower', get_loaded_extensions() );
			if ( in_array( strtolower( $name ), $all_loaded_extensions, true ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Function mo_ldap_debugger_test_connection
		 *
		 * @return string
		 */
		public function mo_ldap_debugger_test_connection() {
			$message        = '';
			$mo_ldap_config = new Mo_LDAP_Cloud_Configuration_Handler();
			$content        = $mo_ldap_config->test_connection();
			$response       = json_decode( $content, true );

			if ( strcasecmp( $response['statusCode'], 'SUCCESS' ) === 0 ) {
				$message = $message . 'Connection was established successfully.';
			} elseif ( strcasecmp( $response['statusCode'], 'ERROR' ) === 0 ) {
				$message = $message . $response['statusMessage'];
			} elseif ( strcasecmp( $response['statusCode'], 'FAILED' ) === 0 ) {
				$message = $message . $response['statusMessage'];
			} else {
				$message = $message . 'There was an error connecting with the current settings.';
			}

			return $message;
		}

		/**
		 * Function mo_ldap_is_customer_validated : Check if valid customer.
		 *
		 * @return bool
		 */
		public static function mo_ldap_is_customer_validated() {
			$email        = get_option( 'mo_ldap_admin_email' );
			$customer_key = get_option( 'mo_ldap_admin_customer_key' );
			if ( empty( $email ) || empty( $customer_key ) || ! is_numeric( trim( $customer_key ) ) ) {
				return 0;
			} else {
				return get_option( 'mo_ldap_check_ln' ) ? get_option( 'mo_ldap_check_ln' ) : 0;
			}
		}

		/**
		 * Function upgrade_plugin : Called while upgrading plugin
		 *
		 * @return bool
		 */
		public static function upgrade_plugin() {
			delete_option( 'mo_ldap_license_flag_cloud' );
			delete_option( 'mo_ldap_ldap_login_status_cloud' );
			delete_option( 'mo_ldap_test_auth_cloud' );
			delete_option( 'mo_ldap_config_status_cloud' );
			delete_option( 'mo_ldap_activation_time_cloud' );
			delete_option( 'mo_ldap_xecurify_migration' );
			delete_option( 'mo_ldap_default_config' );

			return true;
		}

		/**
		 * Function get_api_argument : Create arguments for API call.
		 *
		 * @param  mixed $field_string : body parameters.
		 * @return array
		 */
		public static function get_api_argument( $field_string ) {
			$customer_key              = ! empty( get_option( 'mo_ldap_admin_customer_key' ) ) ? get_option( 'mo_ldap_admin_customer_key' ) : '16555';
			$api_key                   = ! empty( get_option( 'mo_ldap_admin_api_key' ) ) ? get_option( 'mo_ldap_admin_api_key' ) : 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
			$current_time_in_milli_sec = round( microtime( true ) * 1000 );
			$string_to_hash            = $customer_key . number_format( $current_time_in_milli_sec, 0, '', '' ) . $api_key;
			$hash_value                = hash( 'sha512', $string_to_hash );
			$timestamp_header          = number_format( $current_time_in_milli_sec, 0, '', '' );

			$headers = array(
				'Content-Type'  => 'application/json',
				'Customer-Key'  => $customer_key,
				'Timestamp'     => $timestamp_header,
				'Authorization' => $hash_value,
				'charset'       => 'UTF-8',
			);

			return array(
				'method'      => 'POST',
				'body'        => $field_string,
				'timeout'     => '10',
				'redirection' => '10',
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => $headers,
			);
		}

		/**
		 * Function get_customer_auth_headers : Get header for API call
		 *
		 * @param  mixed $current_time_in_milli_sec : current time in milli seconds.
		 * @return array
		 */
		public static function get_customer_auth_headers( $current_time_in_milli_sec ) {
			$customer_key = ! empty( get_option( 'mo_ldap_admin_customer_key' ) ) ? get_option( 'mo_ldap_admin_customer_key' ) : '';
			$api_key      = ! empty( get_option( 'mo_ldap_admin_api_key' ) ) ? get_option( 'mo_ldap_admin_api_key' ) : '';

			if ( empty( $customer_key ) || empty( $api_key ) ) {
				return array(
					'status'         => 'ERROR',
					'status_message' => 'Error Fetching Customer Header Authorisation. Please login using miniOrange premium account in LDAP/AD Login for Shared Hosting Plugin for WordPress.',
				);
			}

			$string_to_hash = $customer_key . number_format( $current_time_in_milli_sec, 0, '', '' ) . $api_key;
			$hash_value     = hash( 'sha512', $string_to_hash );

			return array(
				'status'         => 'SUCCESS',
				'customer_key'   => $customer_key,
				'authorization'  => $hash_value,
				'status_message' => 'Authorization Header fetched successfully',
			);
		}

		/**
		 * Function check_customer_ln : Check license
		 *
		 * @param  mixed $license_name : license plan name.
		 * @return string
		 */
		public function check_customer_ln( $license_name ) {
			$url          = MO_LDAP_CLOUD_HOST_NAME . '/moas/rest/customer/license';
			$customer_key = get_option( 'mo_ldap_admin_customer_key' );

			$fields = array(
				'customerId'      => $customer_key,
				'applicationName' => $license_name,
			);

			$field_string = wp_json_encode( $fields );

			$args    = self::get_api_argument( $field_string );
			$content = wp_remote_post( $url, $args );

			if ( 400 === $content['response']['code'] && strcasecmp( $content['body'], 'Invalid parameters' ) === 0 ) {
				return wp_json_encode(
					array(
						'status'        => 'LICENSE_ERROR',
						'statusMessage' => 'Error fetching license details. Please contact at info@xecurify.com or ldapsupport@xecurify.com for support.',
					)
				);
			} elseif ( 200 !== $content['response']['code'] ) {
				return wp_json_encode(
					array(
						'status'        => 'ERROR',
						'statusMessage' => 'Error Connecting to miniOrange. Please contact at info@xecurify.com or ldapsupport@xecurify.com for more details.',
					)
				);
			} elseif ( is_wp_error( $content ) ) {
				return wp_json_encode(
					array(
						'status'        => 'WP_ERROR',
						'statusMessage' => 'Error Connecting to miniOrange. Please contact at info@xecurify.com or ldapsupport@xecurify.com for more details.',
					)
				);
			}

			return $content['body'];
		}

		/**
		 * Function mo_ldap_cloud_update_customer_license
		 *
		 * @return void
		 */
		public static function mo_ldap_cloud_update_customer_license() {
			$check_license = new Mo_LDAP_Cloud_Utils();
			$content       = json_decode( $check_license->check_customer_ln( 'wp_ldap_cloud_premium_plan' ), true );

			if ( strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
				$mo_license_status = 1;
				unset( $content['status'] );
				unset( $content['message'] );
				update_option( 'mo_ldap_cloud_license_info', self::encrypt( maybe_serialize( $content ) ) );
			} elseif ( strcasecmp( $content['status'], 'LICENSE_ERROR' ) === 0 ) {
				$mo_license_status = -99;
				$license_details   = array(
					'licenseType'   => 'Unable to fetch License Details',
					'licensePlan'   => 'Unable to fetch License Details',
					'noOfUsers'     => 'Unable to fetch License Details',
					'trial'         => false,
					'licenseExpiry' => '',
					'supportExpiry' => '',
				);
				update_option( 'mo_ldap_cloud_license_info', self::encrypt( maybe_serialize( $license_details ) ) );
			} else {
				$mo_ldap_config  = new Mo_LDAP_Cloud_Configuration_Handler();
				$content         = $mo_ldap_config->test_authentication( 'license test', 'license test' );
				$response        = json_decode( $content, true );
				$license_details = array(
					'licenseType'   => 'TRIAL',
					'licensePlan'   => 'DEMO',
					'noOfUsers'     => 'NA',
					'trial'         => true,
					'licenseExpiry' => '',
					'supportExpiry' => '',
				);

				if ( strcasecmp( $response['statusCode'], 'DENIED' ) === 0 ) {
					$mo_license_status = -1;
				} else {
					$mo_license_status = 0;
				}
				update_option( 'mo_ldap_cloud_license_info', self::encrypt( maybe_serialize( $license_details ) ) );
			}
			update_option( 'mo_ldap_license_status', $mo_license_status );
		}

		/**
		 * Function for getting faqs
		 *
		 * @return array
		 */
		public static function mo_ldap_cloud_get_faqs() {
			$faqs = array(
				'Allow access from Firewall (Pre-requisite)' => '<ul>
				<li> You need to allow incoming requests from hosts - <span class="mo_ldap_cloud_notice_ip_col">52.55.147.107</span> by a firewall rule for the port <span class="mo_ldap_cloud_notice_ip_col">389</span>(<span class="mo_ldap_cloud_notice_ip_col">636</span> for SSL or ldaps) on LDAP Server.</li>
					</ul>',
				'Why is my LDAPS connection not working even after whitelisting miniOrange IPs on port 636?' => '<ul>
					<li>To enable LDAPS connection on our miniOrange LDAP/AD Login for Cloud & Shared Hosting Plugin you are required to get your LDAPS certificate uploaded on our miniOrange Servers. To get your certificates uploaded please <a href="https://www.miniorange.com/contact" target="_blank" rel="noopener"> contact us. </a></li>
					</ul>',
				'Why is Contact LDAP Server not working?' => '<ul>
						<ol>
							<li>Check your LDAP Server URL to see if it is correct.<br>
							eg. ldap://myldapserver.domain:389 , ldap://89.38.192.1:389. When using SSL, the host may have to take the form ldaps://host:636.</li>
							<li>Your LDAP Server may be behind a firewall. Check if the firewall is open to allow requests from hosts - <span class="mo_ldap_cloud_faq_ip_port">52.55.147.107</span> and the port <span class="mo_ldap_cloud_faq_ip_port">389</span> (<span class="mo_ldap_cloud_faq_ip_port">636</span> for SSL or ldaps) on LDAP Server.</li>
						</ol>
					</ul>',
				'Why is Test LDAP Configuration not working?' => '<ul>
						<ol>
							<li>Check if you have entered valid Service Account DN (Distinguished Name) of the LDAP server. <br>e.g. cn=username,cn=group,dc=domain,dc=com<br>
							uid=username,ou=organisational unit,dc=domain,dc=com</li>
							<li>Check if you have entered correct Password for the Service Account.</li>
					    </ol>
					</ul>',

				'Why is Test Authentication not working?' => '<ol>
						<li>The username/password combination you provided may be incorrect.</li>
						<li>You may have provided a <strong>Search Base(s)</strong> in which the user does not exist.</li>
						<li>Your <strong>Search Filter</strong> may be incorrect and the username mapping may be to an LDAP attribute other than the ones provided in the Search Filter</li>
						<li>You may have provided an incorrect <strong>Distinguished Name attribute</strong> for your LDAP Server.
		            </ol>',

				'What are the LDAP Service Account Credentials?' => '<ol>
						<li>Service account is an non privileged user which is used to bind to the LDAP Server. It is the preferred method of binding to the LDAP Server if you have to perform search operations on the directory.</li>
						<li>The distinguished name(DN) of the service account object and the password are provided as credentials.</li>
					</ol>',

				'What is meant by Search Base in my LDAP environment?' => '<ol>
						<li>Search Base denotes the location in the directory where the search for a particular directory object begins.</li>
						<li>It is denoted as the distinguished name of the search base directory object. eg: CN=Users,DC=domain,DC=com.</li>
					</ol>',
				'What is meant by Search Filter in my LDAP environment?' => '<ol>
				<li>Search Filter is a basic LDAP Query for searching users based on mapping of username to a particular LDAP attribute.</li>
				<li>The following are some commonly used Search Filters. You will need to use a search filter which uses the attributes specific to your LDAP environment. Confirm from your LDAP administrator.</li>
				</ol>
				<ul>
				  <li>
						<table aria-hidden="true">
							<tr><td>common name</td><td>(&(|(objectClass=person)(objectClass=user))(<strong>cn</strong>=?))</td></tr>
							<tr><td>email</td><td>(&(|(objectClass=person)(objectClass=user))(<strong>mail</strong>=?))</td></tr>
							<tr><td>logon name</td><td>(&(|(objectClass=person)(objectClass=user))(<strong>sAMAccountName</strong>=?))<br/>(&(|(objectClass=person)(objectClass=user))(<strong>userPrincipalName</strong>=?))</td></tr>
							<tr><td>custom attribute where you store your WordPress usernames use</td> <td>(&(|(objectClass=person)(objectClass=user))(<strong>customAttribute</strong>=?))</td></tr>
							<tr><td>if you store WordPress usernames in multiple attributes(eg: some users login using email and others using their username)</td><td>(&(|(objectClass=person)(objectClass=user))(<strong>|</strong>(<strong>cn=?</strong>)(<strong>mail=?</strong>)))</td></tr>
						</table>
					</li>
				</ul>',
				'How do users present in different Organizational Units(OU\'s) login into WordPress?' => '<ol>
					<li> can provide multiple search bases seperated by a semi-colon to ensure users present in different OU\'s are able to login into WordPress.</li>
					<li> You can also provide the RootDN value in the Search Base so that users in all subtrees of the RootDN are able to login.</li>
		            </ol>',
				'Some of my users login using their email and the rest using their usernames. How will both of them be able to login?' => '<ol>
					<li> You need to provide a search filter which checks for the username against multiple LDAP attributes.</li>
					<li> For example, if you have some users who login using their email and some using their username, the following search filter can be applied: (&(|(objectClass=person)(objectClass=user))(|(mail=?)(cn=?)))</li>
					</ol>',
				'What are the different Distinguished Name attributes?' => '<ol>
					<li>The distinguished name attribute depends on the LDAP environment.</li>
					<li>For example, Active Directory (AD) uses distinguishedName to store the Distinguished Name (DN) attribute</li>
					</ol>',
				'How to use the services if I don\'t want to whitelist IPs in my AD for LDAP/LDAPS connection directly?' => '<ul>
				
				<li><strong ><b> What is the miniOrange gateway? </b><br></strong>
				<ul> 
				<li>
				<p>miniOrange gateway is a small piece of software that allows users to log in to publicly or privately hosted sites using Active Directory, OpenLDAP, and other LDAP servers credentials. If your site\'s LDAP server isn\'t publicly accessible, this module can be used in conjunction with the miniOrange LDAP Gateway, which is installed on the intranet\'s DMZ server. <br> <br> The miniOrange gateway can be hosted on your domain controller or any server in your network. The gateway connects directly to the LDAP server, storing all the information of the LDAP configuration on your network itself. The gateway then connects to the miniOrange IdP server using the HTTP/HTTPS protocol, which provides the authentication service to the LDAP Plugin. </p></li>
				</li>
				</ul>
				</ul>',
			);

			return $faqs;
		}
	}
}
