<?php
/**
 * This file contains class to save all the options.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Handlers
 */

namespace MO_LDAP_CLOUD\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-mo-ldap-cloud-customer-setup-handler.php';
require_once 'class-mo-ldap-cloud-configuration-handler.php';
require_once 'class-mo-ldap-cloud-role-mapping-handler.php';
require_once dirname( __DIR__ ) . '/lib/class-mo-ldap-cloud-config-details.php';
require_once dirname( __DIR__ ) . '/lib/class-mo-ldap-cloud-account-details.php';
require_once dirname( __DIR__ ) . '/lib/class-mo-ldap-cloud-attribute-mapping-details.php';
require_once dirname( __DIR__ ) . '/lib/class-mo-ldap-cloud-role-mapping-details.php';



use MO_LDAP_CLOUD\Utils\Mo_LDAP_Cloud_Utils;
use MO_LDAP_CLOUD\Handlers\MO_LDAP_Cloud_Customer_Setup_Handler;
use MO_LDAP_CLOUD\Handlers\Mo_LDAP_Cloud_Configuration_Handler;
use MO_LDAP_CLOUD\Handlers\Mo_Ldap_Cloud_Role_Mapping_Handler;

if ( ! class_exists( 'Mo_Ldap_Cloud_Save_Options_Handler' ) ) {
	/**
	 * Save options handler class.
	 */
	class Mo_Ldap_Cloud_Save_Options_Handler {

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
			add_action( 'admin_init', array( $this, 'mo_ldap_cloud_save_options' ) );
			add_action( 'init', array( $this, 'mo_ldap_cloud_test_configuration' ) );
			add_action( 'mo_ldap_cloud_logs_cleanup_event', array( $this, 'mo_ldap_cloud_clear_authentication_report' ) );
			add_filter( 'cron_schedules', array( $this, 'mo_ldap_cloud_logs_cleanup_cron_schedules' ) );
			$this->utils = new Mo_LDAP_Cloud_Utils();
			add_action( 'admin_init', array( $this, 'mo_ldap_check_ldap_security_notice' ) );
		}

		/**
		 * Function miniorange_ldap_cloud_export : Export Plugin configuration.
		 *
		 * @return void
		 */
		private function miniorange_ldap_cloud_export() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'miniorange-wp-ldap-login' ) );
			}
			if ( check_admin_referer( 'mo_ldap_cloud_export_nonce' ) && array_key_exists( 'option', $_POST ) && 'mo_ldap_cloud_export' === sanitize_text_field( wp_unslash( $_POST['option'] ) ) ) {
				$tab_class_name      = $this->utils::secure_unserialize_option( TAB_LDAP_CLOUD_CLASS_NAMES );
				$configuration_array = array();
				foreach ( $tab_class_name as $key => $value ) {
					$configuration_array[ $key ] = $this->mo_cloud_get_configuration_array( $value );
				}
				header( 'Content-Disposition: attachment; filename=miniorange-ldap-cloud-config.json' );
				echo wp_json_encode( $configuration_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
				exit;
			}
		}
		/**
		 * Function mo_ldap_cloud_clear_authentication_report : To delete all existing user authentication logs
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_clear_authentication_report() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'cloud_user_report';
			$delete     = $wpdb->query( $wpdb->prepare( 'TRUNCATE TABLE %1s', $table_name ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Changing a custom table. Table name is safe as it uses wpdb->prefix.
			wp_cache_delete( 'mo_ldap_cloud_user_report_cache' );
			wp_cache_delete( 'mo_ldap_cloud_user_report_count_cache' );
			wp_cache_delete( 'wp_user_reports_pagination_cache' );
		}

		/**
		 * Function mo_ldap_cloud_logs_cleanup_cron_schedules : Adds custom intervals to WordPress cron schedules
		 *
		 * @param  array $schedules Cron schedules array to be filtered.
		 * @return array Updated cron schedules including weekly, monthly, and yearly intervals.
		 */
		public function mo_ldap_cloud_logs_cleanup_cron_schedules( $schedules ) {
			$schedules['weekly']  = array(
				'interval' => 604800,
				'display'  => __( 'Once Weekly', 'miniorange-wp-ldap-login' ),
			);
			$schedules['monthly'] = array(
				'interval' => 2635200,
				'display'  => __( 'Once Monthly', 'miniorange-wp-ldap-login' ),
			);
			$schedules['yearly']  = array(
				'interval' => 31556926,
				'display'  => __( 'Once Yearly', 'miniorange-wp-ldap-login' ),
			);
			return $schedules;
		}

		/**
		 * Function mo_ldap_check_ldap_security_notice : To display security notice
		 *
		 * @return void
		 */
		public function mo_ldap_check_ldap_security_notice() {
			$search_filter = $this->utils::decrypt( get_option( 'mo_ldap_search_filter' ) );
			if ( stripos( $search_filter, 'objectClass=*' ) && 1 !== get_option( 'mo_ldap_cloud_remove_security_notice' ) && current_user_can( 'manage_options' ) ) {
				add_action( 'admin_notices', array( $this, 'mo_ldap_cloud_show_security_notice' ) );
			}
		}

		/**
		 * Function mo_ldap_cloud_show_security_notice : Security notice
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_show_security_notice() {
			?>
			<div style="display:flex;" class="notice notice-error">
				<div>
				<img width="65px" height="65px" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'logo.png' ); ?>" 
					class="alignleft mo-security-notice" 
					alt="miniOrange logo">

				</div>
				<div style="width: 100%;">
					<div class="alignright" style="padding-top: 12px;">
						<form method="POST" action="">
							<?php wp_nonce_field( 'mo_ldap_cloud_remove_security_notice_nonce' ); ?>
							<input type="hidden" name="option" value="mo_ldap_cloud_remove_security_notice" />
							<button style="background: transparent; cursor: pointer;" type="submit"> 
								<img width="12px" height="12px" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'close.png' ); ?>" 
								alt="miniOrange logo">
							</button>
						</form>
					</div>
					<h2 class="mo-lic-admin-notice-heading">Security Notice: Potential Security Risk Detected</h2>
					<div class="alignleft">
						<p class="mo-lic-admin-notice-text"> 
							Your current search filter uses <b>objectClass=*</b>, which may pose security risks by retrieving a large set of entries, potentially leading to unauthorized access or data exposure.
						</p>
						<p class="mo-lic-admin-notice-text">
							To enhance security, we recommend using a more specific filter to restrict data retrieval. You can update your search filter in the <a href="<?php echo esc_url( admin_url( 'admin.php?page=mo_ldap_cloud_login&subtab=config#mo_ldap_cloud_user_mapping_section' ) ); ?>"> <b>  User Mapping Configuration Section </b> </a> .
						</p>
						<p class="mo-lic-admin-notice-text">
							For further information or assistance, feel free to contact us at <a href="mailto:ldapsupport@xecurify.com"><b>ldapsupport@xecurify.com</b></a>.
						</p>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Function for exporting authentication report in csv file.
		 *
		 * @return void
		 */
		private function miniorange_ldap_cloud_export_authentication_report() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'miniorange-wp-ldap-login' ) );
			}
			global $wpdb;
			$wp_user_reports_cache = wp_cache_get( 'mo_ldap_cloud_user_report_cache' );
			if ( $wp_user_reports_cache ) {
				$user_reports = $wp_user_reports_cache;
			} else {
				$table_name   = $wpdb->prefix . 'cloud_user_report';
				$user_reports = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s', $table_name ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- Fetching data from a custom table. Table name is safe as it uses wpdb->prefix.
				wp_cache_set( 'mo_ldap_cloud_user_report_cache', $user_reports );
			}

			$csv_file = fopen( 'php://output', 'w' );

			if ( ! empty( $user_reports ) ) {
				$fields = array( 'ID', 'USERNAME', 'TIME', 'LDAP STATUS', 'LDAP ERROR' );
				fputcsv( $csv_file, $fields );
				foreach ( $user_reports as $user_report ) {
					$line_data = array( $user_report->id, $user_report->user_name, $user_report->time, $user_report->ldap_status, sanitize_text_field( $user_report->ldap_error ) );
					fputcsv( $csv_file, $line_data );
				}
			} else {
				$message = 'No Logs Available';
				update_option( 'mo_ldap_message', $message );
				$this->utils->show_error_message();
				return;
			}

			fclose( $csv_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose, WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- This file should not be saved locally.
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment; filename=ldap-cloud-authentication-report.csv' );

			exit;
		}

		/**
		 * Function miniorange_ldap_cloud_create_authentication_logs_table creates authentication report logs table.
		 *
		 * @return void
		 */
		private function miniorange_ldap_cloud_create_authentication_logs_table() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'miniorange-wp-ldap-login' ) );
			}
			global $prefix_my_db_version;
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE if not exists`{$wpdb->base_prefix}cloud_user_report` (
				  id int NOT NULL AUTO_INCREMENT,
				  user_name varchar(50) NOT NULL,
				  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				  ldap_status varchar(250) NOT NULL,
				  ldap_error varchar(250) ,
				  PRIMARY KEY  (id)
				) $charset_collate;";

			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . '/wp-admin/includes/upgrade.php';
			}

			dbDelta( $sql );

			update_option( 'mo_ldap_cloud_user_logs_table_exists', 1 );
		}

		/**
		 * Function mo_cloud_get_configuration_array : get plugin configuration array
		 *
		 * @param  string $class_name Class name.
		 * @return array
		 */
		private function mo_cloud_get_configuration_array( $class_name ) {
			$class_object         = call_user_func( $class_name . '::get_constants' );
			$mapping_count        = get_option( 'mo_ldap_role_mapping_count' );
			$mo_array             = array();
			$mo_map_key           = array();
			$mo_map_value         = array();
			$mo_custom_attributes = array();
			foreach ( $class_object as $key => $value ) {
				$key = strtolower( $key );
				if ( 'mo_ldap_server_url' === $value || 'mo_ldap_server_password' === $value || 'mo_ldap_server_dn' === $value || 'mo_ldap_search_base' === $value || 'mo_ldap_search_filter' === $value || 'mo_ldap_username_attributes' === $value ) {
					$flag = 1;
				} else {
					$flag = 0;
				}
				if ( 'mo_ldap_mapping_key_' === $value ) {
					for ( $i = 1; $i <= $mapping_count; $i++ ) {
						$mo_map_key[ $i ] = get_option( $value . $i );
					}
					$mo_option_exists = $mo_map_key;
				} elseif ( 'mo_ldap_mapping_value_' === $value ) {
					for ( $i = 1; $i <= $mapping_count; $i++ ) {
						$mo_map_value[ $i ] = get_option( $value . $i );
					}
					$mo_option_exists = $mo_map_value;
				} elseif ( 'mo_ldap_custom_attribute_' === $value ) {
					$custom_attributes = array();
					$all_options       = wp_load_alloptions();
					foreach ( $all_options as $name => $value ) {
						if ( ! ( strpos( $name, 'mo_ldap_custom_attribute' ) === false ) ) {
							$custom_attributes[ $name ] = $value;
						}
					}
					$i = 1;
					foreach ( $custom_attributes as $attribute => $value ) {
						$mo_custom_attributes[ $i++ ] = $value;
					}
					$mo_option_exists = $mo_custom_attributes;
				} else {
					$mo_option_exists = get_option( $value );
				}
				if ( $mo_option_exists ) {
					if ( is_serialized( $mo_option_exists ) !== false ) {
						$mo_option_exists = $this->utils::secure_unserialize_option( $mo_option_exists );
					}
					if ( 1 === $flag ) {
						if ( 'mo_ldap_server_password' === $value && ( empty( get_option( 'mo_ldap_cloud_export_pass' ) || '0' === get_option( 'mo_ldap_cloud_export_pass' ) ) ) ) {
							continue;
						} elseif ( 'mo_ldap_server_password' === $value && '1' === get_option( 'mo_ldap_cloud_export_pass' ) ) {
							$mo_array[ $key ] = $mo_option_exists;
						} elseif ( 'user_attr' === $key ) {
							$mo_array[ $key ] = $mo_option_exists;
						} else {
							$mo_array[ $key ] = $this->utils::decrypt( $mo_option_exists );
						}
					} else {
						$mo_array[ $key ] = $mo_option_exists;
					}
				}
			}
			return $mo_array;
		}

		/**
		 * Function mo_ldap_handle_mo_check_ln : Checks and verifies the licenses.
		 *
		 * @param  boolean $show_message boolean variable indicates whether to display the message.
		 * @return void
		 */
		private function mo_ldap_handle_mo_check_ln( $show_message ) {
			$content = json_decode( $this->utils->check_customer_ln( 'wp_ldap_cloud_premium_plan' ), true );
			if ( strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
				update_option( 'mo_ldap_license_status', 1 );
				array_key_exists( 'licensePlan', $content ) && ! empty( $content['licensePlan'] ) ? update_option( 'mo_ldap_check_ln', base64_encode( $content['licensePlan'] ) ) : update_option( 'mo_customer_check_ln', '' ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- function not being used to obfuscate the code
				if ( $show_message ) {
					array_key_exists( 'licensePlan', $content ) && ! empty( $content['licensePlan'] ) ? update_option( 'mo_ldap_message', 'Thank you. You have upgraded to ' . esc_html( $content['licensePlan'] ) . '  for ' . esc_html( $content['noOfUsers'] ) . '  users. ' ) : update_option( 'mo_ldap_message', 'You are on our FREE plan. Check Licensing Plans to learn how to upgrade.' );
				}
				if ( array_key_exists( 'licensePlan', $content ) && ! empty( $content['licensePlan'] ) ) {
					delete_option( 'mo_ldap_free_version' );
				}
				$this->utils->show_success_message();
			} elseif ( strcasecmp( $content['status'], 'ERROR' ) === 0 || strcasecmp( $content['status'], 'WP_ERROR' ) === 0 ) {
				update_option( 'mo_ldap_message', esc_html( $content['statusMessage'] ) );
				$this->utils->show_error_message();
			} elseif ( strcasecmp( $content['status'], 'LICENSE_ERROR' ) === 0 ) {
				update_option( 'mo_ldap_message', esc_html( $content['statusMessage'] ) );
				$this->utils->show_error_message();
			} else {
				$mo_license_status = 0;
				$mo_ldap_config    = new Mo_LDAP_Cloud_Configuration_Handler();
				$content           = $mo_ldap_config->test_authentication( 'license test', 'license test' );
				$response          = json_decode( $content, true );
				if ( strcasecmp( $response['statusCode'], 'DENIED' ) === 0 ) {
					$mo_license_status = -1;
				}
				update_option( 'mo_ldap_license_status', $mo_license_status );
				if ( -1 === $mo_license_status ) {
					update_option( 'mo_ldap_message', 'Your license has expired. Please upgrade to the premium plan to continue using our service.' );
					$this->utils->show_error_message();
				} else {
					update_option( 'mo_ldap_message', 'Currently you are in our Free Plan. Please check our Licensing Plan below to upgrade your plan.' );
					$this->utils->show_success_message();
				}
			}
		}


		/**
		 * Function save_success_customer_config : Save customer configuration.
		 *
		 * @param  string $id customer key.
		 * @param  string $api_key api key.
		 * @param  string $token customer token.
		 * @param  string $message message to be displayed.
		 * @return void
		 */
		private function save_success_customer_config( $id, $api_key, $token, $message ) {
			update_option( 'mo_ldap_admin_customer_key', $id );
			update_option( 'mo_ldap_admin_api_key', $api_key );
			update_option( 'mo_ldap_customer_token', $token );
			update_option( 'mo_ldap_message', esc_html( $message ) );
			delete_option( 'mo_ldap_verify_customer' );
			delete_option( 'mo_ldap_new_registration' );
			delete_option( 'mo_ldap_registration_status' );
			$this->utils::mo_ldap_cloud_update_customer_license();
			$this->utils->show_success_message();
			if ( 'Registration complete!' === $message ) {
				wp_safe_redirect( admin_url() . 'admin.php?page=mo_ldap_cloud_login&tab=pricing' );
			}
		}

		/**
		 * Function mo_ldap_cloud_save_options : Saves and updates plugin options.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_save_options() {
			if ( isset( $_POST['option'] ) && current_user_can( 'manage_options' ) ) {
				$post_option = sanitize_text_field( wp_unslash( $_POST['option'] ) );
				if ( 'mo_ldap_cloud_register_customer' === $post_option && check_admin_referer( 'mo_ldap_cloud_register_nonce' ) ) {
					$phone                  = isset( $_POST['register_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['register_phone'] ) ) : '';
					$email                  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
					$password               = isset( $_POST['password'] ) ? $_POST['password'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Should not be sanitised as Strong Passwords contains special characters
					$confirm_password       = isset( $_POST['confirmPassword'] ) ? $_POST['confirmPassword'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Should not be sanitised as Strong Passwords contains special characters
					$company_name           = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
					$use_case               = isset( $_POST['usecase'] ) ? sanitize_text_field( wp_unslash( $_POST['usecase'] ) ) : '';
					$send_registration_mail = new MO_LDAP_Cloud_Customer_Setup_Handler();
					if ( empty( $email ) || empty( $password ) || empty( $confirm_password ) ) {
						update_option( 'mo_ldap_message', 'All the fields are required. Please enter valid entries.' );
						$this->utils->show_error_message();
						return;
					} elseif ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						update_option( 'mo_ldap_message', 'Please enter a valid email address.' );
						$this->utils->show_error_message();
						return;
					} elseif ( strlen( $password ) < 6 || strlen( $confirm_password ) < 6 ) {
						update_option( 'mo_ldap_message', 'Choose a password with minimum length 6.' );
						$this->utils->show_error_message();
						return;
					}
					update_option( 'mo_ldap_admin_phone', $phone );
					update_option( 'mo_ldap_admin_email', $email );
					update_option( 'mo_ldap_admin_company', $company_name );
					update_option( 'mo_ldap_admin_use_case', $use_case );
					if ( $password === $confirm_password ) {
						$customer = new MO_LDAP_Cloud_Customer_Setup_Handler();
						$content  = json_decode( $customer->check_customer(), true );
						if ( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND' ) === 0 ) {
							$customer_key = $customer->create_customer( $password );
							if ( ! empty( $customer_key ) ) {
								$customer_key = json_decode( $customer_key, true );
								if ( strcasecmp( $customer_key['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS' ) === 0 ) {
									$customer_content = $customer->get_customer_key( $password );
									$customer_key     = json_decode( $customer_content, true );
									if ( json_last_error() === JSON_ERROR_NONE ) {
										$this->save_success_customer_config( $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], 'Your account has been retrieved successfully.' );
									} else {
										update_option( 'mo_ldap_message', 'You already have an account with miniOrange. Please enter a valid password.' );
										update_option( 'mo_ldap_verify_customer', 'true' );
										delete_option( 'mo_ldap_new_registration' );
										$this->utils->show_error_message();
									}
								} elseif ( strcasecmp( $customer_key['status'], 'ERROR' ) === 0 ) {
									update_option( 'mo_ldap_message', esc_html( $customer_key['message'] ) );
									$email   = get_option( 'mo_ldap_admin_phone' );
									$phone   = get_option( 'mo_ldap_admin_email' );
									$message = "The customer wasn't able to register with miniOrange because of unknown error.";
									delete_option( 'mo_ldap_admin_use_case' );
									$this->utils->show_error_message();
								} elseif ( strcasecmp( $customer_key['status'], 'SUCCESS' ) === 0 ) {
									$this->save_success_customer_config( $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], 'Registration complete!' );
									delete_option( 'mo_ldap_admin_use_case' );
								} else {
									$message = $customer_key['message'];
									update_option( 'mo_ldap_message', esc_html( $message ) );
									$this->utils->show_error_message();
								}
							}
						} elseif ( strcasecmp( $content['status'], 'ERROR' ) === 0 ) {
							update_option( 'mo_ldap_message', esc_html( $content['message'] ) );
							delete_option( 'mo_ldap_admin_use_case' );
							$this->utils->show_error_message();
						} else {
							$content      = $customer->get_customer_key( $password );
							$customer_key = json_decode( $content, true );
							if ( strcasecmp( $customer_key['status'], 'ERROR' ) === 0 ) {
								update_option( 'mo_ldap_message', esc_html( $customer_key['message'] ) );
								$this->utils->show_error_message();
							} elseif ( json_last_error() === JSON_ERROR_NONE ) {
								$this->save_success_customer_config( $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], 'Your account has been retrieved successfully.' );
							} else {
								update_option( 'mo_ldap_message', 'You already have an account with miniOrange. Please enter a valid password.' );
								update_option( 'mo_ldap_verify_customer', 'true' );
								delete_option( 'mo_ldap_new_registration' );
								$this->utils->show_error_message();
							}
						}
					} else {
						update_option( 'mo_ldap_message', 'Password and Confirm password do not match.' );
						delete_option( 'mo_ldap_verify_customer' );
						$this->utils->show_error_message();
						return;
					}
				} elseif ( 'mo_ldap_verify_customer' === $post_option && check_admin_referer( 'mo_ldap_cloud_verify_customer_nonce' ) ) {
					$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
					$password = isset( $_POST['password'] ) ? $_POST['password'] : ''; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Should not be sanitised as Strong Passwords contains special characters
					if ( empty( $email ) || empty( $password ) ) {
						update_option( 'mo_ldap_message', 'All the fields are required. Please enter valid entries.' );
						$this->utils->show_error_message();
						return;
					}
					update_option( 'mo_ldap_admin_email', $email );
					$customer     = new MO_LDAP_Cloud_Customer_Setup_Handler();
					$content      = $customer->get_customer_key( $password );
					$customer_key = json_decode( $content, true );
					if ( strcasecmp( $customer_key['status'], 'ERROR' ) === 0 ) {
						update_option( 'mo_ldap_message', esc_html( $customer_key['message'] ) );
						$this->utils->show_error_message();
					} elseif ( json_last_error() === JSON_ERROR_NONE ) {
						$this->save_success_customer_config( $customer_key['id'], $customer_key['apiKey'], $customer_key['token'], 'Your account has been retrieved successfully.' );
					} else {
						update_option( 'mo_ldap_message', 'Invalid username or password. Please try again.' );
						$this->utils->show_error_message();
					}
				} elseif ( strcasecmp( $post_option, 'mo_ldap_cloud_authentication_report' ) === 0 && check_admin_referer( 'mo_ldap_cloud_authentication_report' ) ) {
							$this->miniorange_ldap_cloud_export_authentication_report();
				} elseif ( strcasecmp( $post_option, 'user_report_logs_cleanup' ) === 0 && check_admin_referer( 'log_cleanup_settings_nonce' ) ) {
					$enable_log_cleanup = isset( $_POST['mo_ldap_cloud_user_report_log_cleanup'] ) ? absint( wp_unslash( $_POST['mo_ldap_cloud_user_report_log_cleanup'] ) ) : 0;
					$enable_log_cleanup = 1 === $enable_log_cleanup ? 1 : 0;
					update_option( 'mo_ldap_cloud_enable_log_cleanup', $enable_log_cleanup );
					if ( $enable_log_cleanup ) {
						$this->mo_ldap_cloud_schedule_log_clearing_cron( get_option( 'mo_ldap_cloud_log_cleanup_interval', 'weekly' ) );
					} else {
						$this->mo_ldap_cloud_unschedule_log_clearing_cron();
					}
				} elseif ( strcasecmp( $post_option, 'user_report_logs_clearing_schedule' ) === 0 && check_admin_referer( 'log_cleanup_interval_nonce' ) ) {
					$log_cleanup_interval = isset( $_POST['log_cleanup_interval'] ) ? sanitize_text_field( wp_unslash( $_POST['log_cleanup_interval'] ) ) : 'weekly';
					update_option( 'mo_ldap_cloud_log_cleanup_interval', $log_cleanup_interval );
					if ( get_option( 'mo_ldap_cloud_enable_log_cleanup', 0 ) ) {
						$this->mo_ldap_cloud_schedule_log_clearing_cron( $log_cleanup_interval );
					}
				} elseif ( 'mo_ldap_enable' === $post_option && check_admin_referer( 'mo_ldap_cloud_enable_login_nonce' ) ) {
					update_option( 'mo_ldap_enable_login', isset( $_POST['enable_ldap_login'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_ldap_login'] ) ) : '0' );
					if ( ! empty( get_option( 'mo_ldap_enable_login' ) ) && '1' === get_option( 'mo_ldap_enable_login' ) ) {
						update_option( 'mo_ldap_register_user', '1' );
					}
					update_option( 'mo_ldap_cloud_disabled_local_login', '0' );
					if ( ! empty( get_option( 'mo_ldap_enable_login' ) ) && '1' === get_option( 'mo_ldap_enable_login' ) ) {
						update_option( 'mo_ldap_message', 'Login through your LDAP has been enabled.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'Login through your LDAP has been disabled.' );
						$this->utils->show_error_message();
					}
				} elseif ( strcmp( $post_option, 'cloud_user_report_logs' ) === 0 && check_admin_referer( 'cloud_user_report_logs' ) ) {

					$enable_user_report_logs = ( isset( $_POST['mo_ldap_cloud_user_report_log'] ) && strcmp( sanitize_text_field( wp_unslash( $_POST['mo_ldap_cloud_user_report_log'] ) ), 1 ) === 0 ) ? 1 : 0;

					update_option( 'mo_ldap_cloud_local_user_report_log', $enable_user_report_logs );
					$user_logs_table_exists = get_option( 'mo_ldap_cloud_user_logs_table_exists' );
					$user_reporting         = get_option( 'mo_ldap_cloud_local_user_report_log' );
					if ( strcasecmp( $user_reporting, '1' ) === 0 && strcasecmp( $user_logs_table_exists, '1' ) !== 0 ) {
						$this->miniorange_ldap_cloud_create_authentication_logs_table();
					}
				} elseif ( strcasecmp( $post_option, 'mo_ldap_cloud_clear_authentication_report' ) === 0 && check_admin_referer( 'mo_ldap_cloud_clear_authentication_report' ) ) {
					$this->mo_ldap_cloud_clear_authentication_report();
					update_option( 'mo_ldap_message', 'LDAP Authentication Logs cleared successfully' );
					$this->utils->show_success_message();
				} elseif ( 'mo_ldap_save_login_redirect' === $post_option && check_admin_referer( 'mo_ldap_cloud_save_login_redirect_nonce' ) ) {
					update_option( 'mo_ldap_redirect_to', isset( $_POST['redirect_to'] ) ? sanitize_text_field( wp_unslash( $_POST['redirect_to'] ) ) : '' );
					if ( 'profile' === sanitize_text_field( wp_unslash( $_POST['redirect_to'] ) ) ) {
						update_option( 'mo_ldap_message', 'Users will get redirected to profile page after login.' );
					} elseif ( 'homepage' === sanitize_text_field( wp_unslash( $_POST['redirect_to'] ) ) ) {
						update_option( 'mo_ldap_message', 'Users will get redirected to homepage after login.' );
					}
					if ( 'custom' !== sanitize_text_field( wp_unslash( $_POST['redirect_to'] ) ) && 'none' !== sanitize_text_field( wp_unslash( $_POST['redirect_to'] ) ) ) {
						$this->utils->show_success_message();
					} elseif ( 'none' === sanitize_text_field( wp_unslash( $_POST['redirect_to'] ) ) ) {
						update_option( 'mo_ldap_message', 'Redirection after login is disabled.' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_custom_redirect' === $post_option && check_admin_referer( 'mo_ldap_cloud_custom_redirect_nonce' ) ) {
					$custom_redirect = isset( $_POST['mo_ldap_custom_url'] ) ? esc_url_raw( wp_unslash( $_POST['mo_ldap_custom_url'] ) ) : '';
					update_option( 'mo_ldap_custom_redirect', $custom_redirect );
					update_option( 'mo_ldap_message', 'Users will get redirected to custom redirect URL after login.' );
					$this->utils->show_success_message();
				} elseif ( 'mo_ldap_register_user' === $post_option && check_admin_referer( 'mo_ldap_cloud_register_user_nonce' ) ) {
					update_option( 'mo_ldap_register_user', isset( $_POST['mo_ldap_register_user'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_register_user'] ) ) : '0' );
					if ( ! empty( get_option( 'mo_ldap_register_user' ) ) && '1' === get_option( 'mo_ldap_register_user' ) ) {
						update_option( 'mo_ldap_message', 'Auto Registering users has been enabled.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'Auto Registering users has been disabled.' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_authorized_users_only' === $post_option && check_admin_referer( 'mo_ldap_cloud_authorized_users_only_nonce' ) ) {
					update_option( 'mo_ldap_authorized_users_only', isset( $_POST['authorized_users_only'] ) ? sanitize_text_field( wp_unslash( $_POST['authorized_users_only'] ) ) : 0 );
					if ( '1' === get_option( 'mo_ldap_authorized_users_only' ) ) {
						update_option( 'mo_ldap_message', 'Protect Content by Login enabled.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'Protect Content by Login disabled.' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_save_config' === $post_option && check_admin_referer( 'mo_ldap_cloud_save_config_nonce' ) ) {
					$server_name         = '';
					$dn                  = '';
					$admin_ldap_password = '';
					$search_base         = '';
					$search_filter       = '(&(|(objectClass=person)(objectClass=user))(cn=?))';
					$user_attribute      = array();
					$ldap_protocol       = isset( $_POST['mo_ldap_protocol'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_protocol'] ) ) : '';
					$port_number         = isset( $_POST['mo_ldap_server_port_no'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_server_port_no'] ) ) : '';
					$server_address      = isset( $_POST['ldap_server'] ) ? sanitize_text_field( wp_unslash( $_POST['ldap_server'] ) ) : '';
					$server_name         = isset( $_POST['mo_ldap_protocol'] ) ? $ldap_protocol . '://' . $server_address . ':' . $port_number : '';
					$dn                  = isset( $_POST['dn'] ) ? sanitize_text_field( wp_unslash( $_POST['dn'] ) ) : '';
					$admin_ldap_password = isset( $_POST['admin_password'] ) ? sanitize_text_field( wp_unslash( $_POST['admin_password'] ) ) : '';
					$search_base         = isset( $_POST['search_base'] ) ? sanitize_text_field( wp_unslash( $_POST['search_base'] ) ) : '';
					$search_filter       = isset( $_POST['search_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['search_filter'] ) ) : '';
					if ( empty( $ldap_protocol ) || empty( $port_number ) || empty( $server_address ) || empty( $server_address ) || empty( $admin_ldap_password ) || empty( $search_base ) ) {
						update_option( 'mo_ldap_message', 'All the fields are required. Please enter valid entries.' );
						$this->utils->show_error_message();
						return;
					} else {
						$user_attribute_text     = isset( $_POST['user_attribute_text'] ) ? array_map(
							function ( $array_element ) {
								$array_element = sanitize_text_field( $array_element );
								$array_element = wp_unslash( $array_element );
								return $array_element;
							},
							$_POST['user_attribute_text'] //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitizing and unslashing post data inside the function
						) : array();
						$extra_user_attribute    = isset( $_POST['mo_ldap_cloud_extra_user_attributes'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_cloud_extra_user_attributes'] ) ) : '';
						$is_search_filter_toggle = isset( $_POST['ldap_cloud_search_filters'] ) ? 'enabled' : 'disabled';
						if ( 'enabled' === $is_search_filter_toggle && false === strpos( $search_filter, '?' ) ) {
							update_option( 'mo_ldap_message', 'Please enter a valid Search Filter.' );
							$this->utils->show_error_message();
							return;
						} elseif ( 'disabled' === $is_search_filter_toggle && ( empty( $user_attribute_text ) ) ) {
							update_option( 'mo_ldap_message', 'Please select atleast one User Attribute(s) to auto-create Custom Search Filter.' );
							$this->utils->show_error_message();
							return;
						}
						if ( 'disabled' === $is_search_filter_toggle && isset( $user_attribute_text ) ) {
							foreach ( $user_attribute_text as $key => $value ) {
								// Validate LDAP attribute name - only allow alphanumeric, underscore, and hyphen.
								if ( preg_match( '/^[a-zA-Z0-9_-]+$/', $value ) ) {
									$user_attribute[ $key ] = $value;
								}
							}
						}
						update_option( 'mo_ldap_username_attributes', maybe_serialize( $user_attribute ) );
						update_option( 'mo_ldap_extra_user_attribute', $extra_user_attribute );
						update_option( 'mo_ldap_cloud_filter_check', $is_search_filter_toggle );
					}
					if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
						update_option( 'mo_ldap_message', 'PHP openssl extension is not installed or disabled. Please enable it first.' );
						$this->utils->show_error_message();
					} else {
						update_option( 'mo_ldap_ldap_protocol', $ldap_protocol );
						update_option( 'mo_ldap_ldap_server_address', $this->utils::encrypt( $server_address ) );
						if ( 'ldap' === $ldap_protocol ) {
							update_option( 'mo_ldap_ldap_port_number', $port_number );
						} elseif ( 'ldaps' === $ldap_protocol ) {
							update_option( 'mo_ldap_ldaps_port_number', $port_number );
						}
						update_option( 'mo_ldap_cloud_directory_server_value', isset( $_POST['mo_ldap_cloud_directory_server_value'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_cloud_directory_server_value'] ) ) : '' );
						$directory_server_value = get_option( 'mo_ldap_cloud_directory_server_value' );
						if ( 'other' === sanitize_text_field( wp_unslash( $_POST['mo_ldap_cloud_directory_server_value'] ) ) && ! isset( $_POST['mo_ldap_cloud_directory_server_custom_value'] ) ) {
							update_option( 'mo_ldap_cloud_directory_server_custom_value', 'other' );
						} else {
							update_option( 'mo_ldap_cloud_directory_server_custom_value', sanitize_text_field( wp_unslash( $_POST['mo_ldap_cloud_directory_server_custom_value'] ) ) );
						}
						if ( 'msad' === $directory_server_value ) {
							$directory_server = 'Microsoft Active Directory';
						} elseif ( 'openldap' === $directory_server_value ) {
							$directory_server = 'OpenLDAP';
						} elseif ( 'freeipa' === $directory_server_value ) {
							$directory_server = 'FreeIPA';
						} elseif ( 'jumpcloud' === $directory_server_value ) {
							$directory_server = 'JumpCloud';
						} elseif ( 'other' === $directory_server_value ) {
							$directory_server = get_option( 'mo_ldap_cloud_directory_server_custom_value' );
						} else {
							$directory_server = 'Not Configured';
						}
						update_option( 'mo_ldap_cloud_directory_server', $directory_server );
						update_option( 'mo_ldap_server_url', $this->utils::encrypt( $server_name ) );
						update_option( 'mo_ldap_server_dn', $this->utils::encrypt( $dn ) );
						update_option( 'mo_ldap_server_password', $this->utils::encrypt( $admin_ldap_password ) );
						update_option( 'mo_ldap_search_base', $this->utils::encrypt( $search_base ) );
						update_option( 'mo_ldap_search_filter', $this->utils::encrypt( $search_filter ) );
						$mo_ldap_config = new Mo_LDAP_Cloud_Configuration_Handler();
						$save_content   = $mo_ldap_config->save_ldap_config();
						$save_response  = json_decode( $save_content, true );
						$message        = '';
						$status         = 'error';
						if ( strcasecmp( $save_response['statusCode'], 'SUCCESS' ) === 0 ) {
							$message = $message . 'Your configuration has been saved.';
							$status  = 'success';
						} elseif ( strcasecmp( $save_response['statusCode'], 'ERROR' ) === 0 ) {
							$message = $message . esc_html( $save_response['statusMessage'] );
						} else {
							$message = $message . 'There was an error in saving your configuration.';
						}
						$content  = $mo_ldap_config->test_connection();
						$response = json_decode( $content, true );
						if ( strcasecmp( $response['statusCode'], 'SUCCESS' ) === 0 ) {
							if ( 'success' === $status ) {
								update_option( 'mo_ldap_save_config_success', true );
								$mo_ldap_license_status = get_option( 'mo_ldap_license_status' );
								if ( '-1' === $mo_ldap_license_status ) {
									update_option( 'mo_ldap_message', wp_kses_post( $message ) . ' Connection was established successfully.' );
								} else {
									update_option( 'mo_ldap_message', wp_kses_post( $message ) . ' Connection was established successfully. Please proceed for Test Authentication to verify LDAP user authentication.' );
								}
								$this->utils->show_success_message();
							} else {
								update_option( 'mo_ldap_message', wp_kses_post( $message ) . ' Connection was established successfully.' );
								$this->utils->show_error_message();
							}
						} elseif ( strcasecmp( $response['statusCode'], 'ERROR' ) === 0 ) {
							update_option( 'mo_ldap_message', $message . ' ' . esc_html( $response['statusMessage'] ) . ' Please make sure to open your firewall to allow incoming requests to your LDAP from hosts - 52.55.147.107 and open port 389 (636 for SSL or ldaps). Test using Ping LDAP server.' );
							$this->utils->show_error_message();
						} elseif ( strcasecmp( $response['statusCode'], 'FAILED' ) === 0 ) {
							$ldap_protocol_value = get_option( 'mo_ldap_ldap_protocol' );
							if ( 'ldaps' === $ldap_protocol_value ) {
								$error_statement = 'Please make sure to open your firewall to allow incoming requests to your LDAP from hosts - 52.55.147.107 and open port 389 (636 for SSL or ldaps). If you are using ldaps connection then click <a href=" ' . add_query_arg( array( 'tab' => 'troubleshooting' ), isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ) . '#help_ldaps_desc_row">here</a> for more info.';
								update_option( 'mo_ldap_message', $message . ' ' . esc_html( $response['statusMessage'] ) . ' ' . $error_statement );
							} else {
								update_option( 'mo_ldap_message', $message . ' ' . esc_html( $response['statusMessage'] ) . ' Please make sure to open your firewall to allow incoming requests to your LDAP from hosts - 52.55.147.107 and open port 389 (636 for SSL or ldaps).' );
							}
							$this->utils->show_error_message();
						} else {
							update_option( 'mo_ldap_message', $message . ' There was an error in connecting with the current settings. Please make sure to open your firewall to allow incoming requests to your LDAP from hosts - 52.55.147.107 and open port 389 (636 for SSL or ldaps). Test using Ping LDAP server.' );
							$this->utils->show_error_message();
						}
					}
				} elseif ( 'mo_ldap_cloud_remove_security_notice' === $post_option && check_admin_referer( 'mo_ldap_cloud_remove_security_notice_nonce' ) ) {
					update_option( 'mo_ldap_cloud_remove_security_notice', 1 );
				} elseif ( 'mo_ldap_test_auth' === $post_option && check_admin_referer( 'mo_ldap_cloud_test_auth_nonce' ) ) {
					$server_name         = get_option( 'mo_ldap_server_url' );
					$dn                  = get_option( 'mo_ldap_server_dn' );
					$admin_ldap_password = get_option( 'mo_ldap_server_password' );
					$search_base         = get_option( 'mo_ldap_search_base' );
					$search_filter       = get_option( 'mo_ldap_search_filter' );
					$test_username       = '';
					$test_password       = '';
					if ( empty( sanitize_text_field( wp_unslash( $_POST['test_username'] ) ) ) || empty( $_POST['test_password'] ) ) {
						update_option( 'mo_ldap_message', 'All the fields are required. Please enter valid entries.' );
						$this->utils->show_error_message();
						return;
					} else {
						$test_username = sanitize_text_field( wp_unslash( $_POST['test_username'] ) );
						$test_password = $_POST['test_password']; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Should not be sanitised as Strong Passwords contains special characters
					}
					$mo_ldap_config = new Mo_LDAP_Cloud_Configuration_Handler();
					$content        = $mo_ldap_config->test_authentication( $test_username, $test_password );
					$response       = json_decode( $content, true );
					if ( strcasecmp( $response['statusCode'], 'DENIED' ) === 0 ) {
						update_option( 'mo_ldap_license_status', -1 );
						$licensing_plans_url = add_query_arg( array( 'tab' => 'pricing' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
						$message             = 'Please upgrade to the <a href="' . $licensing_plans_url . '">premium plan</a> to continue using this service.';
						update_option( 'mo_ldap_message', $message, 'no' );
						$this->utils->show_error_message();
					} elseif ( strcasecmp( $response['statusCode'], 'SUCCESS' ) === 0 ) {
						$role_mapping_url = add_query_arg( array( 'subtab' => 'rolemapping' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
						$message          = 'You have successfully configured your LDAP settings.<br>
								You can now do either of two things.<br>
								1. Enable LDAP Login at the top and then <a href="' . wp_logout_url( get_permalink() ) . '">Logout</a> from WordPress and login again with your LDAP credentials.<br> 
								2. Do role mapping (<a href="' . $role_mapping_url . '">Click here</a>).';
						update_option( 'mo_ldap_message', $message, 'no' );
						$this->utils->mo_ldap_cloud_auth_report_update( $test_username, 'SUCCESS', 'Test Authentication Successfull.' );
						$this->utils->show_success_message();
					} elseif ( strcasecmp( $response['statusCode'], 'ERROR' ) === 0 ) {
						$message = 'Invalid username or password. Please verify the Search Base(s) and Search filter. Your user should be present in the Search Base defined.';
						update_option( 'mo_ldap_message', $message, 'no' );
						$this->utils->mo_ldap_cloud_auth_report_update( $test_username, 'ERROR', $message );
						$this->utils->show_error_message();
					} elseif ( strcasecmp( $response['statusCode'], 'WP_ERROR' ) === 0 ) {
						update_option( 'mo_ldap_message', esc_html( $response['statusMessage'] ), 'no' );
						$this->utils->mo_ldap_cloud_auth_report_update( $test_username, 'WP_ERROR', esc_html( $response['statusMessage'] ) );
						$this->utils->show_error_message();
					} elseif ( strcasecmp( $response['statusCode'], 'OPENSSL_ERROR' ) === 0 ) {
						update_option( 'mo_ldap_message', esc_html( $response['statusMessage'] ) );
						$this->utils->mo_ldap_cloud_auth_report_update( $test_username, 'OPENSSL_ERROR', esc_html( $response['statusMessage'] ) );
						$this->utils->show_error_message();
					} else {
						$error_message = 'There was an error processing your request. Please verify the Search Base(s) and Search filter. Your user should be present in the Search Base defined.';
						update_option( 'mo_ldap_message', $error_message );
						$this->utils->mo_ldap_cloud_auth_report_update( $test_username, 'ERROR', $error_message );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_cloud_ldap_login_send_query' === $post_option && check_admin_referer( 'mo_ldap_cloud_login_send_query_nonce' ) ) {
					$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
					$email = isset( $_POST['query_email'] ) ? sanitize_email( wp_unslash( $_POST['query_email'] ) ) : '';
					$phone = isset( $_POST['query_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['query_phone'] ) ) : '';
					if ( empty( $email ) || empty( $query ) || ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						update_option( 'mo_ldap_message', 'Please submit your query along with valid email.' );
						$this->utils->show_error_message();
						return;
					}
					if ( isset( $_POST['mo_ldap_cloud_setup_call'] ) && 'on' === $_POST['mo_ldap_cloud_setup_call'] ) {
						$time_zone        = isset( $_POST['ldap_setup_call_timezone_cloud'] ) ? sanitize_text_field( wp_unslash( $_POST['ldap_setup_call_timezone_cloud'] ) ) : '';
						$call_date        = isset( $_POST['mo_ldap_setup_call_date_cloud'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_setup_call_date_cloud'] ) ) : '';
						$call_time        = isset( $_POST['mo_ldap_setup_call_time_cloud'] ) ? gmdate( 'g:i A', strtotime( sanitize_text_field( wp_unslash( $_POST['mo_ldap_setup_call_time_cloud'] ) ) ) ) : '';
						$query            = 'Query: ' . $query . '<br><br> Time-Zone: ' . $time_zone . '<br> <br>Date: ' . $call_date . '<br> <br>Time: ' . $call_time . '<br><br>Current Version Installed : ' . MO_LDAP_CLOUD_VERSION . ' <br>';
						$feedback_reasons = new MO_LDAP_Cloud_Customer_Setup_Handler();
						$subject          = 'WordPress LDAP Cloud Request For Setup Call - ';
						$submited         = json_decode( $feedback_reasons->send_email_alert( $email, $phone, $subject, $query ), true );
						if ( is_array( $submited ) && array_key_exists( 'status', $submited ) && strcasecmp( $submited['status'], 'ERROR' ) === 0 ) {
							update_option( 'mo_ldap_message', esc_html( $submited['message'] ) );
							$this->utils->show_error_message();
						} elseif ( ! $submited ) {
							update_option( 'mo_ldap_message', 'Error while submitting the request.' );
							$this->utils->show_error_message();
						} elseif ( strcasecmp( $submited['status'], 'SUCCESS' ) === 0 ) {
							update_option( 'mo_ldap_message', 'Your Request for call has been successfully sent. An executive from the miniOrange Team will soon reach out to you.' );
							$this->utils->show_success_message();
						} else {
							update_option( 'mo_ldap_message', 'Unexpected Error Occurred. Contact us at info@xecurify.com' );
							$this->utils->show_error_message();
						}

						return;
					}
					$contact_us = new MO_LDAP_Cloud_Customer_Setup_Handler();
					$query      = $query . '<br><br>[Current Version Installed] : ' . MO_LDAP_CLOUD_VERSION;
					$submited   = json_decode( $contact_us->submit_contact_us( $email, $phone, $query ), true );
					if ( isset( $submited['status'] ) && strcasecmp( $submited['status'], 'ERROR' ) === 0 ) {
						update_option( 'mo_ldap_message', 'There was an error in sending query. Please send us an email on <a href=mailto:info@xecurify.com><b>info@xecurify.com</b></a>.' );
						$this->utils->show_error_message();
					} else {
						update_option( 'mo_ldap_message', 'Your query successfully sent.<br>In case we dont get back to you, there might be email delivery failures. You can send us email on <a href=mailto:info@xecurify.com><b>info@xecurify.com</b></a> in that case.' );
						$this->utils->show_success_message();
					}
				} elseif ( 'mo_cloud_ldap_extend_trial_send_query' === $post_option && check_admin_referer( 'mo_ldap_cloud_extend_trial_send_query_nonce' ) ) {
					$query = 'Query: I want to extend my trial period';
					$email = isset( $_POST['extend_trial_query_email'] ) ? sanitize_email( wp_unslash( $_POST['extend_trial_query_email'] ) ) : '';
					if ( empty( $email ) || ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						update_option( 'mo_ldap_message', 'Please enter a valid email.' );
						$this->utils->show_error_message();
						return;
					} else {
						$default_admin_email = get_option( 'mo_ldap_admin_email' );
						$query               = $query . '<br><br>Registered email : ' . $default_admin_email;
						$phone               = isset( $_POST['extend_trial_query_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['extend_trial_query_phone'] ) ) : '';
						$feedback_reasons    = new MO_LDAP_Cloud_Customer_Setup_Handler();
						$current_version     = get_option( 'mo_ldap_current_plugin_version' );
						$query               = $query . '<br><br>[Current Version Installed] : ' . $current_version;
						$subject             = 'WordPress LDAP Cloud Extend Trial Request - ';
						$submited            = json_decode( $feedback_reasons->send_email_alert( $email, $phone, $subject, $query ), true );
						if ( ! $submited ) {
							update_option( 'mo_ldap_message', 'There was an error in sending query. Please send us an email on <a href=mailto:info@xecurify.com><b>info@xecurify.com</b></a>.' );
							$this->utils->show_error_message();
						} else {
							update_option( 'mo_ldap_message', 'Your query successfully sent.<br>In case we dont get back to you, there might be email delivery failures. You can send us email on <a href=mailto:info@xecurify.com><b>info@xecurify.com</b></a> in that case.' );
							$this->utils->show_success_message();
						}
					}
				} elseif ( 'mo_ldap_enable_both_login' === $post_option && check_admin_referer( 'mo_ldap_cloud_enable_both_login_nonce' ) ) {
					update_option( 'mo_ldap_enable_both_login', isset( $_POST['mo_ldap_enable_both_login'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_enable_both_login'] ) ) : 0 );
					$login_options = get_option( 'mo_ldap_enable_both_login' );
					if ( 'all' === $login_options ) {
						update_option( 'mo_ldap_message', 'Login with both LDAP and WordPress has been enabled.' );
						$this->utils->show_success_message();
					} elseif ( 'admin' === $login_options ) {
						update_option( 'mo_ldap_message', 'Login with LDAP and WordPress Administrator has been enabled.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'Login only with LDAP has been enabled. Please make sure you have an LDAP user with administrator permissions in WordPress.' );
						$this->utils->show_success_message();
					}
				} elseif ( 'mo_ldap_resend_otp' === $post_option && check_admin_referer( 'mo_ldap_cloud_resend_otp_nonce' ) ) {
					$customer = new MO_LDAP_Cloud_Customer_Setup_Handler();
					$content  = json_decode( $customer->send_otp_token(), true );
					if ( strcasecmp( $content['status'], 'SUCCESS' ) === 0 ) {
						update_option( 'mo_ldap_message', ' A one time passcode is sent to ' . get_option( 'mo_ldap_admin_email' ) . ' again. Please enter the OTP recieved.' );
						update_option( 'mo_ldap_transactionId', $content['txId'] );
						update_option( 'mo_ldap_registration_status', 'MO_OTP_DELIVERED_SUCCESS' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'There was an error in sending email. Please click on Resend OTP to try again.' );
						update_option( 'mo_ldap_registration_status', 'MO_OTP_DELIVERED_FAILURE' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_enable_role_mapping' === $post_option && check_admin_referer( 'mo_ldap_cloud_enable_role_mapping_nonce' ) ) {
					update_option( 'mo_ldap_enable_role_mapping', isset( $_POST['enable_ldap_role_mapping'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_ldap_role_mapping'] ) ) : '0' );
					if ( ! empty( get_option( 'mo_ldap_enable_role_mapping' ) ) && '1' === get_option( 'mo_ldap_enable_role_mapping' ) ) {
						update_option( 'mo_ldap_message', 'LDAP Group to WP role mapping has been enabled.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'LDAP Group to WP role mapping has been disabled.' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_keep_existing_user_roles' === $post_option && check_admin_referer( 'mo_ldap_cloud_keep_existing_user_roles_nonce' ) ) {
					update_option( 'mo_ldap_keep_existing_user_roles', isset( $_POST['keep_existing_user_roles'] ) ? sanitize_text_field( wp_unslash( $_POST['keep_existing_user_roles'] ) ) : '0' );
					if ( ! empty( get_option( 'mo_ldap_keep_existing_user_roles' ) ) && '1' === get_option( 'mo_ldap_keep_existing_user_roles' ) ) {
						update_option( 'mo_ldap_message', 'WP Roles of Existing users will not be removed.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'WP Roles of Existing users will be updated.' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_save_role_mapping' === $post_option && check_admin_referer( 'mo_ldap_cloud_save_role_mapping_nonce' ) ) {
					$added_mappings_count = 0;
					$i                    = 1;
					$mapping_count        = get_option( 'mo_ldap_role_mapping_count' );
					while ( isset( $_POST[ 'mapping_key_' . $i ] ) ) {
						if ( ! empty( $_POST[ 'mapping_key_' . $i ] ) ) {
							++$added_mappings_count;
							update_option( 'mo_ldap_mapping_key_' . $added_mappings_count, trim( sanitize_text_field( wp_unslash( $_POST[ 'mapping_key_' . $i ] ) ) ) );
							update_option( 'mo_ldap_mapping_value_' . $added_mappings_count, isset( $_POST[ 'mapping_value_' . $i ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'mapping_value_' . $i ] ) ) : '' );
						}
						++$i;
					}
					if ( $added_mappings_count < $mapping_count ) {
						$i = $added_mappings_count + 1;
						while ( $i <= $mapping_count ) {
							delete_option( 'mo_ldap_mapping_key_' . $i );
							delete_option( 'mo_ldap_mapping_value_' . $i );
							++$i;
						}
					}
					update_option( 'mo_ldap_role_mapping_count', $added_mappings_count );
					if ( isset( $_POST['mapping_value_default'] ) ) {
						update_option( 'mo_ldap_mapping_value_default', sanitize_text_field( wp_unslash( $_POST['mapping_value_default'] ) ) );
					}
					if ( isset( $_POST['mapping_memberof_attribute'] ) ) {
						update_option( 'mo_ldap_mapping_memberof_attribute', sanitize_text_field( wp_unslash( $_POST['mapping_memberof_attribute'] ) ) );
					}
					$status_message = '';
					if ( empty( get_option( 'mo_ldap_enable_role_mapping' ) ) || '1' !== get_option( 'mo_ldap_enable_role_mapping' ) ) {
						$status_message = ' Please check <b>"Enable Role Mapping"</b> to activate it.';
					}
					update_option( 'mo_ldap_message', 'LDAP Group to WP role mapping has been updated.' . $status_message );
					$this->utils->show_success_message();
				} elseif ( 'mo_ldap_delete_group_to_role' === $post_option && check_admin_referer( 'mo_ldap_cloud_delete_group_to_role_nonce' ) ) {
					$group_to_role_id = isset( $_POST['delete_group_to_role_id'] ) ? sanitize_text_field( wp_unslash( $_POST['delete_group_to_role_id'] ) ) : '';
					$group_option     = 'mo_ldap_mapping_key_' . $group_to_role_id;
					$role_option      = 'mo_ldap_mapping_value_' . $group_to_role_id;
					delete_option( $group_option );
					delete_option( $role_option );
					$current_mapping_count = get_option( 'mo_ldap_role_mapping_count' );
					if ( $group_to_role_id < $current_mapping_count ) {
						for ( $i = $group_to_role_id + 1; $i <= $current_mapping_count; $i++ ) {
							$old_key   = get_option( 'mo_ldap_mapping_key_' . $i );
							$old_value = get_option( 'mo_ldap_mapping_value_' . $i );
							update_option( 'mo_ldap_mapping_key_' . ( $i - 1 ), $old_key );
							update_option( 'mo_ldap_mapping_value_' . ( $i - 1 ), $old_value );
						}
					}
					delete_option( 'mo_ldap_mapping_key_' . $current_mapping_count );
					delete_option( 'mo_ldap_mapping_value_' . $current_mapping_count );
					update_option( 'mo_ldap_role_mapping_count', $current_mapping_count - 1 );
				} elseif ( 'mo_ldap_cloud_save_restrict_login_by_role' === $post_option && check_admin_referer( 'mo_ldap_save_restrict_login_by_role_nonce' ) ) {
					$enable_role_restriction = isset( $_POST['mo_ldap_cloud_local_restrict_user_by_role'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_cloud_local_restrict_user_by_role'] ) ) : '0';
					update_option( 'mo_ldap_cloud_enable_role_restriction', $enable_role_restriction );
					$restricted_roles = empty( $_POST['mo_ldap_cloud_restrict_role'] ) ? array() : array_map( 'sanitize_text_field', wp_unslash( $_POST['mo_ldap_cloud_restrict_role'] ) );
					update_option( 'mo_ldap_cloud_restricted_login_roles', maybe_serialize( $restricted_roles ) );
					update_option( 'mo_ldap_message', 'configuration Save Successfully' );
					$this->utils->show_success_message();
				} elseif ( 'mo_ldap_cloud_save_attribute_config' === $post_option && check_admin_referer( 'mo_ldap_cloud_save_attribute_config_nonce' ) ) {
					$custom_attributes = array();
					foreach ( $_POST as $key => $value ) {
						if ( strpos( $key, 'mo_ldap_custom_attribute' ) !== false ) {
							array_push( $custom_attributes, $key );
						}
					}
					$email_attribute        = isset( $_POST['mo_ldap_email_attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_email_attribute'] ) ) : '';
					$phone_attribute        = isset( $_POST['mo_ldap_phone_attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_phone_attribute'] ) ) : '';
					$fname_attribute        = isset( $_POST['mo_ldap_fname_attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_fname_attribute'] ) ) : '';
					$lname_attribute        = isset( $_POST['mo_ldap_lname_attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_lname_attribute'] ) ) : '';
					$nickname_attribute     = isset( $_POST['mo_ldap_nickname_attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_nickname_attribute'] ) ) : '';
					$display_name_attribute = isset( $_POST['mo_ldap_display_name_attribute'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_display_name_attribute'] ) ) : '';
					update_option( 'mo_ldap_email_attribute', $email_attribute );
					update_option( 'mo_ldap_phone_attribute', $phone_attribute );
					update_option( 'mo_ldap_fname_attribute', $fname_attribute );
					update_option( 'mo_ldap_lname_attribute', $lname_attribute );
					update_option( 'mo_ldap_nickname_attribute', $nickname_attribute );
					update_option( 'mo_ldap_display_name_attribute', $display_name_attribute );
					foreach ( $custom_attributes as $attribute ) {
						if ( isset( $_POST[ $attribute ] ) && ! empty( sanitize_text_field( wp_unslash( $_POST[ $attribute ] ) ) ) ) {
							if ( empty( get_option( $attribute ) ) ) {
								$attribute_key = 'mo_ldap_custom_attribute_' . strtolower( sanitize_text_field( wp_unslash( $_POST[ $attribute ] ) ) );
							} else {
								$attribute_key = strtolower( sanitize_text_field( wp_unslash( $_POST[ $attribute ] ) ) );
							}
							update_option( str_replace( ' ', '', $attribute_key ), str_replace( ' ', '', strtolower( sanitize_text_field( wp_unslash( $_POST[ $attribute ] ) ) ) ) );
						}
					}
					update_option( 'mo_ldap_message', 'Successfully saved LDAP Attribute Configuration.' );
					$this->utils->show_success_message();
				} elseif ( 'mo_ldap_cloud_enable_attr_mapping' === $post_option && check_admin_referer( 'mo_ldap_cloud_enable_attr_mapping_nonce' ) ) {
					$enable_attribute_mapping = isset( $_POST['enable_attr_mapping'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_attr_mapping'] ) ) : 0;
					update_option( 'mo_ldap_enable_attribute_mapping', $enable_attribute_mapping );
					if ( '1' === $enable_attribute_mapping ) {
						update_option( 'mo_ldap_message', 'LDAP Attribute Mapping Enabled' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'LDAP Attribute Mapping Disabled' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_cloud_delete_custom_attribute' === $post_option && check_admin_referer( 'mo_ldap_cloud_delete_custom_attribute_nonce' ) ) {
					$custom_attribute_name = isset( $_POST['custom_attribute_name'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_attribute_name'] ) ) : '';
					$custom_attribute_key  = 'mo_ldap_custom_attribute_' . $custom_attribute_name;
					delete_option( $custom_attribute_key );
					update_option( 'mo_ldap_message', 'Successfully deleted custom attribute: <b>' . esc_html( $custom_attribute_name ) . '</b>' );
					$this->utils->show_success_message();
				} elseif ( 'mo_ldap_enable_debugger' === $post_option && check_admin_referer( 'mo_ldap_enable_debugger_nonce' ) ) {
					$enable_debugger = isset( $_POST['enable_debugger'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_debugger'] ) ) : '0';
					update_option( 'mo_ldap_enable_debugger', $enable_debugger );
					if ( '1' === $enable_debugger ) {
						update_option( 'mo_ldap_message', 'Debugger mode has been enabled. Use the new "Debugger" tab to access it.' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'Debugger mode has been disabled.' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_cloud_check_license' === $post_option && check_admin_referer( 'mo_ldap_check_license_nonce' ) ) {
					$this->mo_ldap_handle_mo_check_ln( true );
				} elseif ( 'mo_ldap_cancel' === $post_option && check_admin_referer( 'mo_ldap_cloud_cancel_nonce' ) ) {
					update_option( 'mo_ldap_registration_status', '' );
					update_option( 'mo_ldap_verify_customer', '' );
					delete_option( 'mo_ldap_new_registration' );
					delete_option( 'mo_ldap_admin_email' );
					delete_option( 'mo_ldap_admin_phone' );
				} elseif ( 'mo_ldap_cloud_change_miniorange_account_option' === $post_option && check_admin_referer( 'mo_ldap_cloud_change_miniorange_account_nonce' ) ) {
					delete_option( 'mo_ldap_admin_customer_key' );
					delete_option( 'mo_ldap_admin_api_key' );
					delete_option( 'mo_ldap_message' );
					delete_option( 'mo_ldap_cust' );
					delete_option( 'mo_ldap_verify_customer' );
					delete_option( 'mo_ldap_new_registration' );
					delete_option( 'mo_ldap_registration_status' );
					delete_option( 'mo_ldap_license_status' );
					delete_option( 'mo_ldap_cloud_license_info' );
					delete_option( 'mo_ldap_username_attributes' );
				} elseif ( 'mo_ldap_cloud_goto_login' === $post_option && check_admin_referer( 'mo_ldap_cloud_goto_login_nonce' ) ) {
					delete_option( 'mo_ldap_new_registration' );
					update_option( 'mo_ldap_verify_customer', 'true' );
				} elseif ( 'mo_ldap_skip_feedback_cloud' === $post_option && check_admin_referer( 'mo_ldap_cloud_skip_feedback_cloud_nonce' ) ) {
					deactivate_plugins( MO_LDAP_CLOUD_PLUGIN_NAME );
					update_option( 'mo_ldap_message', 'Plugin deactivated successfully' );
					$this->utils->show_success_message();
				} elseif ( 'mo_ldap_cloud_pass' === $post_option && check_admin_referer( 'mo_ldap_cloud_pass_nonce' ) ) {
					$export_service_password = isset( $_POST['enable_export_pass'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_export_pass'] ) ) : '0';
					update_option( 'mo_ldap_cloud_export_pass', $export_service_password );
					if ( '1' === $export_service_password ) {
						update_option( 'mo_ldap_message', 'Service account password will be exported in encrypted fashion' );
						$this->utils->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'Service account password will not be exported.' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_cloud_export' === $post_option && check_admin_referer( 'mo_ldap_cloud_export_nonce' ) ) {
					if ( ! empty( get_option( 'mo_ldap_server_url' ) ) ) {
						$this->miniorange_ldap_cloud_export();
					} else {
						update_option( 'mo_ldap_message', 'LDAP Configuration not set. Please configure LDAP connection settings.' );
						$this->utils->show_error_message();
					}
				} elseif ( 'mo_ldap_cloud_import' === $post_option && check_admin_referer( 'mo_ldap_cloud_import_nonce' ) ) {
					if ( $this->utils::is_customer_registered() ) {
						$file_name = sanitize_file_name( isset( $_FILES['mo_ldap_cloud_import_file']['name'] ) ? wp_unslash( $_FILES['mo_ldap_cloud_import_file']['name'] ) : '' );
						$file_size = sanitize_text_field( isset( $_FILES['mo_ldap_cloud_import_file']['size'] ) ? wp_unslash( $_FILES['mo_ldap_cloud_import_file']['size'] ) : '' );
						$file_tmp  = sanitize_text_field( isset( $_FILES['mo_ldap_cloud_import_file']['tmp_name'] ) ? $_FILES['mo_ldap_cloud_import_file']['tmp_name'] : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- File Path cannot be unslashed
						$file_type = sanitize_text_field( isset( $_FILES['mo_ldap_cloud_import_file']['type'] ) ? wp_unslash( $_FILES['mo_ldap_cloud_import_file']['type'] ) : '' );
						$file_ext  = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
						if ( 'json' === $file_ext ) {
							$mime_type = '';
							if ( function_exists( 'finfo_open' ) && ! empty( $file_tmp ) ) {
								$finfo = finfo_open( FILEINFO_MIME_TYPE );
								if ( $finfo ) {
									$mime_type = finfo_file( $finfo, $file_tmp );
									finfo_close( $finfo );
								}
							}

							$max_size = 2 * 1024 * 1024;
							if ( ! empty( $file_tmp ) && $file_size > $max_size ) {
								update_option( 'mo_ldap_message', 'Incorrect file uploaded! Please upload the file that was exported from this plugin' );
								$this->utils->show_error_message();
								return;
							}

							if ( 'application/json' === $mime_type ) {
								$file_json_content   = file_get_contents( $file_tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Using file_get_contents to read uploaded local file.
								$configuration_array = json_decode( $file_json_content, true );
								if ( ! is_array( $configuration_array ) && JSON_ERROR_NONE !== json_last_error() ) {
									update_option( 'mo_ldap_message', 'Incorrect file uploaded! Please upload the file that was exported from this plugin' );
									$this->utils->show_error_message();
									return;
								}

								if ( array_key_exists( 'ldap_Login', $configuration_array ) || array_key_exists( 'ldap_config', $configuration_array ) || array_key_exists( 'Role_Mapping', $configuration_array ) || array_key_exists( 'Attribute_Mapping', $configuration_array ) ) {
									$tab_class_name = $this->utils::secure_unserialize_option( TAB_LDAP_CLOUD_CLASS_NAMES );
									foreach ( $configuration_array as $class_key => $class_array ) {
										$class_object  = call_user_func( $tab_class_name[ $class_key ] . '::get_constants' );
										$mapping_count = 0;
										foreach ( $class_object as $key => $option ) {
											$key = strtolower( $key );
											if ( array_key_exists( $key, $class_array ) ) {
												if ( 'mapping_count' === $key ) {
													$mapping_count = $class_array[ $key ];
													update_option( $option, $mapping_count );
												} elseif ( 'role_mapping_key' === $key ) {
													for ( $i = 1; $i <= $mapping_count; $i++ ) {
														$value = sanitize_text_field( $class_array[ $key ][ $i ] );
														update_option( $option . $i, $value );
													}
												} elseif ( 'role_mapped_value' === $key ) {
													for ( $i = 1; $i <= $mapping_count; $i++ ) {
														$value = sanitize_text_field( $class_array[ $key ][ $i ] );
														update_option( $option . $i, $value );
													}
												} elseif ( 'custom_attribute_name' === $key ) {
													$custom_attribute_count = count( $class_array[ $key ] );
													for ( $i = 1; $i <= $custom_attribute_count; $i++ ) {
														$value = sanitize_text_field( $class_array[ $key ][ $i ] );
														update_option( 'mo_ldap_custom_attribute_' . $value, $value );
													}
												} elseif ( 'restricted_roles' === $key || 'user_attr' === $key ) {
													$sanitized_array = is_array( $class_array[ $key ] ) ? array_map( 'sanitize_text_field', $class_array[ $key ] ) : array();
													update_option( $option, maybe_serialize( $sanitized_array ) );
												} else {
													$value = sanitize_text_field( $class_array[ $key ] );
													if ( 'mo_ldap_server_url' === $option || 'mo_ldap_server_dn' === $option || 'mo_ldap_search_base' === $option || 'mo_ldap_search_filter' === $option || 'mo_ldap_username_attributes' === $option ) {
														if ( 'mo_ldap_username_attributes' === $option ) {
															$value = maybe_serialize( $value );
														} else {
															$value = $this->utils::encrypt( $value );
														}
													}
													update_option( $option, $value );
												}
											}
										}
									}
									$user_attribute    = ! empty( get_option( 'mo_ldap_username_attributes' ) ) ? $this->utils::secure_unserialize_option( get_option( 'mo_ldap_username_attributes' ) ) : '';
									$is_checked_toggle = ( ( empty( $user_attribute ) && empty( get_option( 'mo_ldap_search_filter' ) ) ) || ( ! empty( get_option( 'mo_ldap_cloud_filter_check' ) ) && 'disabled' === get_option( 'mo_ldap_cloud_filter_check' ) ) ) ? false : true;

									if ( 1 !== $is_checked_toggle ) {
										$ldap_search_filter    = $this->utils::decrypt( get_option( 'mo_ldap_search_filter' ) );
										$updated_search_filter = preg_replace( '/\(objectClass=\*\)/', '(|(objectClass=person)(objectClass=user))', $ldap_search_filter );
										update_option( 'mo_ldap_search_filter', $this->utils::encrypt( $updated_search_filter ) );
									}

									update_option( 'mo_ldap_message', 'Configuration was successfully imported.' );
									$this->utils->show_success_message();
								}
							} else {
								update_option( 'mo_ldap_message', 'Incorrect file uploaded! Please upload the file that was exported from this plugin' );
								$this->utils->show_error_message();
							}
						} else {
							update_option( 'mo_ldap_message', 'Incorrect file uploaded! Please upload the file that was exported from this plugin' );
							$this->utils->show_error_message();
						}
					}
				} elseif ( 'mo_ldap_feedback_cloud' === $post_option && check_admin_referer( 'mo_ldap_cloud_feedback_cloud_nonce' ) ) {
					$user                      = wp_get_current_user();
					$message                   = 'Query: Plugin Deactivated:';
					$deactivate_reason_message = array_key_exists( 'query_feedback_cloud', $_POST ) ? sanitize_text_field( wp_unslash( $_POST['query_feedback_cloud'] ) ) : false;
					$reply_required            = '';
					if ( isset( $_POST['get_reply_cloud'] ) ) {
						$reply_required = sanitize_text_field( wp_unslash( $_POST['get_reply_cloud'] ) );
					}
					if ( empty( $reply_required ) ) {
						$reply_required = 'NO';
						$message       .= '<b class="mo_ldap_cloud_required_attr";> &nbsp;[Follow up Needed : ' . $reply_required . ']</b>';
					} else {
						$reply_required = 'YES';
						$message       .= '<b style="color:green";> &nbsp;[Follow up Needed : ' . $reply_required . ']</b>';
					}
					if ( ! empty( $deactivate_reason_message ) ) {
						$message .= '<br>Feedback : ' . esc_html( $deactivate_reason_message ) . '<br/>';
					}
					if ( isset( $_POST['rate_cloud'] ) ) {
						$rate_value = sanitize_text_field( wp_unslash( $_POST['rate_cloud'] ) );
						$message   .= '<br>[Rating : ' . esc_html( $rate_value ) . ']<br>';
					}
					$message .= '<br><br>[Current Version Installed : ' . MO_LDAP_CLOUD_VERSION . ']<br>';
					$email    = isset( $_POST['query_mail_cloud'] ) ? sanitize_email( wp_unslash( $_POST['query_mail_cloud'] ) ) : '';
					if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
						$email = get_option( 'mo_ldap_admin_email' );
						if ( empty( $email ) ) {
							$email = $user->user_email;
						}
					}
					$phone            = get_option( 'mo_ldap_admin_phone' );
					$subject          = 'WordPress LDAP Cloud Plugin Feedback - ';
					$feedback_reasons = new MO_LDAP_Cloud_Customer_Setup_Handler();
					if ( ! is_null( $feedback_reasons ) ) {
						$submited = json_decode( $feedback_reasons->send_email_alert( $email, $phone, $subject, $message ), true );
						if ( json_last_error() === JSON_ERROR_NONE ) {
							if ( is_array( $submited ) && array_key_exists( 'status', $submited ) && strcasecmp( $submited['status'], 'ERROR' ) === 0 ) {
									update_option( 'mo_ldap_message', esc_html( $submited['message'] ) );
									$this->utils->show_error_message();
							} elseif ( ! $submited ) {
									update_option( 'mo_ldap_message', 'Error while submitting the query.' );
									$this->utils->show_error_message();
							}
						}
						deactivate_plugins( MO_LDAP_CLOUD_PLUGIN_NAME );
						update_option( 'mo_ldap_message', 'Thank you for the feedback.' );
						$this->utils->show_success_message();
						wp_safe_redirect( 'plugins.php' );
					}
				} elseif ( 'mo_ldap_cloud_public_pages' === $post_option && check_admin_referer( 'mo_ldap_cloud_public_pages_nonce' ) ) {
					$is_toggle_checked  = isset( $_POST['mo_ldap_cloud_public_pages_check'] ) ? sanitize_text_field( wp_unslash( $_POST['mo_ldap_cloud_public_pages_check'] ) ) : '';
					$count              = 1;
					$public_pages_array = ! empty( get_option( 'mo_ldap_cloud_public_pages_list' ) ) ? $this->utils::secure_unserialize_option( get_option( 'mo_ldap_cloud_public_pages_list' ) ) : array();
					update_option( 'mo_ldap_cloud_public_pages_enable', $is_toggle_checked );
					$message = '<strong>Saved Configuration</strong><br>';
					while ( isset( $_POST[ 'mo_ldap_cloud_public_custom_page_' . $count ] ) ) {
						$current_public_page = esc_url_raw( wp_unslash( $_POST[ 'mo_ldap_cloud_public_custom_page_' . $count ] ) );
						if ( ! empty( $current_public_page ) ) {
							$public_page_name = rtrim( strtolower( $current_public_page ), '/' );
							if ( ! in_array( $public_page_name, $public_pages_array, true ) ) {
								array_push( $public_pages_array, $public_page_name );
								$message .= 'Page <strong>' . esc_html( $public_page_name ) . '</strong> Added Successfully.<br>';
							} else {
								$message .= 'Page <strong>' . esc_html( $public_page_name ) . '</strong> Already exists.<br>';
							}
						}
						++$count;
					}
					update_option( 'mo_ldap_message', $message );
					$this->utils->show_success_message();
					update_option( 'mo_ldap_cloud_public_pages_list', maybe_serialize( $public_pages_array ) );
				} elseif ( 'mo_ldap_cloud_delete_page' === $post_option && check_admin_referer( 'mo_ldap_cloud_delete_page_nonce' ) ) {
					$public_page_name   = isset( $_POST['mo_ldap_cloud_delete_page_name'] ) ? esc_url_raw( wp_unslash( $_POST['mo_ldap_cloud_delete_page_name'] ) ) : '';
					$public_pages_array = ! empty( get_option( 'mo_ldap_cloud_public_pages_list' ) ) ? $this->utils::secure_unserialize_option( get_option( 'mo_ldap_cloud_public_pages_list' ) ) : array();
					$key                = array_search( $public_page_name, $public_pages_array, true );
					unset( $public_pages_array[ $key ] );
					update_option( 'mo_ldap_cloud_public_pages_list', maybe_serialize( $public_pages_array ) );
					$message = 'Page <strong>' . esc_html( $public_page_name ) . '</strong> Removed Successfully';
					update_option( 'mo_ldap_message', $message );
					$this->utils->show_success_message();
				}
			}
		}

		/**
		 * Function mo_ldap_cloud_schedule_log_clearing_cron : schedule cron for clearing logs
		 *
		 * @param  string $interval Cron interval slug (e.g. 'weekly', 'monthly', 'yearly').
		 * @return void
		 */
		public function mo_ldap_cloud_schedule_log_clearing_cron( $interval ) {
			if ( wp_next_scheduled( 'mo_ldap_cloud_logs_cleanup_event' ) ) {
				$this->mo_ldap_cloud_unschedule_log_clearing_cron();
			}
			if ( ! wp_next_scheduled( 'mo_ldap_cloud_logs_cleanup_event' ) ) {
				wp_schedule_event( time() + $this->get_interval_in_seconds( $interval ), $interval, 'mo_ldap_cloud_logs_cleanup_event' );
			}
		}

		/**
		 * Function mo_ldap_cloud_unschedule_log_clearing_cron : unschedule cron for clearing logs
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_unschedule_log_clearing_cron() {
			$timestamp = wp_next_scheduled( 'mo_ldap_cloud_logs_cleanup_event' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'mo_ldap_cloud_logs_cleanup_event' );
			}
		}

		/**
		 * Function get_interval_in_seconds : convert interval in second
		 *
		 * @param  string $interval Cron interval slug (e.g. 'weekly', 'monthly', 'yearly').
		 * @return string
		 */
		public function get_interval_in_seconds( $interval ) {
			switch ( $interval ) {
				case 'weekly':
					return 604800;
				case 'monthly':
					return 2635200;
				case 'yearly':
					return 31556926;
				default:
					return 0;
			}
		}

		/**
		 * Function mo_ldap_cloud_test_configuration : Test attribute mapping.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_test_configuration() {
			if ( isset( $_REQUEST['option'] ) && 'testcloudrolemappingconfig' === sanitize_text_field( wp_unslash( $_REQUEST['option'] ) ) && isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'mo_ldap_cloud_test_role_mapping_nonce' ) && current_user_can( 'manage_options' ) ) {
				$username             = isset( $_REQUEST['user'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user'] ) ) : '';
				$mo_ldap_role_mapping = new Mo_Ldap_Cloud_Role_Mapping_Handler();
				$mo_ldap_role_mapping->test_configuration( $username );
			}
		}
	}
}
