<?php
/**
 * This file contains class to handle roles of WordPress users.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Handlers
 */

namespace MO_LDAP_CLOUD\Handlers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_User;
use MO_LDAP_CLOUD\Utils\Mo_LDAP_Cloud_Utils;

if ( ! class_exists( 'Mo_Ldap_Cloud_Role_Mapping_Handler' ) ) {
	/**
	 * Mo_Ldap_Cloud_Role_Mapping_Handler : Class for the role mapping handler functions.
	 */
	class Mo_Ldap_Cloud_Role_Mapping_Handler {
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
		 * Function get_member_of_attribute
		 *
		 * @param  mixed $username : LDAP username.
		 * @return array
		 */
		public function get_member_of_attribute( $username ) {
			$url = MO_LDAP_CLOUD_HOST_NAME . '/moas/api/ldap/getmemberof';

			$customer_id      = get_option( 'mo_ldap_admin_customer_key' ) ? get_option( 'mo_ldap_admin_customer_key' ) : null;
			$application_name = ! empty( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
			$member_of        = get_option( 'mo_ldap_mapping_memberof_attribute' );

			if ( ! $member_of ) {
				$member_of = 'memberof';
			}

			$data        = array(
				'customerId'       => $customer_id,
				'userName'         => $username,
				'memberOf'         => $member_of,
				'ldapAuditRequest' => array(
					'endUserEmail'    => $username,
					'applicationName' => $application_name,
					'appType'         => 'WP LDAP for Cloud',
					'requestType'     => 'Get Member Of Attribute',
				),
			);
			$data_string = wp_json_encode( $data );

			$args     = $this->utils::get_api_argument( $data_string );
			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return array();
			}
			$decoded_response = (array) json_decode( $response['body'] );
			if ( isset( $decoded_response['groups'] ) ) {
				return $decoded_response['groups'];
			}
			return array();
		}

		/**
		 * Function mo_ldap_update_role_mapping
		 *
		 * @param  mixed $user_id : WordPress userid.
		 * @param  mixed $member_of_attr_array : LDAP memberof attribute.
		 * @param  mixed $new_registered_user : WP registration status.
		 * @return void
		 */
		public function mo_ldap_update_role_mapping( $user_id, $member_of_attr_array, $new_registered_user ) {
			if ( 1 === $user_id ) {
				return;
			}

			$roles         = 0;
			$mapping_count = 0;
			if ( is_numeric( get_option( 'mo_ldap_role_mapping_count' ) ) ) {
				$mapping_count = intval( get_option( 'mo_ldap_role_mapping_count' ) );
			}
			$keep_existing_roles = ! empty( get_option( 'mo_ldap_keep_existing_user_roles' ) ) ? get_option( 'mo_ldap_keep_existing_user_roles' ) : '';
			$default_role        = ! empty( get_option( 'mo_ldap_mapping_value_default' ) ) ? get_option( 'mo_ldap_mapping_value_default' ) : '';

			$wpuser = new WP_User( $user_id );
			for ( $i = 1; $i <= $mapping_count; $i++ ) {
				$group   = get_option( 'mo_ldap_mapping_key_' . $i );
				$matches = preg_grep( '/' . $group . '/i', $member_of_attr_array );
				if ( count( $matches ) > 0 ) {
					$group_mapping = get_option( 'mo_ldap_mapping_value_' . $i );
					if ( $group_mapping ) {
						if ( strcasecmp( $keep_existing_roles, '1' ) === 0 ) {
							$wpuser->add_role( $group_mapping );
						} else {
							if ( 0 === $roles ) {
								$wpuser->set_role( '' );
							}
							$wpuser->add_role( $group_mapping );
						}
						++$roles;
					}
				}
			}

			if ( 0 === $roles ) {
				if ( ! empty( $default_role ) ) {
					if ( strcasecmp( $keep_existing_roles, '1' ) === 0 && ! $new_registered_user ) {
						$wpuser->add_role( $default_role );
					} else {
						$wpuser->set_role( $default_role );
					}
				}
			}
		}


		/**
		 * Function test_configuration
		 *
		 * @param  mixed $username : LDAP username.
		 * @return mixed
		 */
		public function test_configuration( $username ) {
			if ( ! $this->utils::is_extension_installed( 'openssl' ) ) {
				return null;
			}

			$groups = $this->get_member_of_attribute( $username );

			$mapping_count = 0;
			if ( is_numeric( get_option( 'mo_ldap_role_mapping_count' ) ) ) {
				$mapping_count = intval( get_option( 'mo_ldap_role_mapping_count' ) );
			}
			echo '<div style=padding:20px>';
			$flag           = 0;
			$assigned_roles = array();
			$roles_found    = false;
			for ( $i = 1; $i <= $mapping_count; $i++ ) {
				$group   = get_option( 'mo_ldap_mapping_key_' . $i );
				$matches = preg_grep( '/' . $group . '/i', $groups );
				if ( count( $matches ) > 0 ) {
					$group_mapping = get_option( 'mo_ldap_mapping_value_' . $i );
					if ( $group_mapping ) {
						if ( 0 === $flag ) {
							echo '<div style="color: #3c763d;background-color: #dff0d8; padding:2%;margin-bottom:20px;text-align:center; border:1px solid #AEDB9A; font-size:18pt;">TEST SUCCESSFUL</div>
							<div style="display:block;text-align:center;margin-bottom:4%;"><img style="width:15%;"src="' . esc_url( MO_LDAP_CLOUD_IMAGES . 'green_check.webp' ) . '"/></div>';
						}
						echo '<li>User <b>' . esc_html( $username ) . '</b> found in group <b>' . esc_html( $group ) . '</b> which matches role <b>' . esc_html( $group_mapping ) . '.</b></li>';
						++$flag;
						array_push( $assigned_roles, $group_mapping );
						$roles_found = true;

					}
				}
			}
			if ( ! $roles_found ) {
				$default_role = ! empty( get_option( 'mo_ldap_mapping_value_default' ) ) ? get_option( 'mo_ldap_mapping_value_default' ) : '';
				if ( ! empty( $default_role ) ) {
					$assigned_roles[] = $default_role;
				}
			}
			if ( 0 === $flag ) {
				echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">TEST FAILED</div>
                    <div style="display:block;text-align:center;margin-bottom:4%;"><img style="width:15%;"src="' . esc_url( MO_LDAP_CLOUD_IMAGES . 'wrong.webp' ) . '"/></div>
						<span>User <b>' . esc_html( $username ) . '</b> not found in any group specified in role mapping.</span><br>';

				if ( get_option( 'mo_ldap_mapping_value_default' ) ) {
					echo ' Default Role <b>' . esc_html( get_option( 'mo_ldap_mapping_value_default' ) ) . '</b> will be assigned to the User. ';
				}

				echo '<br><br>Please check : <li>If you have specified DN for group name e.g.<b>cn=group,dc=domain,dc=com</b></li>
								<li>If you have added users in groups specified in role mapping.</li>';
			} elseif ( 1 === $flag ) {
				echo '<br><br><li>Role <b>' . esc_html( $group_mapping ) . '</b> will be assigned to the User.</li>';
			} else {
				$assigned_roles = array_unique( $assigned_roles );
				echo '</br><li>Following roles will be assigned to the User:</li>';
				echo '<ol>';
				foreach ( $assigned_roles as $role ) {
					echo '<li><b>' . esc_html( $role ) . '</b></li>';
				}
				echo '</ol>';
			}

			$enable_login_restriction = get_option( 'mo_ldap_cloud_enable_role_restriction' );
			$login_status             = $this->mo_ldap_is_user_login_restricted( $assigned_roles );
			if ( ( '1' === $enable_login_restriction ) && $login_status ) {
				$login_msg = 'User Login for <strong>' . $username . "</strong> is <span style='color: red;font-weight: bold'>restricted</span>";
			} else {
				$login_msg = 'User Login for <strong>' . $username . "</strong> is <span style='color: darkgreen;font-weight: bold'>not restricted</span>";
			}
			echo "</div><div id='mo-ldap-login-status'>
					<ul><li>User Login Restriction Status : " . wp_kses_post( $login_msg ) . '</li></ul><br><br>
				  </div>';
			exit();
		}
		/**
		 * Function mo_ldap_is_user_login_restricted
		 *
		 * @param  array $assigned_roles : LDAP Assigned Role.
		 * @return bool
		 */
		public function mo_ldap_is_user_login_restricted( $assigned_roles ) {
			if ( ! is_array( $assigned_roles ) ) {
				$assigned_roles = array();
			}
			$restricted_roles = ! empty( get_option( 'mo_ldap_cloud_restricted_login_roles' ) ) ? $this->utils::secure_unserialize_option( get_option( 'mo_ldap_cloud_restricted_login_roles' ) ) : array();
			if ( ! is_array( $restricted_roles ) ) {
				$restricted_roles = array();
			}
			$mo_role_intersection = array_intersect( $assigned_roles, $restricted_roles );
			if ( ! empty( $mo_role_intersection ) && ! in_array( 'administrator', $assigned_roles, true ) ) {
				return true;
			} else {
				return false;
			}
		}
	}
}
