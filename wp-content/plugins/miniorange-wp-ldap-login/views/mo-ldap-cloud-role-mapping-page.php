<?php
/**
 * Display role mapping page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="mo_ldap_small_layout" >
		<a style="float:right;" href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'attributemapping' ), $request_uri ) ); ?>" >
			<button class="mo_cloud_ldap_next_btn mo_ldap_cloud_next_btn" style="float:right;margin:5px;"> 
				Next ❯
			</button>
		</a>
		<a style="float: right;" href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'config' ), $request_uri ) ); ?>" >
			<button class="mo_cloud_ldap_next_btn mo_ldap_cloud_back_btn" >
				❮ Back
			</button>
		</a>
		<br>
		<?php
		$default_role = ! empty( get_option( 'mo_ldap_mapping_value_default' ) ) ? get_option( 'mo_ldap_mapping_value_default' ) : update_option( 'mo_ldap_mapping_value_default', 'subscriber' );
		?>
		<h2>LDAP Groups to WP User Role Mapping</h2>
		<hr><br>
		<form name="f" id="enable_role_mapping_form" method="post" action="">
		<?php wp_nonce_field( 'mo_ldap_cloud_enable_role_mapping_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_enable_role_mapping" />
		<table class="mo_ldap_cloud_grouptouser_role_mapping_table">	
			<tr>
			<td class="mo_ldap_cloud_enable_attr_mapping_toggle">
				<input type="checkbox" class="mo_ldap_cloud_toggle_switch_hide" id="enable_ldap_role_mapping" name="enable_ldap_role_mapping"  value="1" <?php checked( ! empty( get_option( 'mo_ldap_enable_role_mapping' ) ) && '1' === get_option( 'mo_ldap_enable_role_mapping' ) && ! empty( $default_role ) ); ?> <?php echo ! empty( $default_role ) ? '' : 'disabled'; ?>/>
				<label for="enable_ldap_role_mapping" class="mo_ldap_cloud_toggle_switch"></label>
			</td>
			<td>
				<label  class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">
					Enable Role Mapping
				</label>
			</td>
			<tr>
			<tr>
			<td> </td>
			<td>
			<div class="mo_ldap_cloud_note" >
				<b>Note: </b>Enabling Role Mapping will automatically map Users from LDAP Groups to below selected WordPress Role. Role mapping will not be applicable for primary admin of WordPress.
		</div> </td>
			</tr>
	</table>
	</form>
	<br>
	<form name="f" id="keep_existing_user_roles_form" method="post" action="">
		<?php wp_nonce_field( 'mo_ldap_cloud_keep_existing_user_roles_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_keep_existing_user_roles"/>
		<table class="mo_ldap_cloud_grouptouser_role_mapping_table">
			<tr> 
				<td class="mo_ldap_cloud_enable_attr_mapping_toggle">
				<input type="checkbox" class="mo_ldap_cloud_toggle_switch_hide" id="keep_existing_user_roles" name="keep_existing_user_roles"  value="1" <?php checked( ! empty( get_option( 'mo_ldap_keep_existing_user_roles' ) ) && '1' === get_option( 'mo_ldap_keep_existing_user_roles' ) ); ?>/>
				<label for="keep_existing_user_roles" class="mo_ldap_cloud_toggle_switch"></label>
				</td>
				<td>
					<label  class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">
					Do not remove existing roles of users (New Roles will be added)
					</label>
				</td>
			</tr>
		</table>
	</form>
	<br>
	<form id="role_mapping_form" name="f" method="post" action="">
		<?php wp_nonce_field( 'mo_ldap_cloud_save_role_mapping_nonce' ); ?>
		<input id="mo_ldap_user_mapping_form" type="hidden" name="option" value="mo_ldap_save_role_mapping" />
		<div id="panel1">
			<table aria-hidden="true" class="mo_ldap_mapping_table mo_ldap_cloud_customer_registration_attr_input">
				<tr>
					<td ><label for="default_group_mapping" class="mo_ldap_cloud_d_block mo_ldap_cloud_bold_label">Select the default WordPress role all users will have:</label></td>
					<td class="mo_ldap_cloud_cloud_default_mapping_td">
						<select name="mapping_value_default" class= "mo_ldap_cloud_standard_input mo_ldap_cloud_select_directory_server" class="mo_ldap_cloud_cloud_default_mapping_select" id="default_group_mapping">
							<?php
							if ( ! empty( get_option( 'mo_ldap_mapping_value_default' ) ) ) {
								$default_role = get_option( 'mo_ldap_mapping_value_default' );
							} else {
								$default_role = get_option( 'default_role' );
							}
							wp_dropdown_roles( $default_role );
							?>
						</select>
						<select style="display:none" id="wp_roles_list">
							<?php wp_dropdown_roles( $default_role ); ?>
						</select>
					</td>
				</tr>
			</table>
			<br>
			<div class="mo_ldap_cloud_outer" >
			<table aria-hidden="true" class="mo_ldap_mapping_table mo_ldap_cloud_attributes_table" id="ldap_role_mapping_table">
				<tr>
					<h3>Map LDAP Security Groups to WordPress Role</h3>
				</tr>
				<tr>
					<td class="mo_ldap_cloud_group_name_key"><strong>LDAP Group Name</strong></td>
					<td class="mo_ldap_cloud_group_name_key"><strong>WordPress Role</strong></td>
				</tr>
				<?php
				$mapping_count_value = get_option( 'mo_ldap_role_mapping_count' );
				$mapping_count       = 0;
				if ( ! empty( $mapping_count_value ) && is_numeric( $mapping_count_value ) ) {
					$mapping_count = intval( $mapping_count_value );
				}
				for ( $i = 1; $i <= $mapping_count; $i++ ) {
					?>
				<tr>
					<td><input class="mo_ldap_cloud_standard_input mo_ldap_cloud_customer_registration_attr_input" type="text" name="mapping_key_<?php echo esc_attr( $i ); ?>"
							value="<?php echo esc_attr( get_option( 'mo_ldap_mapping_key_' . esc_attr( $i ) ) ); ?>" placeholder="cn=group,dc=domain,dc=com" />
					</td>
					<td>
						<select class ="mo_ldap_cloud_standard_input mo_ldap_cloud_customer_registration_attr_input" name="mapping_value_<?php echo esc_attr( $i ); ?>" id="role" >
							<?php wp_dropdown_roles( get_option( 'mo_ldap_mapping_value_' . $i ) ); ?>
						</select>
					</td>
					<td class="mo_ldap_cloud_delete_role_mapping_btn">
						<div class="mo_ldap_cloud_delete_role_mapping_btn_container" >
							<?php
							$image_id = 'mo_ldap_cloud_delete_button_' . $i;
							?>
							<a class="mo_ldap_cloud_delete_attribute_button" onmouseover="deleteButtonChange(<?php echo esc_js( $image_id ); ?>)" onmouseleave="revertDeleteButtonChange(<?php echo esc_js( $image_id ); ?>)" 
									<?php
									if ( $is_customer_registered ) {
										echo "onclick=deleteGroupToRole('" . esc_js( $i ) . "')";
									}
									?>
								><img id="<?php echo esc_attr( $image_id ); ?>" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'delete.webp' ); ?>" width="15px" alt="">
							</a>
						</div>
					</td>
				</tr>
					<?php
				}
				if ( 0 === $mapping_count ) {
					?>
				<tr>
					<td><input class="mo_ldap_cloud_standard_input mo_ldap_cloud_customer_registration_attr_input" type="text" name="mapping_key_1"
							value="" placeholder="cn=group,dc=domain,dc=com" />
					</td>
					<td>
						<select class ="mo_ldap_cloud_standard_input mo_ldap_cloud_customer_registration_attr_input" name="mapping_value_1" id="role">
							<?php wp_dropdown_roles(); ?>
						</select>
					</td>
				</tr>
					<?php
				}
				?>
			</table>
			<table aria-hidden="true" class="mo_ldap_mapping_table mo_ldap_cloud_attributes_table" id="ldap_role_mapping_table">
				<tr><td><a class="mo_ldap_cloud_cursor_pointer" id ="add_mapping">Add More Mapping</a><br><br></td><td>&nbsp;</td></tr>
				<tr>
					<td class="mo_ldap_cloud_group_attr_name_td">
						<label for="group_attribute_name" class="mo_ldap_input_label_text">LDAP Group Attributes Name <span class="mo_ldap_cloud_required_attr">*</span></label>
					</td>
					<td>
						<?php
						if ( empty( get_option( 'mo_ldap_mapping_memberof_attribute' ) ) ) {
							update_option( 'mo_ldap_mapping_memberof_attribute', 'memberOf' );
						}
						$mapping_memberof_attribute = get_option( 'mo_ldap_mapping_memberof_attribute' );
						?>
						<input type="text"  class= "mo_ldap_cloud_standard_input mo_ldap_cloud_memberof mo_ldap_cloud_customer_registration_attr_input" name="mapping_memberof_attribute" required="true" placeholder="Group Attributes Name"  value="<?php echo esc_attr( $mapping_memberof_attribute ); ?>">
					</td>
				</tr>
				<tr>
					<td></td>
					<td ><em> Specify attribute which stores group names to which LDAP Users belong.</em></td>
				</tr>
					</table>	
					</div>	
					<table>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td><input type="submit" class="mo_ldap_cloud_save_user_mapping" value="Save Mapping"/></td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</div>
	</form>
	<form id="delete_group_to_role_form" method="post">
		<?php wp_nonce_field( 'mo_ldap_cloud_delete_group_to_role_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_delete_group_to_role" />
		<input type="hidden" id="delete_group_to_role_id" name="delete_group_to_role_id" value="" />
	</form>
	<br>

</div>
<div class="mo_ldap_small_layout" id="testRoleMapping">
	<form method="post" id="rolemappingtest">
		<br>
		<h2>Test Role Mapping Configuration</h2>
		<hr>
		<br>
		Enter LDAP username to test role mapping configuration
		<table aria-hidden="true" id="attributes_table" class="mo_ldap_cloud_attributes_table">
			<tbody>
				<tr></tr>
				<tr></tr>
				<tr>
					<td class="mo_ldap_cloud_conf_label"><label for="default_username" class="mo_ldap_input_label_text">Username:</label><span class="mo_ldap_cloud_required_attr">*</span></td>
					<td><input type="text" class="mo_ldap_cloud_customer_registration_attr_input mo_ldap_cloud_standard_input mo_ldap_cloud_user_credentials mo_ldap_cloud_memberof" id="mo_ldap_username" name="mo_ldap_username"required="" placeholder="Enter Username">
					</td>
				</tr>
				<tr>
					<td>
						<input type="submit" value="Test Configuration" class="mo_ldap_cloud_save_user_mapping">
					</td>
					<td></td>
				</tr>
			</tbody>
		</table>
	</form>
	<br><br>
</div>
<script>
	jQuery('#enable_ldap_role_mapping').change(function() {
		jQuery('#enable_role_mapping_form').submit();
	});
	jQuery("#keep_existing_user_roles").change(function(){
		jQuery("#keep_existing_user_roles_form").submit();
	});
	<?php
	if ( $is_customer_registered ) {
		?>
		jQuery('#add_mapping').click(function() {
			var last_index_name = jQuery('#ldap_role_mapping_table tr:last .mo_ldap_cloud_standard_input').attr('name');
			var splittedArray = last_index_name.split("_");
			var last_index = parseInt(splittedArray[splittedArray.length-1])+1;
			var dropdown = jQuery("#wp_roles_list").html();
			var new_row = '<tr><td><input class="mo_ldap_cloud_standard_input mo_ldap_cloud_customer_registration_attr_input" type="text" placeholder="cn=group,dc=domain,dc=com" name="mapping_key_'+last_index+'" value="" /></td><td><select class="mo_ldap_cloud_standard_input mo_ldap_cloud_customer_registration_attr_input" name="mapping_value_'+last_index+'" id="role">'+dropdown+'</select></td></tr>';
			jQuery('#ldap_role_mapping_table tr:last').after(new_row);
		});
		<?php
	}
	?>
	jQuery("#rolemappingtest").submit(function(event ) {
		event.preventDefault();
		testCloudRoleMappingConfiguration();
	});
	function testCloudRoleMappingConfiguration(){
		var username = jQuery("#mo_ldap_username").val();
		var nonce = "<?php echo esc_attr( wp_create_nonce( 'mo_ldap_cloud_test_role_mapping_nonce' ) ); ?>";
		var myWindow = window.open('<?php echo esc_url( site_url() ); ?>' + '/?option=testcloudrolemappingconfig&user='+username + '&_wpnonce='+nonce, "Test Attribute Configuration", "width=600, height=600");
	}
	function deleteGroupToRole(id){
		jQuery("#delete_group_to_role_id").val(id);
		jQuery("#delete_group_to_role_form").submit();
	}
	function deleteButtonChange(imageId) {
		jQuery(imageId).attr("src", "<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'delete1.webp' ); ?>");
	}
	function revertDeleteButtonChange(imageId) {
		jQuery(imageId).attr("src", "<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'delete.webp' ); ?>");
	}
	<?php
	if ( ! $is_customer_registered ) {
		?>
		jQuery( document ).ready(function() {
			jQuery("#enable_role_mapping_form :input").prop("disabled", true);
			jQuery("#keep_existing_user_roles_form :input").prop("disabled", true);
			jQuery("#enable_role_mapping_form :input[type=text]").val("");
			jQuery("#enable_role_mapping_form :input[type=url]").val("");
			jQuery("#role_mapping_form :input").prop("disabled", true);
			jQuery("#role_mapping_form :input[type=text]").val("");
			jQuery("#role_mapping_form :input[type=url]").val("");
			jQuery("#rolemappingtest :input").prop("disabled", true);
		});
		<?php
	}
	?>
</script>
