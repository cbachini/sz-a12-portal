<?php
/**
 * This file contains the details of all the addons.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Utils
 */

namespace MO_LDAP_CLOUD\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'MO_LDAP_Cloud_Addon_List_Content' ) ) {

	/**
	 * Class MO_LDAP_Cloud_Addon_List_Content : Class to store the details of addon.
	 */
	class MO_LDAP_Cloud_Addon_List_Content {

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {
			define(
				'MO_LDAP_ADDONS_CONTENT',
				maybe_serialize(
					array(
						'KERBEROS_NTLM'                  =>
							array(
								'addonName'        => 'Auto-Login (SSO) for Shared Hosting',
								'addonDescription' => 'Auto login (sso) into your wordpress site hosted on shared hosting environment.',
								'addonPrice'       => '149',
								'addonLicense'     => 'ContactUs',
								'addonGuide'       => '',
								'addonVideo'       => '',
							),
						'DIRECTORY_SYNC'                 =>
							array(
								'addonName'        => 'Sync Users from LDAP Directory',
								'addonDescription' => 'Sync your ldap directory users to your wordpress site. Schedules can be configured for the sync to run at a specific time and after a specific interval.',
								'addonPrice'       => '149',
								'addonLicense'     => 'ContactUs',
								'addonGuide'       => '',
								'addonVideo'       => '',
							),

						'PAGE_LINK_RESTRICTION'          =>
							array(
								'addonName'        => 'Page/Post Link Restriction Add-on',
								'addonDescription' => 'Restrict WordPress pages/posts based on WordPress roles.',
								'addonPrice'       => '119',
								'addonLicense'     => 'ContactUs',
								'addonGuide'       => '',
								'addonVideo'       => '',
							),

						'WP_CLI_INTEGRATION'             =>
							array(
								'addonName'        => 'WP-CLI',
								'addonDescription' => 'Configure the miniOrange LDAP/AD Login For Cloud & Shared Hosting Platform Plugin through command line.',
								'addonPrice'       => '119',
								'addonLicense'     => 'ContactUs',
								'addonGuide'       => '',
								'addonVideo'       => '',
							),

						'BUDDYPRESS_PROFILE_INTEGRATION' =>
							array(
								'addonName'        => 'BuddyPress/BuddyBoss Profile Integration',
								'addonDescription' => "Integrates user's BuddyPress/BuddyBoss extended profile attributes with LDAP/AD attributes upon LDAP/AD login.",
								'addonPrice'       => '149',
								'addonLicense'     => 'ContactUs',
								'addonGuide'       => '',
								'addonVideo'       => '',
							),

						'PROFILE_PICTURE_SYNC'           =>
							array(
								'addonName'        => 'Profile Picture Sync',
								'addonDescription' => 'Update WordPress user profile picture with the thumbnail photo stored in your Active Directory/ LDAP server.',
								'addonPrice'       => '149',
								'addonLicense'     => 'ContactUs',
								'addonGuide'       => '',
								'addonVideo'       => '',
							),

						'THIRD_PARTY_PLUGIN_USER_PROFILE_INTEGRATION' =>
							array(
								'addonName'        => 'Third Party Plugin User Profile Integration',
								'addonDescription' => 'Update the user profiles created using any third party plugins with the attributes present in your Active Directory/LDAP Server.',
								'addonPrice'       => '149',
								'addonLicense'     => 'ContactUs',
								'addonGuide'       => '',
								'addonVideo'       => '',
							),

						'WP_GROUPS_PLUGIN_INTEGRATION'   =>
							array(
								'addonName'        => 'WP Groups Plugin Integration',
								'addonDescription' => 'Assign users to WordPress groups based on their groups memberships present in the Active Directory. You can map any number of WordPress groups to the LDAP/AD groups.',
								'addonPrice'       => '149',
								'addonLicense'     => 'ContactUs',
								'addonGuide'       => '',
								'addonVideo'       => '',
							),

						'ULTIMATE_MEMBER_LOGIN_INTEGRATION' =>
							array(
								'addonName'        => 'Ultimate Member Login Integration',
								'addonDescription' => 'Using LDAP credentials, login to Ultimate Member and integrate your Ultimate Member User Profile with LDAP attributes.',
								'addonPrice'       => '149',
								'addonLicense'     => 'ContactUs',
								'addonGuide'       => '',
								'addonVideo'       => '',
							),
					)
				)
			);
		}

		/**
		 * Function mo_ldap_cloud_show_cloud_addons_content
		 *
		 * @return string
		 */
		public static function mo_ldap_cloud_show_cloud_addons_content() {
			$display_message = '';
			$messages        = Mo_LDAP_Cloud_Utils::secure_unserialize_option( MO_LDAP_ADDONS_CONTENT );
			$row_elements    = 0;
			echo '<div id="ldap_addon_container" class="mo_ldap_wrapper">';
			foreach ( $messages as $message_key ) {
				if ( 0 === $row_elements % 3 ) {
					echo ' <div class="row mo_ldap_cloud_row1">';
				}
				echo '
                    <div class="cd-pricing-wrapper-addons">
                        <div data-type="singlesite" class="is-visible ldap-addon-box">
                        <div class="individual-container-addons" style="height:100%;" >
                            <header class="cd-pricing-header">
                            	<div style="height:35px"> <h2 id="addonNameh2" title=' . esc_attr( $message_key['addonVideo'] ) . '>' . esc_html( $message_key['addonName'] ) . '</h2>
                            	</div><br>
                            	<hr><br>
								<center>
									<div style="margin-right: 3%;"></div>
								</center>
                                <div style="height: 100px;display: grid;align-items: center;"><h3 class="subtitle" style="color:black;padding-left:unset;vertical-align: middle;text-align: center;letter-spacing: 1px; line-height: 1.2;">' . esc_html( $message_key['addonDescription'] ) . '</h3></div><br>
                                <div class="cd-priceAddon">
									<div style="display:inline"><span class="cd-value" id="addon2Price" >$' . esc_html( $message_key['addonPrice'] ) . ' </span><p style="display:inline;font-size:20px" id="addon2Text"> / instance</p></span></div>
                                </div>
                            </header> 
                            <footer>
                                <a id="" href="#" style="text-align: center;display:inherit" class="cd-select" onclick="openSupportForm(\'' . esc_js( $message_key['addonName'] ) . '\')" >Contact Us</a>
							</footer>
                        </div>
                    </div> 
					</div>';
				if ( 2 === $row_elements % 3 ) {
					echo ' </div>';
				}
				++$row_elements;

			}
			return $display_message;
		}
	}
}
