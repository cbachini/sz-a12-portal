<?php
/**
 * This file contains constant used for Role Mapping.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Lib
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-mo-ldap-cloud-basic-enum.php';

if ( ! class_exists( 'MO_LDAP_Cloud_Role_Mapping_Details' ) ) {
	/**
	 * MO_LDAP_Cloud_Role_Mapping_Details
	 */
	class MO_LDAP_Cloud_Role_Mapping_Details extends Mo_LDAP_Cloud_Basic_Enum {
		const ROLE_MAPPING_ENABLE   = 'mo_ldap_enable_role_mapping';
		const KEEP_EXISTING_ROLE    = 'mo_ldap_keep_existing_user_roles';
		const DEFAULT_MAPPING_VALUE = 'mo_ldap_mapping_value_default';
		const MAPPING_COUNT         = 'mo_ldap_role_mapping_count';
		const ROLE_MAPPING_KEY      = 'mo_ldap_mapping_key_';
		const ROLE_MAPPED_VALUE     = 'mo_ldap_mapping_value_';
		const GROUP_ATTRIBUTE_NAME  = 'mo_ldap_mapping_memberof_attribute';
	}
}
