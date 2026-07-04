<?php
/**
 * Display top navbar.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$active_subtab = isset( $_GET['subtab'] ) ? sanitize_key( wp_unslash( $_GET['subtab'] ) ) : 'config'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.
$active_step   = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : '1'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.

?>

<div class="mo_ldap_cloud_horizontal_flex_container mo_ldap_cloud_subtab_container">
	<div class="<?php echo strcmp( $active_subtab, 'config' ) === 0 ? 'mo_ldap_cloud_active_subtab' : ''; ?>"><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'config' ), $request_uri ) ); ?>" class="mo_ldap_cloud_unset_link_affect">LDAP Configuration</a></div>
	<div class="<?php echo strcmp( $active_subtab, 'rolemapping' ) === 0 ? 'mo_ldap_cloud_active_subtab' : ''; ?>"><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'rolemapping' ), $request_uri ) ); ?>" class="mo_ldap_cloud_unset_link_affect">Role Mapping</a></div>
	<div class="<?php echo strcmp( $active_subtab, 'attributemapping' ) === 0 ? 'mo_ldap_cloud_active_subtab' : ''; ?>"><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'attributemapping' ), $request_uri ) ); ?>" class="mo_ldap_cloud_unset_link_affect">Attribute Mapping</a></div>
	<div class="<?php echo strcmp( $active_subtab, 'signin_settings' ) === 0 ? 'mo_ldap_cloud_active_subtab' : ''; ?>"><a href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'signin_settings' ), $request_uri ) ); ?>" class="mo_ldap_cloud_unset_link_affect">Login Settings</a></div>
</div>
<hr class="mo_ldap_cloud_hr">
<?php

if ( strcmp( $active_subtab, 'config' ) === 0 ) {
	require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-ldap-configuration-page.php';
} elseif ( strcmp( $active_subtab, 'rolemapping' ) === 0 ) {
	require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-role-mapping-page.php';
} elseif ( strcmp( $active_subtab, 'attributemapping' ) === 0 ) {
	require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-attribute-mapping-page.php';
} elseif ( strcmp( $active_subtab, 'signin_settings' ) === 0 ) {
	require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-signin-settings.php';
}
