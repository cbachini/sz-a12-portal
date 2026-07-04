<?php
/**
 * Display configuration settings page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="mo_ldap_small_layout" >
	<div class= "mo_ldap_cloud_outer" id="mo_cloud_export_pass" >
		<form id="mo_cloud_export_pass_form" method="post" action="" name="mo_cloud_export_pass">
			<input type="hidden" name="option" value="mo_ldap_cloud_pass"/>
			<?php wp_nonce_field( 'mo_ldap_cloud_pass_nonce' ); ?>
			<table class="mo_ldap_cloud_attributes_table">
				<tr>
					<h2>Export Configurations</h2>
				</tr>
				<tr>
					<p>This tab will help you to transfer your plugin configurations when you change your WordPress instance.</p>
				</tr>
				<tr>
					<td class="mo_ldap_cloud_enable_attr_mapping_toggle">
						<input type="checkbox" id="enable_export_pass" name="enable_export_pass" class="mo_ldap_cloud_toggle_switch_hide" value="1" onchange="this.form.submit()" 
						<?php
							checked( '1' === get_option( 'mo_ldap_cloud_export_pass' ) );
						?>
						/>
						<label for="enable_export_pass" class="mo_ldap_cloud_toggle_switch"></label>
					</td>
					<td>
					<label class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">
					Export Service Account password.
					</label>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<div class="mo_ldap_cloud_note">
						<b>Note: </b>This will lead to your service account password to be exported in encrypted fashion in a file.
						(Enable this only when server password is needed.)
						</div>
					</td>
				</tr>
			</table>
		</form>
	<form id="mo_ldap_cloud_export_form" method="post" action="" name="mo_ldap_cloud_export">
		<input type="hidden" name="option" value="mo_ldap_cloud_export"/>
		<?php wp_nonce_field( 'mo_ldap_cloud_export_nonce' ); ?>
		<br>
		<table>
			<tr>
				<td>
					<input type="button" class="mo_ldap_cloud_save_user_mapping" onclick="document.forms['mo_ldap_cloud_export'].submit()" value="Export configuration" />
				</td>
			</tr>
		</table>
	</form>
	</div>
	<div class= "mo_ldap_cloud_outer" id="mo_cloud_import">
		<form id="mo_cloud_import_form" method="post" action="" name="mo_cloud_import" enctype="multipart/form-data">
		<input type="hidden" name="option" value="mo_ldap_cloud_import"/>
			<?php wp_nonce_field( 'mo_ldap_cloud_import_nonce' ); ?>
			<table class="mo_ldap_cloud_attributes_table">
				<tbody>
				<tr>
					<h2>Import Configurations</h2>
				</tr>
				<tr>
					<td><p>This tab will help you to transfer your plugin configurations from your older WordPress
							instance.</p></td>
				</tr>
				<tr>
					<td>
						<input type="file" name="mo_ldap_cloud_import_file" id="mo_ldap_cloud_import_file" required >
					</td>
				</tr>
				<tr>
					<td><br>
					<td>
				</tr>
				<tr>
					<td>
						<input type="submit" class="mo_ldap_cloud_save_user_mapping" value="Import Configuration" name="import_file">
					</td>
				</tr>
				<tr>
				</tr>
				</tbody>
			</table>
		</form>
	</div>
	<script>
		<?php
		if ( ! $is_customer_registered ) {
			?>
			jQuery( document ).ready(function() {
				jQuery("#mo_cloud_export_pass_form :input").prop("disabled", true);
				jQuery("#mo_ldap_cloud_export_form :input").prop("disabled", true);
				jQuery("#mo_cloud_import_form :input").prop("disabled", true);
			});
			<?php
		}
		?>
	</script>
</div>
</div>
