<?php
/**
 * Autoload all the plugin constants.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir_name = substr( MO_LDAP_CLOUD_PLUGIN_NAME, 0, strpos( MO_LDAP_CLOUD_PLUGIN_NAME, '/' ) );
define( 'MO_LDAP_CLOUD_NAME', $dir_name );
define( 'MO_LDAP_CLOUD_HOST_NAME', 'https://login.xecurify.com' );
define( 'MO_LDAP_CLOUD_DIR', plugin_dir_path( __FILE__ ) );
define( 'MO_LDAP_CLOUD_URL', plugin_dir_url( __FILE__ ) );
define( 'MO_LDAP_CLOUD_VIEWS', MO_LDAP_CLOUD_DIR . 'views/' );
define( 'MO_LDAP_CLOUD_IMAGES', MO_LDAP_CLOUD_URL . 'includes/images/' );
define( 'MO_LDAP_CLOUD_CONTROLLERS', MO_LDAP_CLOUD_DIR . 'controllers/' );
define( 'MO_LDAP_CLOUD_LIB', MO_LDAP_CLOUD_DIR . 'lib/' );
define( 'MO_LDAP_CLOUD_LOGO_URL', MO_LDAP_CLOUD_URL . 'includes/images/logo.webp' );
define( 'MO_LDAP_CLOUD_INCLUDES', MO_LDAP_CLOUD_URL . 'includes/' );
define( 'MO_LDAP_CLOUD_VERSION', '6.2.0' );
define(
	'MO_LDAP_CLOUD_ESC_ALLOWED',
	array(
		'a'      => array(
			'href'  => array(),
			'title' => array(),
		),
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'b'      => array(),
		'h1'     => array(),
		'h2'     => array(),
		'h3'     => array(),
		'h4'     => array(),
		'h5'     => array(),
		'h6'     => array(),
		'i'      => array(),
		'div'    => array(),
		'img'    => array(),
	)
);
define(
	'TAB_LDAP_CLOUD_CLASS_NAMES',
	maybe_serialize(
		array(
			'ldap_Login'        => 'MO_LDAP_Cloud_Account_Details',
			'ldap_config'       => 'MO_LDAP_Cloud_Config_Details',
			'Role_Mapping'      => 'MO_LDAP_Cloud_Role_Mapping_Details',
			'Attribute_Mapping' => 'MO_LDAP_Cloud_Attribute_Mapping_Details',
		)
	)
);
