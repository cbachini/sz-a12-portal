<?php
/**
 * Display OTP verification page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="mo_ldap_cloud_table_layout">
	<table aria-hidden="true" class="mo_ldap_settings_table">
		<form name="f" method="post" id="ldap_form" action="">
			<?php wp_nonce_field( 'mo_ldap_cloud_validate_otp_nonce' ); ?>
			<input type="hidden" name="option" value="mo_ldap_validate_otp" />
			<h3>Verify Your Email</h3>
			<tr>
				<td><span class="mo_ldap_cloud_required_attr" >*</span><span style="font-weight: bold">Enter OTP:</span></td>
				<td colspan="2"><input class="mo_ldap_table_textbox" autofocus="true" type="text" name="otp_token" required placeholder="Enter OTP" style="width:61%;" />
				&nbsp;&nbsp;<a style="cursor:pointer;" onclick="document.getElementById('resend_otp_form').submit();">Resend OTP</a></td>
			</tr>
			<tr><td colspan="3"></td></tr>
			<tr>
				<td>&nbsp;</td>
				<td>
				<input type="submit" value="Validate OTP" class="button button-primary button-large mo-ldap-cloud-button-submit" />
				<a id="back_button" href=""class="button button-primary button-large">Cancel</a>
				</td>
		</form>
		<form name="f" id="resend_otp_form" method="post" action="">
			<?php wp_nonce_field( 'mo_ldap_cloud_resend_otp_nonce' ); ?>
				<td>
				<input type="hidden" name="option" value="mo_ldap_resend_otp"/>
				</td>
			</tr>
		</form>
	</table>
</div>
<script>
	jQuery('#back_button').click(function() {
		<?php update_option( 'mo_ldap_registration_status', '' ); ?>
		window.location.reload();
	});
</script>
