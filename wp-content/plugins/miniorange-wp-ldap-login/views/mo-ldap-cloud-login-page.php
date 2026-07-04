<?php
/**
 * Display Login with miniOrange page.
 *
 * @package miniOrange SL_Management
 * @subpackage views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="mo_ldap_cloud_account_box">
	<div class="mo_ldap_cloud_account_box_container">
		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'default' ), $request_uri ) ); ?>" class="mo_ldap_cloud_view_faq_page_anchor mo_cloud_ldap_back_btn mo_ldap_cloud_plugin_config_back_btn mo_ldap_cloud_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'back.svg' ); ?>" height="10px" width="15px"></span> Plugin Config</a>
	</div>
	<div class="mo_ldap_cloud_registration_info">
		<div>
			<img src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'registration.svg' ); ?>" width="70%">
		</div>
		<div class="mo_ldap_cloud_popup_page_para">Why should I register?</div>
		<div class="mo_ldap_cloud_registration_para">
			You should register so that in case you need help, we can help you with step by step
			instructions. We support all known directory systems like Active Directory, OpenLDAP, JumpCloud etc.
			<strong>You will also need a miniOrange account to upgrade to the premium version of the plugins.</strong> We do not store any information except the email that you will use to register with us.
		</div>
	</div>

	<div class="mo_ldap_cloud_register_box">
		<div class="mo_ldap_cloud_popup_page_para mo_ldap_cloud_registration_heading">Login with miniOrange</div>
		<div>
			<form name="mo_ldap_verify_password" id="mo_ldap_verify_password" method="post" action="">
				<?php wp_nonce_field( 'mo_ldap_cloud_verify_customer_nonce' ); ?>
				<input type="hidden" name="option" value="mo_ldap_verify_customer"/>
				<div class="trial_page_input_email">
					<label class="mo_ldap_cloud_label mo_ldap_input_label_text" for="mo_ldap_cloud_register_email">Email</label>
					<input id="mo_ldap_cloud_register_email" class="mo_ldap_cloud_customer_registration_attr_input mo_ldap_cloud_email_pop_up_input_field" type="email" name="email" required placeholder="person@example.com" value="<?php echo esc_attr( get_option( 'mo_ldap_admin_email' ) ); ?>"/>
				</div>
				<div class="trial_page_input_email">
					<label class="mo_ldap_cloud_label mo_ldap_input_label_text" for="mo_ldap_cloud_register_password">Password</label>
					<input id="mo_ldap_cloud_register_password" class="mo_ldap_cloud_customer_registration_attr_input mo_ldap_cloud_email_pop_up_input_field" required type="password" name="password" placeholder="Choose your password (Min. length 6)" minlength="6" pattern="^[(\w)*(!@#$.%^&*-_)*]+$" title="Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*) should be present."/>
				</div>
				<div class="mo_ldap_cloud_debugger_custome_attr_td" >
					<input type="submit" name="submit" value="Login" class="mo_ldap_cloud_login_btn mo_ldap_cloud_reg_button mo_ldap_cloud_save_user_mapping mo_ldap_cloud_save_user_mapping_temp"/>
					<span><a class="mo_ldap_cloud_text_decoration_none" target="_blank" href= <?php echo esc_url( MO_LDAP_CLOUD_HOST_NAME . '/moas/idp/resetpassword' ); ?> rel="noopener">Click here if you forgot your password?</a></span>
					<hr style="width:80%"><br>
					<strong class="mo_ldap_cloud_login_create_account"><a class="mo_ldap_cloud_goto_login_anchor" id="mo_ldap_goback">Create an Account</a></strong>
				</div>
			</form>
		</div>
		<form name="f" method="post" action="" id="mo_ldap_goback_form">
			<?php wp_nonce_field( 'mo_ldap_cloud_cancel_nonce' ); ?>
			<input type="hidden" name="option" value="mo_ldap_cancel"/>
		</form>
	</div>
	<script>
	jQuery('#mo_ldap_goback').click(function () {
		jQuery('#mo_ldap_goback_form').submit();
	});
</script>
</div>

