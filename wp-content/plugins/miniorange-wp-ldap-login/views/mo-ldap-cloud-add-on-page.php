<?php
/**
 * Display add-ons page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="mo_ldap_add_on_layout" class="mo_ldap_addon_layout" >
	<h4 class="mo_ldap_cloud_addon_header" ><div class="mo_ldap_cloud_addon_header_premium" >Premium	</div> Add-ons</h4>
	<div class="mo_ldap_cloud_addon_container">
		<div class="mo_ldap_cloud_addon_card">
			<div class="mo_ldap_cloud_addon_logo_container">
				<img class="mo_ldap_cloud_addons_logo" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'addon-images/logo1.webp' ); ?>" alt="">
			</div>
			<div class="mo_ldap_cloud_card_content">
				<h3 class="mo_ldap_cloud_addon_head">Auto Login (SSO) for Shared Hosting </h3>
				<p class="mo_ldap_cloud_addon_body">Auto login (sso) into your WordPress site hosted on shared hosting environment.</p>
				<a id="" href="#" class="mo_ldap_cloud_links" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, 'I am interested in Auto Login (SSO)')" >Contact Us</a>
			</div>
		</div>
		<div class="mo_ldap_cloud_addon_card">
			<div class="mo_ldap_cloud_addon_logo_container">
				<img class="mo_ldap_cloud_addons_logo" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'addon-images/logo2.webp' ); ?>" alt="">
			</div>
			<div class="mo_ldap_cloud_card_content">
				<h3 class="mo_ldap_cloud_addon_head">Sync Users from LDAP Directory </h3>
				<p class="mo_ldap_cloud_addon_body">Sync your ldap directory users to your WordPress site. Schedules can be configured for the sync to run at a specific time and after a specific interval.</p>
				<a id="" href="#" class="mo_ldap_cloud_links" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, 'I am interested in Sync Users from LDAP Directory')" >Contact Us</a>
			</div>
		</div>
		<div class="mo_ldap_cloud_addon_card">
			<div class="mo_ldap_cloud_addon_logo_container">
				<img class="mo_ldap_cloud_addons_logo" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'addon-images/PagePostRestriction.webp' ); ?>" alt="">
			</div>
			<div class="mo_ldap_cloud_card_content">
				<h3 class="mo_ldap_cloud_addon_head">Page/Post Restriction </h3>
				<p class="mo_ldap_cloud_addon_body">Restrict WordPress pages/posts based on WordPress roles.</p>
				<a id="" href="#" class="mo_ldap_cloud_links" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, 'I am interested in Page/Post Restriction')" >Contact Us</a>
			</div>
		</div>
		<div class="mo_ldap_cloud_addon_card">
			<div class="mo_ldap_cloud_addon_logo_container">
				<img class="mo_ldap_cloud_addons_logo" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'addon-images/WPCli.webp' ); ?>" alt="">
			</div>
			<div class="mo_ldap_cloud_card_content">
				<h3 class="mo_ldap_cloud_addon_head">WP-CLI </h3>
				<p class="mo_ldap_cloud_addon_body">Configure the miniOrange LDAP/AD Login For Cloud & Shared Hosting Platform Plugin through command line.</p>
				<a id="" href="#" class="mo_ldap_cloud_links" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, 'I am interested in WP-CLI')" >Contact Us</a>
			</div>
		</div>
		<div class="mo_ldap_cloud_addon_card">
			<div class="mo_ldap_cloud_addon_logo_container">
				<img class="mo_ldap_cloud_addons_logo" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'addon-images/BuddyPress.webp' ); ?>" alt="">
			</div>
			<div class="mo_ldap_cloud_card_content">
				<h3 class="mo_ldap_cloud_addon_head">BuddyPress/BuddyBoss Profile Integration Add-on </h3>
				<p class="mo_ldap_cloud_addon_body">
					Integration with BuddyPress to sync extended profiles of users with LDAP attributes upon login.
				</p>
				<a id="" href="#" class="mo_ldap_cloud_links" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, 'I am interested in BuddyPress/BuddyBoss Profile Integration Add-on')" >Contact Us</a>
			</div>
		</div>
		<div class="mo_ldap_cloud_addon_card">
			<div class="mo_ldap_cloud_addon_logo_container">
				<img class="mo_ldap_cloud_addons_logo" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'addon-images/ProfilePicture.webp' ); ?>" alt="">
			</div>
			<div class="mo_ldap_cloud_card_content">
				<h3 class="mo_ldap_cloud_addon_head">Profile Picture Sync </h3>
				<p class="mo_ldap_cloud_addon_body">
					Update WordPress user profile picture with the thumbnail photo stored in your Active Directory/ LDAP server.
				</p>
				<a id="" href="#" class="mo_ldap_cloud_links" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, 'I am interested in Profile Picture Sync')" >Contact Us</a>
			</div>
		</div>
		<div class="mo_ldap_cloud_addon_card">
			<div class="mo_ldap_cloud_addon_logo_container">
				<img class="mo_ldap_cloud_addons_logo" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'addon-images/ThirdParty.webp' ); ?>" alt="">
			</div>
			<div class="mo_ldap_cloud_card_content">
				<h3 class="mo_ldap_cloud_addon_head">Third Party Plugin User Profile Integration </h3>
				<p class="mo_ldap_cloud_addon_body">
					Update the user profiles created using any third party plugins with the attributes present in your Active Directory/LDAP Server.
				</p>
				<a id="" href="#" class="mo_ldap_cloud_links" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, 'I am interested in Third Party Plugin User Profile Integration')" >Contact Us</a>
			</div>
		</div>
		<div class="mo_ldap_cloud_addon_card">
			<div class="mo_ldap_cloud_addon_logo_container">
				<img class="mo_ldap_cloud_addons_logo" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'addon-images/GroupsPlugin.webp' ); ?>" alt="">
			</div>
			<div class="mo_ldap_cloud_card_content">
				<h3 class="mo_ldap_cloud_addon_head">WP Groups Plugin Integration </h3>
				<p class="mo_ldap_cloud_addon_body">
					Assign users to WordPress groups based on their groups memberships present in the Active Directory. You can map any number of WordPress groups to the LDAP/AD groups.
				</p>
				<a id="" href="#" class="mo_ldap_cloud_links" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, 'I am interested in WP Groups Plugin Integration')" >Contact Us</a>
			</div>
		</div>
		<div class="mo_ldap_cloud_addon_card">
			<div class="mo_ldap_cloud_addon_logo_container">
				<img class="mo_ldap_cloud_addons_logo" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'addon-images/UltimateMember.webp' ); ?>" alt="">
			</div>
			<div class="mo_ldap_cloud_card_content">
				<h3 class="mo_ldap_cloud_addon_head">Ultimate Member Login Integration </h3>
				<p class="mo_ldap_cloud_addon_body">
					Using LDAP credentials, login to Ultimate Member and integrate your Ultimate Member User Profile with LDAP attributes.
				</p>
				<a id="" href="#" class="mo_ldap_cloud_links" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, 'I am interested in Ultimate Member Login Integration')" >Contact Us</a>
			</div>
		</div>
	</div>
</div>
<p class="mo_ldap_cloud_addon_contact_details">For more details, please drop a mail to <a href="mailto:info@xecurify.com">info@xecurify.com</a> or <a href="mailto:ldapsupport@xecurify.com">ldapsupport@xecurify.com</a>.</p>

