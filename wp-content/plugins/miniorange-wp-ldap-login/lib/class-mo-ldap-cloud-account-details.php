<?php
/**
 * This file contains constant used for User Account setup.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Lib
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-mo-ldap-cloud-basic-enum.php';

if ( ! class_exists( 'MO_LDAP_Cloud_Account_Details' ) ) {
	/**
	 * MO_LDAP_Cloud_Account_Details
	 */
	class MO_LDAP_Cloud_Account_Details extends Mo_LDAP_Cloud_Basic_Enum {
		const ADMIN_CUSTOMER_ID = 'mo_ldap_admin_customer_key';
		const PLUGIN_VERSION    = 'mo_ldap_current_plugin_version';
	}
}
