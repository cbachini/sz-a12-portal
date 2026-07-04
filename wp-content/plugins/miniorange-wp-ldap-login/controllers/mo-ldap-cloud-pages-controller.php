<?php
/**
 * Plugin pages controller.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Controllers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$request_uri              = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
$ldap_server_protocol     = ! empty( get_option( 'mo_ldap_ldap_protocol' ) ) ? get_option( 'mo_ldap_ldap_protocol' ) : 'ldap';
$ldap_server_address      = ! empty( get_option( 'mo_ldap_ldap_server_address' ) ) ? $utils::decrypt( get_option( 'mo_ldap_ldap_server_address' ) ) : '';
$ldap_server_port_number  = ! empty( get_option( 'mo_ldap_ldap_port_number' ) ) ? get_option( 'mo_ldap_ldap_port_number' ) : '389';
$ldaps_server_port_number = ! empty( get_option( 'mo_ldap_ldaps_port_number' ) ) ? get_option( 'mo_ldap_ldaps_port_number' ) : '636';
$dn                       = ! empty( get_option( 'mo_ldap_server_dn' ) ) ? $utils::decrypt( get_option( 'mo_ldap_server_dn' ) ) : '';
$search_base              = ! empty( get_option( 'mo_ldap_search_base' ) ) ? $utils::decrypt( get_option( 'mo_ldap_search_base' ) ) : '';
$search_filter            = ( ! empty( get_option( 'mo_ldap_search_filter' ) ) && ! empty( $utils::decrypt( get_option( 'mo_ldap_search_filter' ) ) ) ) ? $utils::decrypt( get_option( 'mo_ldap_search_filter' ) ) : '(&(|(objectClass=person)(objectClass=user))(sAMAccountName=?))';
$is_customer_registered   = $utils::is_customer_registered();

if ( $is_customer_registered ) {
	$directory_server_value = ! empty( get_option( 'mo_ldap_cloud_directory_server_value' ) ) ? get_option( 'mo_ldap_cloud_directory_server_value' ) : '';
} else {
	$directory_server_value = '';
}

$mo_filter_check          = ! empty( get_option( 'mo_ldap_cloud_filter_check' ) ) ? get_option( 'mo_ldap_cloud_filter_check' ) : '';
$user_attribute           = ! empty( get_option( 'mo_ldap_username_attributes' ) ) ? $utils::secure_unserialize_option( get_option( 'mo_ldap_username_attributes' ) ) : '';
$username_ldap_attributes = array(
	array(
		'name'  => 'sAMAccountName',
		'value' => 'samaccountname',
	),
	array(
		'name'  => 'mail',
		'value' => 'mail',
	),
	array(
		'name'  => 'userPrincipalName',
		'value' => 'userprincipalname',
	),
	array(
		'name'  => 'uid',
		'value' => 'uid',
	),
	array(
		'name'  => 'cn',
		'value' => 'cn',
	),
	array(
		'name'  => 'Extra attributes',
		'value' => 'extraUserAttribute',
	),
);
$extra_user_attribute     = ! empty( get_option( 'mo_ldap_extra_user_attribute' ) ) ? get_option( 'mo_ldap_extra_user_attribute' ) : '';
?>


<div class="mo_ldap_cloud_page_body">
	<?php
		require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-side-navbar.php';
	?>
	<div class="mo_ldap_cloud_tab_container">
		<?php
		if ( strcmp( $active_tab, 'default' ) === 0 ) {
			require_once MO_LDAP_CLOUD_CONTROLLERS . 'mo-ldap-cloud-navbar-controller.php';
		} elseif ( strcmp( $active_tab, 'auth_logs' ) === 0 ) {
			require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-authentication-logs-page.php';
		} elseif ( strcmp( $active_tab, 'config_settings' ) === 0 ) {
			require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-configuration-settings-page.php';
		} elseif ( strcmp( $active_tab, 'other-products' ) === 0 ) {
			require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-other-products-page.php';
		} elseif ( strcmp( $active_tab, 'addons' ) === 0 ) {
			require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-add-on-page.php';
		} elseif ( 'debugger' === $active_tab ) {
				require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-debugger.php';
		}
		?>
	</div>


</div>

