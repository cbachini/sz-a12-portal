<?php
/**
 * Display Debugger Tab.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="mo_ldap_small_layout">
	<h2>Debugger Mode</h2>
	<table aria-hidden="true" class="mo_ldap_help">
		<br>
		<br>
		<tbody>
			<tr>
				<td class="mo_ldap_cloud_enable_attr_mapping_toggle">
					<form name="f" id="enable_debugger_form" method="post" action="">
						<?php wp_nonce_field( 'mo_ldap_enable_debugger_nonce' ); ?>
						<input type="hidden" name="option" value="mo_ldap_enable_debugger" />
						<input class="mo_ldap_cloud_toggle_switch_hide" id="enable_debugger" name="enable_debugger" type="checkbox" value="1" <?php checked( ! empty( get_option( 'mo_ldap_enable_debugger' ) ) && '1' === get_option( 'mo_ldap_enable_debugger' ) ); ?> />
						<label class="mo_ldap_cloud_toggle_switch" for="enable_debugger"></label>
					</form>
				</td>
				<td>
					<label class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">Enable debugger mode</label>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<div class= "mo_ldap_cloud_note">
						<b>Note: </b>Enable debugger mode by toggling the button below to view the various configuration statuses of your plugin in a new tab. These will be helpful when debugging issues.
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<br>
</div>
<script>
	jQuery('#enable_debugger').change(function() {
		jQuery('#enable_debugger_form').submit();
	});
	<?php
	if ( ! $is_customer_registered ) {
		?>
		jQuery("#enable_debugger_form :input").prop("disabled", true);
		<?php
	}
	?>
</script>
<?php
if ( empty( get_option( 'mo_ldap_enable_debugger' ) ) || '0' === get_option( 'mo_ldap_enable_debugger' ) ) {
	?>
	<?php
	return;
} elseif ( ! $is_customer_registered ) {
	return;
}

$connection_message = $utils->mo_ldap_debugger_test_connection();

?>

<div class="mo_ldap_cloud_debugger_layout">
	<table class=" mo_ldap_cloud_debugger">
		<tr>
			<td>
				<?php
				$status_image_path   = ( 'Connection was established successfully.' === $connection_message ) ? esc_url( MO_LDAP_CLOUD_IMAGES . 'green_check.webp' ) : esc_url( MO_LDAP_CLOUD_IMAGES . 'wrong.webp' );
				$directory_server    = ! empty( get_option( 'mo_ldap_cloud_directory_server' ) ) ? get_option( 'mo_ldap_cloud_directory_server' ) : '';
				$ldap_protocol       = ! empty( get_option( 'mo_ldap_ldap_protocol' ) ) ? get_option( 'mo_ldap_ldap_protocol' ) : '';
				$ldap_server_address = ! empty( get_option( 'mo_ldap_ldap_server_address' ) ) ? $utils::decrypt( get_option( 'mo_ldap_ldap_server_address' ) ) : '';
				$port_number         = 'ldap' === $ldap_protocol ? ( ! empty( get_option( 'mo_ldap_ldap_port_number' ) ) ? get_option( 'mo_ldap_ldap_port_number' ) : '389' ) : ( ! empty( get_option( 'mo_ldap_ldaps_port_number' ) ) ? get_option( 'mo_ldap_ldaps_port_number' ) : '389' );
				$server_dn           = ! empty( get_option( 'mo_ldap_server_dn' ) ) ? $utils::decrypt( get_option( 'mo_ldap_server_dn' ) ) : '';
				$search_base         = ! empty( get_option( 'mo_ldap_search_base' ) ) ? $utils::decrypt( get_option( 'mo_ldap_search_base' ) ) : '';
				$search_filter       = ! empty( get_option( 'mo_ldap_search_filter' ) ) ? $utils::decrypt( get_option( 'mo_ldap_search_filter' ) ) : '';
				?>
				<h2 class ="mo_ldap_left">LDAP Sever Status<span class="mo_ldap_cloud_debugger_span" ><img alt="" width="25%" height="25%" src="<?php echo esc_url( $status_image_path ); ?>" /></span></h2>
				<table>
					<tr>
						<td class="mo_ldap_cloud_directory_server_debugger_head">
							<strong>Directory Server</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $directory_server ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Server URL</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $ldap_protocol ) . '://' . esc_html( $ldap_server_address ) . ':' . ( esc_html( $port_number ) ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Username</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $server_dn ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Search Base</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $search_base ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Search Filter</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $search_filter ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Connection Status</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php
							if ( 'Connection was established successfully.' === $connection_message ) {
								echo "<span class='mo_ldap_cloud_debugger_connection_success'>" . esc_html( $connection_message ) . ' </span>';
							} else {
								echo "<span class='mo_ldap_cloud_debugger_connection_fail'>" . esc_html( $connection_message ) . ' </span>';
							}
							?>
							<span onclick="window.location.reload();" class="mo_ldap_cloud_reload_debugger">&#x21bb;</span>
						</td>
					</tr>
				</table>
			<td>
		</tr>
	</table>
	<br>
</div>

<div class="mo_ldap_cloud_debugger_layout">
	<table class="mo_ldap_cloud_debugger">
		<tr>
			<td>
				<?php
				$role_mapping_enabled = ( ! empty( get_option( 'mo_ldap_enable_role_mapping' ) ) && '1' === get_option( 'mo_ldap_enable_role_mapping' ) ) ? 'Enabled' : 'Disabled';
				$keep_existing_roles  = ( ! empty( get_option( 'mo_ldap_keep_existing_user_roles' ) ) && '1' === get_option( 'mo_ldap_keep_existing_user_roles' ) ) ? 'Enabled' : 'Disabled';
				$default_role         = ! empty( get_option( 'mo_ldap_mapping_value_default' ) ) ? get_option( 'mo_ldap_mapping_value_default' ) : 'subscriber';
				$member_of_attribute  = ! empty( get_option( 'mo_ldap_mapping_memberof_attribute' ) ) ? get_option( 'mo_ldap_mapping_memberof_attribute' ) : '';
				?>
				<h3>Role Mapping Configuration Status</h3>
				<table>
					<tr>
						<td>
							<strong>Role Mapping</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $role_mapping_enabled ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Keep Existing Roles</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $keep_existing_roles ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Default Role</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $default_role ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Group Attribute Name</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $member_of_attribute ); ?>
							<br>
						</td>
					</tr>
				</table>
				<br>
				<div class="mo_ldap_cloud_debugger_table_area">
					<table class="mo_ldap_cloud_debugger_table">
						<?php
						$mapping_count = ! empty( get_option( 'mo_ldap_role_mapping_count' ) ) ? intval( get_option( 'mo_ldap_role_mapping_count' ) ) : 0;
						if ( $mapping_count > 0 ) {
							?>
								<tr>
									<td><strong>LDAP Group</strong></td>
									<td><strong>WordPress Role</strong></td>
								</tr>

							<?php
						}
						for ( $i = 1; $i <= $mapping_count; $i++ ) {

							$group   = get_option( 'mo_ldap_mapping_key_' . $i );
							$wp_role = get_option( 'mo_ldap_mapping_value_' . $i );

							?>

								<tr>
									<td><?php echo esc_html( $group ); ?></td>
									<td><?php echo esc_html( $wp_role ); ?></td>
								</tr>

								<?php

						}
						?>
					</table>
				</div>
			</td>
		</tr>
	</table>
	<br>
</div>

<div class="mo_ldap_cloud_debugger_layout">
	<table class="mo_ldap_cloud_debugger">
		<tr>
			<td>
				<?php
				$enable_attribute_mapping = ( ! empty( get_option( 'mo_ldap_enable_attribute_mapping' ) ) && '1' === get_option( 'mo_ldap_enable_attribute_mapping' ) ) ? 'Enabled' : 'Disabled';
				$ldap_email_attribute     = ! empty( get_option( 'mo_ldap_email_attribute' ) ) ? get_option( 'mo_ldap_email_attribute' ) : '';
				$ldap_phone_attribute     = ! empty( get_option( 'mo_ldap_phone_attribute' ) ) ? get_option( 'mo_ldap_phone_attribute' ) : '';
				$ldap_fname_attribute     = ! empty( get_option( 'mo_ldap_fname_attribute' ) ) ? get_option( 'mo_ldap_fname_attribute' ) : '';
				$ldap_lname_attribute     = ! empty( get_option( 'mo_ldap_lname_attribute' ) ) ? get_option( 'mo_ldap_lname_attribute' ) : '';
				$ldap_nickname_attribute  = ! empty( get_option( 'mo_ldap_nickname_attribute' ) ) ? get_option( 'mo_ldap_nickname_attribute' ) : '';
				?>
				<h3>Attribute Mapping Configuration Status</h3>
				<table>
					<tr>
						<td>
							<strong>Attribute Mapping</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $enable_attribute_mapping ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Email Attribute</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $ldap_email_attribute ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Phone Attribute</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $ldap_phone_attribute ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>First Name Attribute</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $ldap_fname_attribute ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Last Name Attribute</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $ldap_lname_attribute ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong>Nickname Attribute</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $ldap_nickname_attribute ); ?>
						</td>
					</tr>

					<?php
					$display_name = ! empty( get_option( 'mo_ldap_display_name_attribute' ) ) ? get_option( 'mo_ldap_display_name_attribute' ) : '';

					if ( 'nickname' === $display_name ) {

						$display_name = 'Nickname';

					} elseif ( 'email' === $display_name ) {

						$display_name = 'Email';

					} elseif ( 'firstname' === $display_name ) {

						$display_name = 'First Name';

					} elseif ( 'firstlast' === $display_name ) {

						$display_name = 'First Name + Last Name';

					} elseif ( 'lastfirst' === $display_name ) {

						$display_name = 'Last Name + First Name';

					}
					?>
					<tr>
						<td>
							<strong>Display Name</strong>
						</td>
						<td class="mo_ldap_cloud_debugger_td_right">
							<?php echo esc_html( $display_name ); ?>
							<br>
						</td>
					</tr>
				</table>
				<br>
				<table class="mo_ldap_cloud_debugger_table">
					<?php
						$custom_attributes = array();
						$wp_options        = wp_load_alloptions();
					foreach ( $wp_options as $option => $value ) {
						if ( ! ( strpos( $option, 'mo_ldap_custom_attribute' ) === false ) ) {
							array_push( $custom_attributes, $value );
						}
					}

					if ( count( $custom_attributes ) > 0 ) {
						?>

							<tr>
								<td colspan="2" class="mo_ldap_cloud_debugger_custome_attr_td"><strong>Custom Attributes</strong></td>
							</tr>
							<tr>
								<td><strong>Meta Key</strong></td>
								<td><strong>Attribute Name</strong></td>
							</tr>

						<?php
						$custom_attributes_size = count( $custom_attributes );
						for ( $i = 0; $i < $custom_attributes_size; $i++ ) {

							?>

								<tr>
									<td>mo_ldap_custom_attribute_<?php echo esc_html( $custom_attributes[ $i ] ); ?></td>
									<td><?php echo esc_html( $custom_attributes[ $i ] ); ?></td>
								</tr>

							<?php

						}
					}

					?>
				</table>
			</td>
		</tr>
	</table>
	<br>
</div>

<script>
	function toggleDebuggerPasswordVisibility(password_id, toggle_id) {
		const toggle = document.querySelector("#" + toggle_id);
		const password = document.querySelector("#" + password_id);

		visible_path = "<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'eye.webp' ); ?>";
		invisible_path = "<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'eye-slash.webp' ); ?>";

		if (toggle.src === visible_path) {
			toggle.src = invisible_path;
			password.innerHTML = "<?php echo esc_html( $utils::decrypt( get_option( 'mo_ldap_server_password' ) ) ); ?> ";
		} else {
			toggle.src = visible_path;
			password.innerHTML = "************* ";
		}
	}
</script>
