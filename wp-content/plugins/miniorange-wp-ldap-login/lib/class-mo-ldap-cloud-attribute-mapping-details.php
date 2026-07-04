<?php
/**
 * This file contains constant used for Attribute Mapping.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Lib
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-mo-ldap-cloud-basic-enum.php';

if ( ! class_exists( 'MO_LDAP_Cloud_Attribute_Mapping_Details' ) ) {
	/**
	 * MO_LDAP_Cloud_Attribute_Mapping_Details
	 */
	class MO_LDAP_Cloud_Attribute_Mapping_Details extends Mo_LDAP_Cloud_Basic_Enum {
		const ATTRIBUTE_MAPPING_ENABLE = 'mo_ldap_enable_attribute_mapping';
		const MAIL                     = 'mo_ldap_email_attribute';
		const PHONE                    = 'mo_ldap_phone_attribute';
		const FIRST_NAME               = 'mo_ldap_fname_attribute';
		const LAST_NAME                = 'mo_ldap_lname_attribute';
		const NICK_NAME                = 'mo_ldap_nickname_attribute';
		const DISPLAY_NAME             = 'mo_ldap_display_name_attribute';
		const CUSTOM_ATTRIBUTE_NAME    = 'mo_ldap_custom_attribute_';
	}
}
