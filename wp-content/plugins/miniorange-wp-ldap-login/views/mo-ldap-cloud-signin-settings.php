<?php
/**
 * Display Sign in Settings page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

use MO_LDAP_CLOUD\Utils\Mo_LDAP_Cloud_Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mo_ldap_small_layout">
	<a class="mo_ldap_cloud_right" href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'attributemapping' ), $request_uri ) ); ?>" >
		<button class="mo_cloud_ldap_next_btn mo_ldap_cloud_back_btn" style="float: right;"> 
			❮ Back
		</button>
	</a>
	<form name="f" id="enable_login_form" method="post" action="">
		<?php wp_nonce_field( 'mo_ldap_cloud_enable_login_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_enable" />
		<h3>Enable login using LDAP</h3>
		<hr><br>
		<table class="mo_ldap_cloud_attributes_table">
			<tr>
				<td class="mo_ldap_cloud_enable_attr_mapping_toggle">
					<input type="checkbox" id="enable_ldap_login" name="enable_ldap_login" class="mo_ldap_cloud_toggle_switch_hide " value="1" <?php checked( ! empty( get_option( 'mo_ldap_enable_login' ) ) && '1' === get_option( 'mo_ldap_enable_login' ) ); ?> />
					<label for="enable_ldap_login" class="mo_ldap_cloud_toggle_switch"></label>
				</td>
				<td>
					<label class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">
						Enable LDAP login
					</label>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<div class="mo_ldap_cloud_note">
					<b>Note: </b>Enabling LDAP login will protect your login page by your configured LDAP. <strong>Please check this only after you have successfully tested your configuration</strong> as the default WordPress login will stop working.
					</div>
				</td>
			</tr>
		</table>
	</form>
	<script>
		jQuery('#enable_ldap_login').change(function() {
			jQuery('#enable_login_form').submit();
		});
	</script>
	<br>
	<form name="f" id="enable_register_user_form" method="post" action="">
		<?php wp_nonce_field( 'mo_ldap_cloud_register_user_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_register_user" />

		<table class="mo_ldap_cloud_attributes_table">
			<tr>
				<td class="mo_ldap_cloud_enable_attr_mapping_toggle">
					<input type="checkbox" id="mo_ldap_register_user" name="mo_ldap_register_user" class="mo_ldap_cloud_toggle_switch_hide" value="1" <?php checked( ! empty( get_option( 'mo_ldap_register_user' ) ) && '1' === get_option( 'mo_ldap_register_user' ) ); ?> />
					<label for="mo_ldap_register_user" class="mo_ldap_cloud_toggle_switch"></label>
				</td>
				<td>
					<label class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">
						Enable Auto Registering users if they do not exist in WordPress
					</label>
				</td>
			</tr>
		</table>
	</form>
	<br>
	<form name="f" id="enable_both_login_form" method="post" action="">
		<?php wp_nonce_field( 'mo_ldap_cloud_enable_both_login_nonce' ); ?>
		<div class="mo_ldap_cloud_bold_label mo_ldap_cloud_wordpress_ldap_user_login">
			Select below option to configure LDAP and WordPress Users login:
		</div>
		<input type="hidden" name="option" value="mo_ldap_enable_both_login" />
		<div class="mo_ldap_select_wrapper">
			<input type="radio" class="mo_ldap_enable_both_login" name="mo_ldap_enable_both_login" id="mo_ldap_enable_both_login" value="admin" <?php checked( ! empty( get_option( 'mo_ldap_enable_both_login' ) ) && 'admin' === get_option( 'mo_ldap_enable_both_login' ) ); ?> />
			LDAP Users and WordPress Administrator Users&nbsp;&nbsp;
			<input type="radio" class="mo_ldap_enable_both_login" name="mo_ldap_enable_both_login" id="mo_ldap_enable_both_login" value="all" <?php checked( ! empty( get_option( 'mo_ldap_enable_both_login' ) ) && ( 'all' === get_option( 'mo_ldap_enable_both_login' ) || '1' === get_option( 'mo_ldap_enable_both_login' ) ) ); ?> />
			Both LDAP and WordPress Users &nbsp;&nbsp;
			<input type="radio" class="mo_ldap_enable_both_login" name="mo_ldap_enable_both_login" id="mo_ldap_enable_both_login" value="none" <?php checked( ! empty( get_option( 'mo_ldap_enable_both_login' ) ) && ( 'none' === get_option( 'mo_ldap_enable_both_login' ) || 'false' === get_option( 'mo_ldap_enable_both_login' ) ) ); ?> />
			Only LDAP Users
		</div>
	</form>
	<br>
	<?php
	$redirect_to        = ! empty( get_option( 'mo_ldap_redirect_to' ) ) ? get_option( 'mo_ldap_redirect_to' ) : '';
	$mo_ldap_custom_url = ! empty( get_option( 'mo_ldap_custom_redirect' ) ) ? get_option( 'mo_ldap_custom_redirect' ) : '';
	?>
	<form name="f" id="form_redirect_to" method="post" action="">
		<label for="redirect_to" class="mo_ldap_cloud_signinsettings_header">Redirect after authentication: </label>
		<?php wp_nonce_field( 'mo_ldap_cloud_save_login_redirect_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_save_login_redirect" />

		<select id="redirect_to" name="redirect_to" class="mo_ldap_cloud_redirect_to">
			<option value="none"
				<?php
				if ( empty( $redirect_to ) || 'none' === $redirect_to ) {
					echo 'selected';
				}
				?>
				>None</option>
			<option value="profile"
				<?php
				if ( 'profile' === $redirect_to ) {
					echo 'selected';
				}
				?>
				>Profile Page</option>
			<option value="homepage"
				<?php
				if ( 'homepage' === $redirect_to ) {
					echo 'selected';
				}
				?>
				>Home Page</option>
			<option value="custom"
				<?php
				if ( 'custom' === $redirect_to ) {
					echo 'selected';
				}
				?>
				>Custom Page</option>
		</select><br>
	</form>
	<form name="custom_redirect_form" id="custom_redirect_form" method="post" action="">
		<?php wp_nonce_field( 'mo_ldap_cloud_custom_redirect_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_custom_redirect">
		<input class="mo_ldap_table_textbox mo_ldap_cloud_redirection_input" type="url" id="mo_ldap_custom_url" name="mo_ldap_custom_url" required placeholder="Enter Custom Page URL" value="<?php echo esc_attr( $mo_ldap_custom_url ); ?>"
			<?php
			if ( 'custom' === $redirect_to ) {
				?>
			style="display: block; margin-top: 1%;"
				<?php
			} else {
				?>
			style="display: none"
				<?php
			}
			?>
			/>
		<input type="submit" class="mo_ldap_cloud_save_user_mapping" value="Save"
			<?php
			if ( 'custom' === $redirect_to ) {
				?>
			style="display: block;  margin-top: 10px;"
				<?php
			} else {
				?>
			style="display: none"
				<?php
			}
			?>
			/>
	</form>
	<div class="mo_ldap_cloud_note mo_ldap_cloud_redirect_note">
		<b>Note: </b>After authentication, all users will be redirected to the web page you have selected. If you don't wish to redirect users, select <strong>None</strong>.
	</div>
	<br>

	<?php
	$enable_role_based_restriction = get_option( 'mo_ldap_cloud_enable_role_restriction' ) === '1' ? 'checked' : '';
	$restricted_roles              = ! empty( get_option( 'mo_ldap_cloud_restricted_login_roles' ) ) ? Mo_LDAP_Cloud_Utils::secure_unserialize_option( get_option( 'mo_ldap_cloud_restricted_login_roles' ) ) : array( '' );
	?>

	<div id="mo_ldap_cloud_restrict_login_role">
		<h3>Restrict User Login by Role</h3>
		<hr>
		<br>
		<form name="f" id="mo_ldap_cloud_save_restrict_login_by_role_form" method="post" action="">
			<?php wp_nonce_field( 'mo_ldap_save_restrict_login_by_role_nonce' ); ?>
			<input type="hidden" name="option" value="mo_ldap_cloud_save_restrict_login_by_role" />
			<input type="checkbox" <?php echo esc_attr( $enable_role_based_restriction ); ?> value="1"
				name="mo_ldap_cloud_local_restrict_user_by_role" id="mo_ldap_cloud_local_restrict_user_by_role" />
			<span class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">Enable Restrict User Login by Role</span>
			<br>
			<p style="color: green;"><em><strong>Note:</strong> User with the Administrator role will not be restricted
					while login.</em></p>
			<div id="panel1">
				<div style="display: flex; align-items: center">
					<div>
						<label class="mo_ldap_cloud_signinsettings_header">Restrict Role(s):</label>
					</div>
					<div style="margin-left: 10px; display: flex; flex-direction: row; align-items: center; justify-content: center;" id="mo_ldap_cloud_local_restrict_login_dd" class="mo_ldap_cloud_restrict_login_role_dropdown"
									tabindex="100">
						<div class="mo_ldap_cloud_restrict_anchor">Select Role(s) </div>
						<div style="margin-top: 3px;"> 
							<svg id="mo_ldap_cloud_doc_dropdown" style="margin-left: 5%;" viewBox="0 0 448 512" height="15px" width="15px" fill="" class="mo_ldap_cloud_reverse_rotate">
								<path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"/>
							</svg>
						</div>
						<ul class="mo_ldap_cloud_local_restrict_roles_list"
							style="display:none; border: 1px solid #ccc; padding: 10px; list-style-type: none; max-height: 200px; overflow-y: auto; margin-top: 187px">
							<?php
							$roles = get_role_name();
							foreach ( $roles as $key => $user_role ) {
								if ( strcasecmp( $key, 'administrator' ) === 0 ) {
									continue;
								}
								$checked_stat = in_array( $key, $restricted_roles, true ) ? 'checked' : '';
								echo '<li><label><input type="checkbox" ' . esc_attr( $checked_stat ) . ' name="mo_ldap_cloud_restrict_role[]" value="' . esc_attr( $key ) . '"/> ' . esc_html( $user_role ) . '</label></li>';
							}
							?>
						</ul>
					</div>
				</div>
				<br>
				<input type="submit" value="Save Configuration" class="mo_ldap_cloud_save_user_mapping">
			</div>
		</form>
	</div>

	<?php
	/**
	 * Get_role_name
	 *
	 * @return array
	 */
	function get_role_name() {
		global $wp_roles;
		return $wp_roles->get_names();
	}
	?>

	<br>
	<h3>Website Privacy</h3>
	<hr>
	<form name="f" id="enable_authorized_users_only" method="post" action="">
		<?php wp_nonce_field( 'mo_ldap_cloud_authorized_users_only_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_authorized_users_only" />
		<table class="mo_ldap_cloud_attributes_table">
			<tr>
				<td class="mo_ldap_cloud_enable_attr_mapping_toggle">
					<input type="checkbox" id="authorized_users_only" name="authorized_users_only" class="mo_ldap_cloud_toggle_switch_hide " value="1" <?php checked( ! empty( get_option( 'mo_ldap_authorized_users_only' ) ) && '1' === get_option( 'mo_ldap_authorized_users_only' ) ); ?> />
					<label for="authorized_users_only" class="mo_ldap_cloud_toggle_switch"></label>
				</td>
				<td>
					<label class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">
						Protect all web pages with login.
					</label>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<div class="mo_ldap_cloud_note">
						<b>Note: </b>By enabling this, only users who have been authenticated will be able to access your website.
					</div>
				</td>
			</tr>
		</table>
	</form>

	<?php
	if ( ! empty( get_option( 'mo_ldap_authorized_users_only' ) ) && '1' === get_option( 'mo_ldap_authorized_users_only' ) ) {
		?>
		<form action="" method="post" id="mo_ldap_cloud_public_pages_form" <?php echo '0' === get_option( 'mo_ldap_authorized_users_only' ) ? 'hiddden' : ''; ?>>
			<?php wp_nonce_field( 'mo_ldap_cloud_public_pages_nonce' ); ?>
			<input type="hidden" name="option" value="mo_ldap_cloud_public_pages" />
			<table class="mo_ldap_cloud_attributes_table">
				<tr>
					<td class="mo_ldap_cloud_enable_attr_mapping_toggle">
						<input type="checkbox" id="mo_ldap_cloud_public_pages_check" name="mo_ldap_cloud_public_pages_check" class="mo_ldap_cloud_toggle_switch_hide " value="1" <?php checked( get_option( 'mo_ldap_cloud_public_pages_enable' ) ); ?> />
						<label for="mo_ldap_cloud_public_pages_check" class="mo_ldap_cloud_toggle_switch"></label>
					</td>
					<td>
						<label class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">
							Add Public Pages
						</label>
					</td>
				</tr>
			</table>
			<?php if ( '1' === get_option( 'mo_ldap_cloud_public_pages_enable' ) ) { ?>
				<table id="mo_ldap_cloud_custom_pages_box">
					<?php
					if ( ! empty( get_option( 'mo_ldap_cloud_public_pages_list' ) ) ) {
						?>
						<tr>
							<td>
								<h3>Public Pages</h3>
							</td>
							<td>&nbsp;</td>
						</tr>
						<?php
						$public_pages = Mo_LDAP_Cloud_Utils::secure_unserialize_option( get_option( 'mo_ldap_cloud_public_pages_list' ) );
						$count        = 1;
						foreach ( $public_pages as $public_page ) {
							$image_id = 'mo_ldap_cloud_delete_public_page_image_' . $count;
							?>
							<tr>
								<td class="mo_ldap_cloud_public_page_link">
									<?php echo esc_html( $public_page ); ?>
									<span class="mo_ldap_cloud_visit_page_link"><a class="mo_ldap_cloud_visit_page_link_anchor" href="<?php echo esc_url( $public_page ); ?>" target="_blank" rel="noopener noreferrer">Visit Page</a></span>
								</td>
								<td>
									<a class="mo_ldap_cloud_delete_attribute_button" onmouseover="deleteButtonChange(<?php echo esc_js( $image_id ); ?>)" onmouseleave="revertDeleteButtonChange(<?php echo esc_js( $image_id ); ?>)"
										<?php
										if ( $is_customer_registered ) {
											echo "onclick=deletePublicPage('" . esc_js( $public_page ) . "')";
										}
										?>
										><img id="<?php echo esc_attr( $image_id ); ?>" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'delete.webp' ); ?>" width="15px" alt="">
									</a>
								</td>
							</tr>
							<?php
							++$count;
						}
						if ( 1 === $count ) {
							?>
							<tr>
								<td>
									<span>Currently there are no public pages for your website.</span>
								</td>
							</tr>
							<?php
						}
					} else {
						?>
						<span>Currently there are no public pages for your website.</span>
						<?php
					}
					?>
					<tr>
						<td>
							<h3>Add Public Pages</h3>
						</td>
					</tr>
					<tr>
						<td class="mo_ldap_cloud_page_url_td">
							<p>Please add <strong>Page URL</strong> Only</p>
						</td>
					</tr>
					<tr>
						<td></td>
						<td></td>
					</tr>
					<tr id="row_1" class="mo_ldap_cloud_public_pages_row_1">
						<input type="hidden" name="option" value="mo_ldap_cloud_public_pages">
						<td>
							<input type="text" class="mo_ldap_cloud_public_page_link mo_ldap_cloud_customer_registration_attr_input" id="mo_ldap_cloud_custom_page_1" name="mo_ldap_cloud_public_custom_page_1" placeholder="Page URL">
						</td>
						<td>
							<input type="button" class="button button-primary button-large mo-ldap-cloud-button-submit mo-ldap-cloud-button-bold" value="+" onclick="add_public_page();">
							<input type="button" class="button button-primary button-large mo-ldap-cloud-button-submit mo-ldap-cloud-button-bold" value="-" onclick="remove_public_page();">
						</td>
					</tr>
					<tr id="mo_ldap_cloud_custom_page">
						<td></td>
					</tr>
					<tr>
						<td>
							<input type="submit" value="Save Configuration" class="mo_ldap_cloud_save_user_mapping">
						</td>
					</tr>
				</table>
			<?php } ?>
		</form>
		<form action="" method="post" id="mo_ldap_cloud_delete_public_page_form">
			<?php wp_nonce_field( 'mo_ldap_cloud_delete_page_nonce' ); ?>
			<input type="hidden" name="option" value="mo_ldap_cloud_delete_page">
			<input type="hidden" id="mo_ldap_cloud_delete_page_name" name="mo_ldap_cloud_delete_page_name" value="">
		</form>
		<?php
	}
	?>
	<script>
		jQuery('#authorized_users_only').change(function() {
			jQuery('#enable_authorized_users_only').submit();
		});
		jQuery('#mo_ldap_cloud_public_pages_check').change(function() {
			jQuery("#mo_ldap_cloud_public_pages_form").submit()
		});

		function deleteButtonChange(imageId) {
			jQuery(imageId).attr("src", "<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'delete1.webp' ); ?>");
		}

		function revertDeleteButtonChange(imageId) {
			jQuery(imageId).attr("src", "<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'delete.webp' ); ?>");
		}
		var countFields = 1;

		function add_public_page() {
			countFields += 1;
			jQuery("<tr id='row_" + countFields + "'><td><input class='mo_ldap_cloud_customer_registration_attr_input' type='text' id='mo_ldap_cloud_public_custom_page_" + countFields + "' name='mo_ldap_cloud_public_custom_page_" + countFields + "' placeholder='Page URL' /></td></tr>").insertBefore(jQuery("#mo_ldap_cloud_custom_page"));
		}

		function remove_public_page() {
			if (countFields > 1) {
				jQuery("#row_" + countFields).remove();
				countFields -= 1;
			}
			if (countFields == 0) {
				countFields = 1;
			}
		}

		function deletePublicPage(pageName) {
			jQuery("#mo_ldap_cloud_delete_page_name").val(pageName);
			jQuery("#mo_ldap_cloud_delete_public_page_form").submit();
		}
		<?php
		if ( ! $is_customer_registered ) {
			?>
			jQuery("#enable_authorized_users_only :input").prop("disabled", true);
			<?php
		}
		?>
	</script>
	<br />
</div>
<script>
	<?php
	if ( ! $is_customer_registered ) {
		?>
		jQuery(document).ready(function() {
			jQuery("#enable_login_form :input").prop("disabled", true);
			jQuery("#enable_both_login_form :input").prop("disabled", true);
			jQuery("#form_redirect_to :input").prop("disabled", true);
			jQuery("#custom_redirect_form :input").prop("disabled", true);
			jQuery("#enable_register_user_form :input").prop("disabled", true);
			jQuery("#mo_ldap_cloud_save_restrict_login_by_role_form :input").prop("disabled", true);
			jQuery('#mo_ldap_cloud_local_restrict_login_dd').on('click', function(e) {
				e.preventDefault();
				e.stopImmediatePropagation();
				jQuery('.mo_ldap_cloud_local_restrict_roles_list').hide(); 
			});
		});
		<?php
	}
	?>
	jQuery('#redirect_to').change(function() {
		jQuery('#form_redirect_to').submit();
	});
	jQuery('.mo_ldap_enable_both_login').change(function() {
		jQuery('#enable_both_login_form').submit();
	});
	jQuery('#mo_ldap_register_user').change(function() {
		jQuery('#enable_register_user_form').submit();
	});
</script>
