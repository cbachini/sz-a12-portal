<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Active Directory/LDAP Integration for Cloud & Shared Hosting Platforms
 *
 * This plugin enables to integrate LDAP/AD Authentication and Sync with WordPress site for cloud & shared hosting platforms.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 */

/**
 * Plugin Name: Active Directory/LDAP Integration for Cloud & Shared Hosting Platforms
 * Plugin URI: http://miniorange.com
 * Description: Plugin for login into WordPress hosted on cloud or shared hosting platforms using credentials stored in Active Directory / other LDAP directory.
 * Author: miniOrange
 * Version: 6.2.1
 * Author URI: http://miniorange.com
 * License: Expat
 * License URI: https://plugins.miniorange.com/mit-license
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MO_LDAP_CLOUD_PLUGIN_NAME', plugin_basename( __FILE__ ) );

require_once 'class-mo-ldap-cloud-login.php';
require_once 'mo-ldap-local-autoload-plugin.php';

use MO_LDAP_CLOUD\Mo_LDAP_Cloud_Login;

new Mo_LDAP_Cloud_Login();
