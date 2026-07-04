<?php
/**
 * This file contains main class of the plugin.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 */

namespace MO_LDAP_CLOUD;

require_once 'utils' . DIRECTORY_SEPARATOR . 'class-mo-ldap-cloud-utils.php';
require_once 'utils' . DIRECTORY_SEPARATOR . 'class-mo-ldap-cloud-addon-list-content.php';
require_once 'utils' . DIRECTORY_SEPARATOR . 'class-mo-ldap-cloud-data-store.php';
require_once 'handlers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-cloud-save-options-handler.php';
require_once 'handlers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-cloud-configuration-handler.php';
require_once 'handlers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-cloud-customer-setup-handler.php';
require_once 'handlers' . DIRECTORY_SEPARATOR . 'class-mo-ldap-cloud-login-handler.php';

use MO_LDAP_CLOUD\Utils\Mo_LDAP_Cloud_Utils;
use MO_LDAP_CLOUD\Utils\MO_LDAP_Cloud_Addon_List_Content;
use MO_LDAP_CLOUD\Utils\MO_LDAP_Cloud_Data_Store;

use MO_LDAP_CLOUD\Handlers\Mo_LDAP_Cloud_Save_Options_Handler;
use MO_LDAP_CLOUD\Handlers\Mo_LDAP_Cloud_Configuration_Handler;
use MO_LDAP\Handlers\Mo_Ldap_Cloud_Login_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Mo_LDAP_Cloud_Login' ) ) {
	/**
	 * Mo_LDAP_Cloud_Login : This is the main class of the plugin.
	 */
	final class Mo_LDAP_Cloud_Login {

		/**
		 * Utility object.
		 *
		 * @var [object]
		 */
		private $util;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->util = new Mo_LDAP_Cloud_Utils();
			$this->mo_ldap_cloud_initialize_hooks();
			$this->mo_ldap_cloud_initialize_handlers();
			$this->mo_ldap_cloud_add_action_links();

			$version_in_db = get_option( 'mo_ldap_current_plugin_version' );
			if ( version_compare( $version_in_db, MO_LDAP_CLOUD_VERSION ) !== 0 ) {
				if ( version_compare( $version_in_db, '6.2.0', '<=' ) ) {
					$user_attribute    = ! empty( get_option( 'mo_ldap_username_attributes' ) ) ? $this->util::secure_unserialize_option( get_option( 'mo_ldap_username_attributes' ) ) : '';
					$is_checked_toggle = ( ( empty( $user_attribute ) && empty( get_option( 'mo_ldap_search_filter' ) ) ) || ( ! empty( get_option( 'mo_ldap_cloud_filter_check' ) ) && 'disabled' === get_option( 'mo_ldap_cloud_filter_check' ) ) ) ? false : true;

					if ( 1 !== $is_checked_toggle ) {
						$ldap_search_filter    = $this->util::decrypt( get_option( 'mo_ldap_search_filter' ) );
						$updated_search_filter = preg_replace( '/\(objectClass=\*\)/', '(|(objectClass=person)(objectClass=user))', $ldap_search_filter );
						update_option( 'mo_ldap_search_filter', $this->util::encrypt( $updated_search_filter ) );
						$mo_ldap_config = new Mo_LDAP_Cloud_Configuration_Handler();
						if ( ! function_exists( 'wp_get_current_user' ) ) {
							include_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-includes/pluggable.php';

						}
						if ( function_exists( 'wp_get_current_user' ) ) {
							$mo_ldap_config->save_ldap_config();
						}
					}
				}
				if ( ! empty( $version_in_db ) ) {
					$status = $this->util::upgrade_plugin();
					if ( ! $status ) {
						update_option( 'mo_ldap_cloud_upgrade_is_error', 'true' );
					} else {
						update_option( 'mo_ldap_cloud_upgrade_is_error', 'false' );
					}
				}
				update_option( 'mo_ldap_current_plugin_version', MO_LDAP_CLOUD_VERSION );
			}
		}

		/**
		 * Function mo_ldap_cloud_add_action_links : Adds action links for the plugin in the plugins section of WordPress.
		 *
		 * @return void
		 */
		private function mo_ldap_cloud_add_action_links() {
			add_filter( 'plugin_action_links_' . MO_LDAP_CLOUD_PLUGIN_NAME, array( $this, 'mo_ldap_cloud_links' ) );
		}

		/**
		 * Function mo_ldap_cloud_initialize_handlers : Initializes handlers required.
		 *
		 * @return void
		 */
		private function mo_ldap_cloud_initialize_handlers() {
			$save_options = new Mo_LDAP_Cloud_Save_Options_Handler();
			$ldap_config  = new Mo_LDAP_Cloud_Configuration_Handler();
			if ( ! empty( get_option( 'mo_ldap_enable_login' ) ) && '1' === get_option( 'mo_ldap_enable_login' ) ) {
				$login_handler = new Mo_Ldap_Cloud_Login_Handler();
			}
		}

		/**
		 * Function mo_ldap_cloud_initialize_hooks : Initializes hooks.
		 *
		 * @return void
		 */
		private function mo_ldap_cloud_initialize_hooks() {
			add_action( 'admin_menu', array( $this, 'mo_ldap_cloud_login_widget_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'mo_ldap_cloud_settings_style' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'mo_ldap_cloud_settings_script' ) );
			register_deactivation_hook( MO_LDAP_CLOUD_PLUGIN_NAME, array( $this, 'mo_ldap_cloud_deactivate' ) );
			add_action( 'admin_footer', array( $this, 'mo_ldap_cloud_feedback_request' ) );

			if ( ! empty( get_option( 'mo_ldap_authorized_users_only' ) ) && '1' === get_option( 'mo_ldap_authorized_users_only' ) ) {
				add_action( 'wp', array( $this, 'mo_ldap_cloud_login_redirect' ) );
			}

			$active_plugins_array = ! empty( get_option( 'active_plugins' ) ) ? get_option( 'active_plugins' ) : array();
			$active_plugins       = array_map( 'strtolower', $active_plugins_array );
			if ( in_array( 'miniorange-page-link-restriction/add_page_links_restriction.php', $active_plugins, true ) ) {
				add_action( 'wp', array( $this, 'mo_ldap_cloud_page_link_restriction' ) );
			}

			register_activation_hook( MO_LDAP_CLOUD_PLUGIN_NAME, array( $this, 'mo_ldap_cloud_activate' ) );
		}

		/**
		 * Function mo_ldap_cloud_activate : Handles the flow at the time of activation.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_activate() {
			update_option( 'mo_ldap_register_user', '1' );
			update_option( 'mo_ldap_enable_both_login', 'all' );
		}

		/**
		 * Function mo_ldap_login_redirect : Login restriction.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_login_redirect() {
			$current_page_id     = get_queried_object_id();
			$current_page_url    = rtrim( get_permalink( $current_page_id ), '/' );
			$public_pages_array  = ! empty( get_option( 'mo_ldap_cloud_public_pages_list' ) ) ? $this->util::secure_unserialize_option( get_option( 'mo_ldap_cloud_public_pages_list' ) ) : array();
			$enable_public_pages = ! empty( get_option( 'mo_ldap_cloud_public_pages_enable' ) ) ? get_option( 'mo_ldap_cloud_public_pages_enable' ) : '0';
			if ( ! ( '1' === $enable_public_pages && in_array( strtolower( $current_page_url ), $public_pages_array, true ) ) ) {
				if ( ! is_user_logged_in() ) {
					auth_redirect();
				}
			}
		}

		/**
		 * Function mo_ldap_cloud_feedback_request : Displays feedback form.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_feedback_request() {
			require_once MO_LDAP_CLOUD_DIR . 'views/mo-ldap-cloud-display-feedback.php';
		}

		/**
		 * Function mo_ldap_cloud_deactivate : Handles the flow at the time of deactivation.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_deactivate() {
			if ( ! empty( get_option( 'mo_ldap_registration_status' ) ) ) {
				delete_option( 'mo_ldap_admin_email' );
			}

			delete_option( 'mo_ldap_new_registration' );
			delete_option( 'mo_ldap_admin_phone' );
			delete_option( 'mo_ldap_verify_customer' );
			delete_option( 'mo_ldap_admin_customer_key' );
			delete_option( 'mo_ldap_admin_api_key' );
			delete_option( 'mo_ldap_customer_token' );
			delete_option( 'mo_ldap_message' );
			delete_option( 'mo_ldap_enable_login' );
			delete_option( 'mo_ldap_redirect_to' );
			delete_option( 'mo_ldap_custom_redirect' );
			delete_option( 'mo_ldap_skip_redirectto_parameter' );
			delete_option( 'mo_ldap_authorized_users_only' );
			delete_option( 'mo_ldap_server_password' );
			delete_option( 'mo_ldap_transactionId' );
			delete_option( 'mo_ldap_registration_status' );
			delete_option( 'mo_ldap_save_config_success' );
			delete_option( 'mo_ldap_license_status' );
			delete_option( 'mo_ldap_enable_both_login' );
			delete_option( 'mo_ldap_cloud_license_info' );
			wp_safe_redirect( 'plugins.php' );
		}

		/**
		 * Function mo_ldap_cloud_login_widget_menu : Adds menu page.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_login_widget_menu() {
			add_menu_page(
				'LDAP/AD Login Cloud & Shared Hosting',
				'LDAP/AD Login for Cloud & Shared Hosting',
				'activate_plugins',
				'mo_ldap_cloud_login',
				array( $this, 'mo_ldap_cloud_login_widget_options' ),
				MO_LDAP_CLOUD_IMAGES . 'miniorange_icon.webp'
			);
		}

		/**
		 * Function mo_ldap_cloud_links : Add plugin action link.
		 *
		 * @param  array $links : String containing link.
		 * @return array
		 */
		public function mo_ldap_cloud_links( $links ) {
			$links = array_merge(
				array(
					'<a href="' . esc_url( admin_url( 'admin.php?page=mo_ldap_cloud_login' ) ) . '">' . __( 'Settings', 'miniorange-wp-ldap-login' ) . '</a>',
				),
				$links
			);
			return $links;
		}

		/**
		 * Function mo_ldap_cloud_page_link_restriction : Checks for the page restriction.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_page_link_restriction() {
			$enable_pg_link = get_option( 'mo_enable_page_link_restriction' );
			if ( $enable_pg_link ) {
				$pg_link_added        = get_option( 'mo_page_link_list' );
				$pg_link_data         = array();
				$page_link_restricted = get_option( 'mo_page_link_restriction' );
				$page_link_count      = count( $pg_link_added );
				for ( $i = 1;$i <= $page_link_count;$i++ ) {
					$pg_link_data[ $page_link_restricted[ $i ][ 'mo_ldap_page_link_' . $i ] ] = $page_link_restricted[ $i ][ 'row_' . $i . '_selected_role' ];
				}
				$url_requested = get_permalink();
				if ( in_array( $url_requested, $pg_link_added, true ) ) {
					global $current_user;
					$user_roles = $current_user->roles;
					if ( empty( array_intersect( $user_roles, $pg_link_data[ $url_requested ] ) ) ) {
						$previous_url = wp_get_referer();
						if ( ! empty( $previous_url ) ) {
							wp_die(
								'<p>Oops! You are not authorized to access this URL</p><br>
                        <table><tr>
                        <td><a href="' . esc_url( $previous_url ) . "\">Go Back to Previous Page</a></td>
                        <td style='width: 50%'></td>
                        <td><a href=\"" . esc_url( site_url() ) . '">Go back to Home Page</a></td></tr></table>'
							);
						} else {
							wp_die(
								'<p>Oops! You are not authorized to access this URL</p><br>
                        <table><tr>
                        <td><a href="' . esc_url( site_url() ) . '">Go back to Home Page</a></td></tr></table>'
							);
						}
					}
				}
			}
		}

		/**
		 * Function mo_ldap_cloud_login_widget_options : Initilizes the main UI of the plugin.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_login_widget_options() {
			$utils       = $this->util;
			$addons      = new MO_LDAP_Cloud_Addon_List_Content();
			$timezones   = new MO_LDAP_Cloud_Data_Store();
			$ldap_config = new Mo_LDAP_Cloud_Configuration_Handler();
			require_once MO_LDAP_CLOUD_DIR . 'controllers/mo-ldap-cloud-main-controller.php';
		}

		/**
		 * Function mo_ldap_cloud_settings_style : Enqueue required styles.
		 *
		 * @param string $page : current page.
		 * @return void
		 */
		public function mo_ldap_cloud_settings_style( $page ) {
			if ( strcasecmp( $page, 'toplevel_page_mo_ldap_cloud_login' ) !== 0 ) {
				return;
			}

			wp_enqueue_style( 'mo_ldap_cloud_admin_settings_style', MO_LDAP_CLOUD_INCLUDES . 'css/mo_ldap_cloud_style_settings.min.css', array(), MO_LDAP_CLOUD_VERSION );
			wp_enqueue_style( 'mo_ldap_cloud_admin_settings_phone_style', MO_LDAP_CLOUD_INCLUDES . 'css/phone.min.css', array(), MO_LDAP_CLOUD_VERSION );
			wp_enqueue_style( 'mo_ldap_cloud_admin_datatable_style', MO_LDAP_CLOUD_INCLUDES . 'css/mo_ldap_cloud_datatable.min.css', array(), MO_LDAP_CLOUD_VERSION );
		}

		/**
		 * Function mo_ldap_cloud_settings_script : Enqueue required scripts.
		 *
		 * @return void
		 */
		public function mo_ldap_cloud_settings_script() {
			if ( isset( $_GET['page'] ) && strcasecmp( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'mo_ldap_cloud_login' ) === 0 ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- fetching GET parameter for changing table layout.
				wp_enqueue_script( 'mo_ldap_cloud_admin_auth_report_script', MO_LDAP_CLOUD_INCLUDES . 'js/mo-ldap-cloud-auth-reports.min.js', array( 'jquery' ), MO_LDAP_CLOUD_VERSION, false );
				wp_enqueue_script( 'mo_ldap_cloud_admin_settings_phone_script', MO_LDAP_CLOUD_INCLUDES . 'js/phone.min.js', array(), MO_LDAP_CLOUD_VERSION, false );
				wp_enqueue_script( 'mo_ldap_cloud_admin_settings_script', MO_LDAP_CLOUD_INCLUDES . 'js/settings_page.min.js', array( 'jquery' ), MO_LDAP_CLOUD_VERSION, false );
				wp_enqueue_script( 'mo_ldap_cloud_admin_datatable_script', MO_LDAP_CLOUD_INCLUDES . 'js/mo_ldap_cloud_datatable.min.js', array(), MO_LDAP_CLOUD_VERSION, false );
			}
		}
	}
}
