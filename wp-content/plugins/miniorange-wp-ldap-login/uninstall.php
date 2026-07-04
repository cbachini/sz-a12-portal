<?php
/**
 * This file is executed at the time of plugin uninstallation.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! defined( 'ABSPATH' ) ) {
	exit;
}

delete_option( 'mo_ldap_admin_email' );
delete_option( 'mo_ldap_new_registration' );
delete_option( 'mo_ldap_admin_phone' );
delete_option( 'mo_ldap_verify_customer' );
delete_option( 'mo_ldap_admin_api_key' );
delete_option( 'mo_ldap_customer_token' );
delete_option( 'mo_ldap_message' );
delete_option( 'mo_ldap_enable_login' );
delete_option( 'mo_ldap_enable_both_login' );
delete_option( 'mo_ldap_redirect_to' );
delete_option( 'mo_ldap_custom_redirect' );
delete_option( 'mo_ldap_skip_redirectto_parameter' );
delete_option( 'mo_ldap_register_user' );
delete_option( 'mo_ldap_authorized_users_only' );
delete_option( 'mo_ldap_server_url' );
delete_option( 'mo_ldap_ldap_server_address' );
delete_option( 'mo_ldap_server_dn' );
delete_option( 'mo_ldap_server_password' );
delete_option( 'mo_ldap_dn_attribute' );
delete_option( 'mo_ldap_search_base' );
delete_option( 'mo_ldap_search_filter' );
delete_option( 'mo_ldap_free_version' );
delete_option( 'mo_ldap_check_ln' );
delete_option( 'mo_ldap_admin_fname' );
delete_option( 'mo_ldap_admin_lname' );
delete_option( 'mo_ldap_admin_company' );
delete_option( 'mo_ldap_enable_role_mapping' );
delete_option( 'mo_ldap_keep_existing_user_roles' );
delete_option( 'mo_ldap_role_mapping_count' );
delete_option( 'mo_ldap_enable_attribute_mapping' );
delete_option( 'mo_ldap_mapping_value_default' );
delete_option( 'mo_ldap_mapping_memberof_attribute' );
delete_option( 'mo_ldap_email_attribute' );
delete_option( 'mo_ldap_phone_attribute' );
delete_option( 'mo_ldap_fname_attribute' );
delete_option( 'mo_ldap_lname_attribute' );
delete_option( 'mo_ldap_nickname_attribute' );
delete_option( 'mo_ldap_display_name_attribute' );
delete_option( 'mo_ldap_cloud_filter_check' );
delete_option( 'mo_ldap_username_attributes' );
delete_option( 'mo_ldap_extra_user_attribute' );
delete_option( 'mo_ldap_cloud_directory_server_value' );
delete_option( 'mo_ldap_cloud_directory_server_custom_value' );
delete_option( 'mo_ldap_cloud_directory_server' );
delete_option( 'mo_ldap_license_status' );
delete_option( 'mo_ldap_cloud_license_info' );
delete_option( 'mo_ldap_enable_debugger' );
delete_option( 'mo_ldap_cloud_local_user_report_log' );
delete_option( 'mo_ldap_cloud_user_logs_table_exists' );
delete_option( 'mo_ldap_cloud_enable_role_restriction' );
delete_option( 'mo_ldap_cloud_restricted_login_roles' );
delete_option( 'mo_ldap_cloud_remove_security_notice' );
delete_option( 'mo_ldap_cloud_enable_log_cleanup' );
delete_option( 'mo_ldap_cloud_log_cleanup_interval' );

$wp_options = wp_load_alloptions();
foreach ( $wp_options as $option => $option_value ) {
	if ( strpos( $option, 'mo_ldap_custom_attribute_' ) !== false ) {
		delete_option( $option );
	} elseif ( strpos( $option, 'mo_ldap_mapping_key_' ) !== false || strpos( $option, 'mo_ldap_mapping_value_' ) !== false ) {
		delete_option( $option );
	}
}

delete_auth_report_table();
/**
 * Function for deleting authentication report table from database.
 *
 * @return void
 */
function delete_auth_report_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'cloud_user_report';
	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %1s', $table_name ) ); // phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared -- Caching is not necessary here.
}
