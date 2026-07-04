<?php
/**
 * This class contains class that handles the login functionality.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Handlers
 */

namespace MO_LDAP\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-mo-ldap-cloud-role-mapping-handler.php';

use WP_Error;
use MO_LDAP_CLOUD\Utils\Mo_Ldap_Cloud_Utils;
use MO_LDAP_CLOUD\Handlers\Mo_LDAP_Cloud_Configuration_Handler;
use MO_LDAP_CLOUD\Handlers\Mo_Ldap_Cloud_Role_Mapping_Handler;


if ( ! class_exists( 'Mo_Ldap_Cloud_Login_Handler' ) ) {
	/**
	 * Mo_Ldap_Cloud_Login_Handler : Class for the login handler functions.
	 */
	class Mo_Ldap_Cloud_Login_Handler {


		/**
		 * Utility object.
		 *
		 * @var [object]
		 */
		private $utils;

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {
			$this->utils = new Mo_Ldap_Cloud_Utils();

			remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
			remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );
			add_filter( 'authenticate', array( $this, 'mo_ldap_cloud_login' ), 7, 3 );
			add_filter( 'login_redirect', array( $this, 'mo_ldap_cloud_custom_redirect' ), 10, 3 );
			add_action( 'show_user_profile', array( $this, 'mo_ldap_cloud_show_user_profile' ) );
			add_action( 'edit_user_profile', array( $this, 'mo_ldap_cloud_show_user_profile' ) );
		}

		/**
		 * Function mo_ldap_cloud_show_user_profile : Displays user profile page.
		 *
		 * @param  mixed $user : user object.
		 * @return void
		 */
		public function mo_ldap_cloud_show_user_profile( $user ) {
			$wp_options = wp_load_alloptions();

			?>
			<h3>Extra profile information</h3>
			<table aria-hidden="true" class="form-table">
				<?php
				if ( get_the_author_meta( 'mo_ldap_cloud_user_phone', $user->ID ) ) {
					?>
					<tr>
						<td><b><label for="user_phone">User Phone Number</label></b></td>

						<td>
							<b><?php echo esc_attr( get_the_author_meta( 'mo_ldap_cloud_user_phone', $user->ID ) ); ?></b>
						</td>
					</tr>
					<?php
				}
				foreach ( $wp_options as $option => $value ) {
					if ( strpos( $option, 'mo_ldap_custom_attribute' ) !== false ) {
						$custom = get_user_meta( $user->ID, $option, true );
						if ( ! empty( $custom ) ) {
							?>
							<tr>
								<td><strong><?php echo esc_html( $value ); ?></strong></td>
								<?php
								if ( is_array( $custom ) ) {
									echo '<td>';
									foreach ( $custom as $metaval ) {
										echo '<b>' . esc_html( $metaval ) . '</b><br>';
									}
									echo '</td>';
								} else {
									echo '<td>';
									echo '<b>' . esc_html( $custom ) . '</b><br>';
									echo '</td>';
								}
								?>
							</tr>
							<?php
						}
					}
				}
				?>
			</table>

			<?php
		}

		/**
		 * Function mo_ldap_cloud_custom_redirect : Custom redirection after login.
		 *
		 * @param  mixed $redirect_to : Redirect Link.
		 * @param  mixed $request : Request Obj.
		 * @param  mixed $user : User Obj.
		 * @return string
		 */
		public function mo_ldap_cloud_custom_redirect( $redirect_to, $request, $user ) {
			$ldap_redirect_to = ! empty( get_option( 'mo_ldap_redirect_to' ) ) ? get_option( 'mo_ldap_redirect_to' ) : 'none';
			if ( isset( $user->roles ) && is_array( $user->roles ) ) {
				if ( in_array( 'administrator', $user->roles, true ) ) {
					return $redirect_to;
				} elseif ( 'none' !== $ldap_redirect_to ) {
					if ( 'homepage' === $ldap_redirect_to ) {
						$redirect_to = home_url();
					} elseif ( 'profile' === $ldap_redirect_to ) {
						$profile_url = home_url() . '/wp-admin/profile.php';
						$redirect_to = $profile_url;
					} elseif ( 'custom' === $ldap_redirect_to ) {
						$redirect_to = ! empty( get_option( 'mo_ldap_custom_redirect' ) ) ? get_option( 'mo_ldap_custom_redirect' ) : $redirect_to;
					}
				}
			}
			return $redirect_to;
		}

		/**
		 * Function add_login_messages
		 *
		 * @param  string $message : Message to display on the login screen.
		 * @return string
		 */
		public function add_login_messages( $message ) {
			$custom_login_message = ! empty( get_option( 'mo_ldap_login_messages' ) ) ? get_option( 'mo_ldap_login_messages' ) : array();
			foreach ( $custom_login_message as $msg_arr ) {
				$message = $message . "<p name='custom-login-message' id='custom-login-message' style='margin-top: 20px;margin-left: 0;padding: 8px 8px 8px;font-weight: 200;overflow: hidden;background: #fff;border: 4px solid #fff;box-shadow: 0 1px 3px rgba(0,0,0,.04);border-left-color: " . esc_attr( $msg_arr['color'] ) . ";border-top: none;border-bottom: none;'><strong>" . esc_html( $msg_arr['message'] ) . '</strong></p>';
			}
			$message = $message . '<br>';
			return $message;
		}
		/**
		 * Function update_user_attributes : Updates user attributes.
		 *
		 * @param  object $profile_attributes Object containing profile attributes.
		 * @param  int    $userid User id.
		 * @return void
		 */
		private function update_user_attributes( $profile_attributes, $userid ) {
			$email_attribute         = ! empty( get_option( 'mo_ldap_email_attribute' ) ) ? get_option( 'mo_ldap_email_attribute' ) : '';
			$phone_attribute         = ! empty( get_option( 'mo_ldap_phone_attribute' ) ) ? get_option( 'mo_ldap_phone_attribute' ) : '';
			$fname_attribute         = ! empty( get_option( 'mo_ldap_fname_attribute' ) ) ? get_option( 'mo_ldap_fname_attribute' ) : '';
			$lname_attribute         = ! empty( get_option( 'mo_ldap_lname_attribute' ) ) ? get_option( 'mo_ldap_lname_attribute' ) : '';
			$ldap_nickname_attribute = ! empty( get_option( 'mo_ldap_nickname_attribute' ) ) ? get_option( 'mo_ldap_nickname_attribute' ) : '';
			$display_name_attribute  = ! empty( get_option( 'mo_ldap_display_name_attribute' ) ) ? get_option( 'mo_ldap_display_name_attribute' ) : '';
			if ( isset( $profile_attributes->$fname_attribute ) && is_array( $profile_attributes->$fname_attribute ) ) {
				$first_name = $profile_attributes->$fname_attribute[0];
			} else {
				$first_name = isset( $profile_attributes->$fname_attribute ) ? $profile_attributes->$fname_attribute : '';
			}
			if ( isset( $profile_attributes->$lname_attribute ) && is_array( $profile_attributes->$lname_attribute ) ) {
				$last_name = $profile_attributes->$lname_attribute[0];
			} else {
				$last_name = isset( $profile_attributes->$lname_attribute ) ? $profile_attributes->$lname_attribute : '';
			}
			if ( isset( $profile_attributes->$email_attribute ) && is_array( $profile_attributes->$email_attribute ) ) {
				$email = $profile_attributes->$email_attribute[0];
			} else {
				$email = isset( $profile_attributes->$email_attribute ) ? $profile_attributes->$email_attribute : '';
			}
			if ( isset( $profile_attributes->$phone_attribute ) && is_array( $profile_attributes->$phone_attribute ) ) {
				$phone = $profile_attributes->$phone_attribute[0];
			} else {
				$phone = isset( $profile_attributes->$phone_attribute ) ? $profile_attributes->$phone_attribute : '';
			}
			if ( isset( $profile_attributes->$ldap_nickname_attribute ) && is_array( $profile_attributes->$ldap_nickname_attribute ) ) {
				$nickname = $profile_attributes->$ldap_nickname_attribute[0];
			} else {
				$nickname = isset( $profile_attributes->$ldap_nickname_attribute ) ? $profile_attributes->$ldap_nickname_attribute : '';
			}
			if ( ! empty( $display_name_attribute ) ) {
				if ( 'nickname' === $display_name_attribute ) {
					$display_name = $nickname;
				} elseif ( 'email' === $display_name_attribute ) {
					$display_name = $email;
				} elseif ( 'firstname' === $display_name_attribute ) {
					$display_name = $first_name;
				} elseif ( 'firstlast' === $display_name_attribute ) {
					$display_name = $first_name . ' ' . $last_name;
				} elseif ( 'lastfirst' === $display_name_attribute ) {
					$display_name = $last_name . ' ' . $first_name;
				}
			}
			$user_data['ID'] = $userid;
			if ( ! empty( $email ) ) {
				$user_data['user_email'] = $email;
			}
			if ( ! empty( $first_name ) ) {
				$user_data['first_name'] = $first_name;
			}
			if ( ! empty( $last_name ) ) {
				$user_data['last_name'] = $last_name;
			}
			if ( ! empty( $display_name_attribute ) ) {
				$user_data['display_name'] = $display_name;
			}
			wp_update_user( $user_data );

			if ( ! empty( $phone ) ) {
				update_user_meta( $userid, 'mo_ldap_cloud_user_phone', $phone );
			}

			$wp_options = wp_load_alloptions();
			foreach ( $wp_options as $option => $value ) {
				if ( strpos( $option, 'mo_ldap_custom_attribute' ) !== false ) {
					if ( isset( $profile_attributes->$value ) && is_array( $profile_attributes->$value ) && ! empty( $profile_attributes->$value ) ) {
						$avalue = $profile_attributes->$value[0];
					} else {
						$avalue = isset( $profile_attributes->$value ) ? $profile_attributes->$value : '';
					}
					update_user_meta( $userid, $option, $avalue );
				}
			}
		}

		/**
		 * Function mo_ldap_user_role_based_restriction : Return error if user restricted to access
		 *
		 * @param  array $assigned_roles : User Roles.
		 * @return mixed
		 */
		private function mo_ldap_user_role_based_restriction( $assigned_roles ) {
			if ( ! is_array( $assigned_roles ) ) {
				$assigned_roles = array();
			}
			$mo_ldap_role_mapping     = new Mo_Ldap_Cloud_Role_Mapping_Handler();
			$is_restricted            = $mo_ldap_role_mapping->mo_ldap_is_user_login_restricted( $assigned_roles );
			$enable_login_restriction = get_option( 'mo_ldap_cloud_enable_role_restriction' );
			if ( $is_restricted && ( '1' === $enable_login_restriction ) ) {
				$error = new WP_Error();
				$error->add( 'registration_disabled_error', __( '<strong>Login Error : </strong>You are not allowed to Login. Please contact your administrator', 'miniorange-wp-ldap-login' ) );
				return $error;
			}
		}


		/**
		 * Function mo_ldap_cloud_login : Handles the flow upon user login.
		 *
		 * @param  mixed  $user : User obj.
		 * @param  string $username : LDAP username.
		 * @param  string $password : LDAP pwd.
		 * @return object
		 */
		public function mo_ldap_cloud_login( $user, $username, $password ) {
			$active_plugins_array = ! empty( get_option( 'active_plugins' ) ) ? get_option( 'active_plugins' ) : array();
			$active_plugins       = array_map( 'strtolower', $active_plugins_array );
			if ( in_array( 'miniorange-customize-WP-login-screen/add_login_message.php', $active_plugins, true ) ) {
				add_filter( 'login_message', 'add_login_messages' );
			}
			if ( empty( $username ) || empty( $password ) ) {
				$error = new WP_Error();
				if ( empty( $username ) ) {
					$error->add( 'empty_username', __( '<strong>ERROR</strong>: Email field is empty.', 'miniorange-wp-ldap-login' ) );
				}
				if ( empty( $password ) ) {
					$error->add( 'empty_password', __( '<strong>ERROR</strong>: Password field is empty.', 'miniorange-wp-ldap-login' ) );
				}
				return $error;
			}
			$login_options = ! empty( get_option( 'mo_ldap_enable_both_login' ) ) ? get_option( 'mo_ldap_enable_both_login' ) : 'all';
			if ( 'none' !== $login_options && username_exists( $username ) ) {
				$user_existing = get_user_by( 'login', $username );
				$user_roles    = $user_existing->roles;
				if ( $user_existing && wp_check_password( $password, $user_existing->data->user_pass, $user_existing->ID ) ) {
					if ( 'admin' === $login_options && in_array( 'administrator', $user_roles, true ) ) {
						$this->utils->mo_ldap_cloud_auth_report_update( $username, 'SUCCESS', '<strong>SUCCESS</strong>: Authentication Successfull.' );
						return $user_existing;
					} elseif ( 'all' === $login_options || '1' === $login_options ) {
						$user_existing = get_user_by( 'login', $username );
						$error         = $this->mo_ldap_user_role_based_restriction( $user_existing->roles );
						if ( $error ) {
							return $error;
						}
						$this->utils->mo_ldap_cloud_auth_report_update( $username, 'SUCCESS', '<strong>SUCCESS</strong>: Authentication Successfull.' );
						return $user_existing;
					}
				}
			}
			$mo_ldap_config   = new Mo_LDAP_Cloud_Configuration_Handler();
			$decoded_response = $mo_ldap_config->ldap_login( $username, $password );
			$status           = $decoded_response['statusCode'];
			if ( 'SUCCESS' === $status ) {
				if ( username_exists( $username ) || email_exists( $username ) ) {
					$user_existing = false;
					if ( username_exists( $username ) ) {
						$user_existing = get_user_by( 'login', $username );
					} elseif ( email_exists( $username ) ) {
						$user_existing = get_user_by( 'email', $username );
					}
					if ( ! empty( get_option( 'mo_ldap_enable_role_mapping' ) ) && '1' === get_option( 'mo_ldap_enable_role_mapping' ) ) {
						$new_registered_user  = false;
						$mo_ldap_role_mapping = new Mo_Ldap_Cloud_Role_Mapping_Handler();
						$member_of_attr_array = $mo_ldap_role_mapping->get_member_of_attribute( $username );
						$mo_ldap_role_mapping->mo_ldap_update_role_mapping( $user_existing->ID, $member_of_attr_array, $new_registered_user );
					}
					if ( isset( $decoded_response['profileAttributes'] ) && get_option( 'mo_ldap_enable_attribute_mapping' ) ) {
						$this->update_user_attributes( $decoded_response['profileAttributes'], $user_existing->ID );
					}
					do_action( 'mo_ldap_cloud_buddypress', $decoded_response['profileAttributes'], $user_existing );
					if ( username_exists( $username ) ) {
						$user_existing = get_user_by( 'login', $username );
					} elseif ( email_exists( $username ) ) {
						$user_existing = get_user_by( 'email', $username );
					}
					$user_roles = isset( $user_existing->roles ) && is_array( $user_existing->roles ) ? $user_existing->roles : array();
					$error      = $this->mo_ldap_user_role_based_restriction( $user_roles );
					if ( $error ) {
						return $error;
					}
					$this->utils->mo_ldap_cloud_auth_report_update( $username, 'SUCCESS', '<strong>SUCCESS</strong>: Authentication Successfull.' );
					return $user_existing;
				} elseif ( empty( get_option( 'mo_ldap_register_user' ) ) || '1' !== get_option( 'mo_ldap_register_user' ) ) {
						$error = new WP_Error();
						$this->utils->mo_ldap_cloud_auth_report_update( $username, 'ERROR', '<strong>Login Error:</strong> Your Administrator has not enabled Auto Registration. Please contact your Administrator.' );
						$error->add( 'registration_disabled_error', __( '<strong>Login Error:</strong> Your Administrator has not enabled Auto Registration. Please contact your Administrator.', 'miniorange-wp-ldap-login' ) );
						return $error;
				} else {
						$user_email      = ( $decoded_response['profileAttributes']->mail )[0];
						$random_password = wp_generate_password( 10, false );
						$userdata        = array(
							'user_login' => $username,
							'user_email' => $user_email,
							'user_pass'  => $random_password,
						);
						$user_id         = wp_insert_user( $userdata );
						if ( ! is_wp_error( $user_id ) ) {
							$new_user = get_user_by( 'login', $username );
							if ( ! empty( get_option( 'mo_ldap_enable_role_mapping' ) ) && '1' === get_option( 'mo_ldap_enable_role_mapping' ) ) {
								$new_registered_user  = true;
								$mo_ldap_role_mapping = new Mo_Ldap_Cloud_Role_Mapping_Handler();
								$member_of_attr_array = $mo_ldap_role_mapping->get_member_of_attribute( $username );
								$mo_ldap_role_mapping->mo_ldap_update_role_mapping( $new_user->ID, $member_of_attr_array, $new_registered_user );
							}
							if ( isset( $decoded_response['profileAttributes'] ) && get_option( 'mo_ldap_enable_attribute_mapping' ) ) {
								$this->update_user_attributes( $decoded_response['profileAttributes'], $new_user->ID );
							}
							do_action( 'mo_ldap_cloud_buddypress', $decoded_response['profileAttributes'], $new_user );
							$new_user      = get_user_by( 'login', $username );
							$user_existing = get_user_by( 'login', $username );
							$error         = $this->mo_ldap_user_role_based_restriction( $user_existing->roles );
							if ( $error ) {
								return $error;
							}
							$this->utils->mo_ldap_cloud_auth_report_update( $username, 'SUCCESS', '<strong>SUCCESS</strong>: Authentication Successfull.' );
							return $new_user;
						} else {
							$error_string       = $user_id->get_error_message();
							$email_exists_error = 'Sorry, that email address is already used!';
							if ( email_exists( $user_email ) && strcasecmp( $error_string, $email_exists_error ) === 0 ) {
								$error = new WP_Error();
								$this->utils->mo_ldap_cloud_auth_report_update( $username, 'LOGIN_ERROR', '<strong>Login Error:</strong> There was an error registering your account. The email is already registered, please choose another one and try again.' );
								$error->add( 'registration_error', __( '<strong>Login Error:</strong> There was an error registering your account. The email is already registered, please choose another one and try again.', 'miniorange-wp-ldap-login' ) );
								return $error;
							} else {
								$this->utils->mo_ldap_cloud_auth_report_update( $username, 'ERROR', '<strong>ERROR</strong>: There was an error registering your account. Please try again.' );
								$error = new WP_Error();
								$error->add( 'registration_error', __( '<strong>ERROR</strong>: There was an error registering your account. Please try again.', 'miniorange-wp-ldap-login' ) );
								return $error;
							}
						}
				}
			} elseif ( 'DENIED' === $status ) {
				update_option( 'mo_ldap_license_status', -1 );
				$this->utils->mo_ldap_cloud_auth_report_update( $username, 'LICENSE_EXPIRED', '<strong>ERROR</strong>: Your are not authorized to login using LDAP credentials. Kindly use local WordPress password.' );
				$error = new WP_Error();
				$error->add( 'license_expired', __( '<strong>ERROR</strong>: Your are not authorized to login using LDAP credentials. Kindly use local WordPress password.', 'miniorange-wp-ldap-login' ) );
				return $error;
			} elseif ( 'OPENSSL_ERROR' === $status ) {
				$this->utils->mo_ldap_cloud_auth_report_update( $username, 'OPENSSL_ERROR', '<strong>ERROR</strong>: <a target="_blank" href="http://php.net/manual/en/openssl.installation.php">PHP OpenSSL extension</a> is not installed or disabled.' );
				$error = new WP_Error();
				$error->add( 'OPENSSL_ERROR', __( '<strong>ERROR</strong>: <a target="_blank" href="http://php.net/manual/en/openssl.installation.php">PHP OpenSSL extension</a> is not installed or disabled.', 'miniorange-wp-ldap-login' ) );
				return $error;
			} elseif ( 'MO_ERROR' === $status ) {
				$this->utils->mo_ldap_cloud_auth_report_update( $username, 'WP_ERROR', '<strong>ERROR</strong>: Cannot connect to miniOrange. Reach out to info@xecurify.com for more details' );
				$error = new WP_Error();
				$error->add( 'WP_ERROR', __( '<strong>ERROR</strong>: Cannot connect to miniOrange. Reach out to info@xecurify.com for more details', 'miniorange-wp-ldap-login' ) );
				return $error;
			} elseif ( 'FAILED' === $status ) {
				$this->utils->mo_ldap_cloud_auth_report_update( $username, 'WP_ERROR', '<strong>ERROR</strong>: Unexpected Error occurred.' );
				$error = new WP_Error();
				$error->add( 'WP_ERROR', __( '<strong>ERROR</strong>: Unexpected Error occurred.', 'miniorange-wp-ldap-login' ) );
				return $error;
			} else {
				$this->utils->mo_ldap_cloud_auth_report_update( $username, 'INVALID_CREDENTIALS', '<strong>ERROR</strong>: Invalid username or incorrect password. Please try again.' );
				$error = new WP_Error();
				$error->add( 'incorrect_credentials', __( '<strong>ERROR</strong>: Invalid username or incorrect password. Please try again.', 'miniorange-wp-ldap-login' ) );
				return $error;
			}
		}
	}
}
