<?php
/**
 * This file contains constant used for plugin setup.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Lib
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-mo-ldap-cloud-basic-enum.php';

if ( ! class_exists( 'MO_LDAP_Cloud_Config_Details' ) ) {
	/**
	 * MO_LDAP_Cloud_Config_Details
	 */
	class MO_LDAP_Cloud_Config_Details extends Mo_LDAP_Cloud_Basic_Enum {
		const LDAP_LOGIN_ENABLE       = 'mo_ldap_enable_login';
		const AUTH_USER_BOTH_LDAP_WP  = 'mo_ldap_enable_both_login';
		const REDIRECT_TO             = 'mo_ldap_redirect_to';
		const CUSTOM_URL              = 'mo_ldap_custom_redirect';
		const AUTO_REGISTERING        = 'mo_ldap_register_user';
		const PROTECT_CONTENT         = 'mo_ldap_authorized_users_only';
		const DIRECTORY_SERVER_VALUE  = 'mo_ldap_cloud_directory_server_value';
		const SERVER_URL              = 'mo_ldap_server_url';
		const SERVER_PROTOCOL         = 'mo_ldap_ldap_protocol';
		const SERVER_PORT             = 'mo_ldap_ldap_port_number';
		const SERVER_DOMAIN_URL       = 'mo_ldap_ldap_server_address';
		const SERVER_DN               = 'mo_ldap_server_dn';
		const SERVER_PASSWORD         = 'mo_ldap_server_password';
		const SEARCH_BASE             = 'mo_ldap_search_base';
		const FILTER_CHECK            = 'mo_ldap_cloud_filter_check';
		const USER_ATTR               = 'mo_ldap_username_attributes';
		const USER_EXTRA_ATTR         = 'mo_ldap_extra_user_attribute';
		const SEARCH_FILTER           = 'mo_ldap_search_filter';
		const ROLE_RESTRICTION_ENABLE = 'mo_ldap_cloud_enable_role_restriction';
		const RESTRICTED_ROLES        = 'mo_ldap_cloud_restricted_login_roles';
	}
}
