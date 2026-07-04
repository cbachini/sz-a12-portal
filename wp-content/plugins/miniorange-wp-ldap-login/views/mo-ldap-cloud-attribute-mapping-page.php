<?php
/**
 * Display attribute mapping page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mo_ldap_cloud_disabled_attribute_mapping = ( ! empty( get_option( 'mo_ldap_enable_attribute_mapping' ) ) && '1' !== get_option( 'mo_ldap_enable_attribute_mapping' ) ) || ! $is_customer_registered;
?>
<div class="mo_ldap_small_layout">
			<a style="float: right;" href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'signin_settings' ), $request_uri ) ); ?>" >
				<button class="mo_cloud_ldap_next_btn mo_ldap_cloud_next_btn" >
					Next ❯
				</button>
			</a>
			<a style="float: right;" href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'rolemapping' ), $request_uri ) ); ?>" >
				<button class="mo_cloud_ldap_next_btn mo_ldap_cloud_back_btn" style="margin:5px"> 
					❮ Back
				</button>
			</a>
			<br>
			<h2>Attribute Configuration</h2>
			<hr><br>
			<form name="f1" method="post" id="enable_attr_mapping_form">
			<table aria-hidden="true" id="attributes_table" class="mo_ldap_settings_table">
			<?php wp_nonce_field( 'mo_ldap_cloud_enable_attr_mapping_nonce' ); ?>
			<input type="hidden" name="option" value="mo_ldap_cloud_enable_attr_mapping"/>
			<tr>
				<td class="mo_ldap_cloud_enable_attr_mapping_toggle" >
				<?php
					$check_atrri_config_status = ! empty( get_option( 'mo_ldap_email_attribute' ) ) ? get_option( 'mo_ldap_email_attribute' ) : '';
				?>
				<input type="checkbox" 
						<?php
						if ( empty( $check_atrri_config_status ) ) {
							echo 'disabled';}
						?>
						id="enable_attr_mapping" name="enable_attr_mapping" class="mo_ldap_cloud_toggle_switch_hide" value="1" <?php checked( ! empty( get_option( 'mo_ldap_enable_attribute_mapping' ) ) && '1' === get_option( 'mo_ldap_enable_attribute_mapping' ) ); ?> />
						<label for="enable_attr_mapping" class="mo_ldap_cloud_toggle_switch"></label>
				</td>
				<td>
						<label class="mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">
							Enable Attribute Mapping
						</label>
				</td>
			</tr>
		</table>
		<br>	
	</form>
	<form name="f" method="post" id="attribute_config_form">
		<?php wp_nonce_field( 'mo_ldap_cloud_save_attribute_config_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_cloud_save_attribute_config" />
		<table aria-hidden="true" id="attributes_table" class="mo_ldap_cloud_attributes_table" >
			<tr class="mo_ldap_cloud_attr_mapping_tr">
				<td style="width:40%;" class="mo_ldap_cloud_left_section"><label for="ldap_intranet_attribute_mail_name" class="mo_ldap_input_attr_label_text">Email Attribute <span class="mo_ldap_cloud_required_attr">*</span></label></td>
				<td><input type="text"  class="mo_ldap_cloud_input_field1 mo_ldap_cloud_attribute_mapping_input"name="mo_ldap_email_attribute" required placeholder="Enter Email attribute" 
				value="<?php echo esc_attr( get_option( 'mo_ldap_email_attribute' ) ); ?>"/></td>
			</tr>
			<tr class="mo_ldap_cloud_attr_mapping_tr">
				<td  class="mo_ldap_cloud_left_section"><label for="ldap_intranet_attribute_mail_name" class="mo_ldap_input_attr_label_text">Phone Attribute <span class="mo_ldap_cloud_required_attr">*</span></label></td>
				<td><input type="text"  class="mo_ldap_cloud_input_field1 mo_ldap_cloud_attribute_mapping_input" name="mo_ldap_phone_attribute" required placeholder="Enter Phone attribute" 
				value="<?php echo esc_attr( get_option( 'mo_ldap_phone_attribute' ) ); ?>"/></td>
			</tr>
			<tr class="mo_ldap_cloud_attr_mapping_tr">
				<td  class="mo_ldap_cloud_left_section"><label for="ldap_intranet_attribute_mail_name" class="mo_ldap_input_attr_label_text">First Name Attribute <span class="mo_ldap_cloud_required_attr">*</span></label></td>
				<td><input type="text"  class="mo_ldap_cloud_input_field1 mo_ldap_cloud_attribute_mapping_input" name="mo_ldap_fname_attribute" required placeholder="Enter First Name attribute" 
				value="<?php echo esc_attr( get_option( 'mo_ldap_fname_attribute' ) ); ?>" /></td>
			</tr>
			<tr class="mo_ldap_cloud_attr_mapping_tr">
				<td  class="mo_ldap_cloud_left_section"><label for="ldap_intranet_attribute_mail_name" class="mo_ldap_input_attr_label_text">Last Name Attribute <span class="mo_ldap_cloud_required_attr">*</span></label></td>
				<td><input type="text"  class="mo_ldap_cloud_input_field1 mo_ldap_cloud_attribute_mapping_input" name="mo_ldap_lname_attribute" required placeholder="Enter Last Name attribute" 
				value="<?php echo esc_attr( get_option( 'mo_ldap_lname_attribute' ) ); ?>" /></td>
			</tr>
			<tr class="mo_ldap_cloud_attr_mapping_tr">
				<td  class="mo_ldap_cloud_left_section"><label for="ldap_intranet_attribute_mail_name" class="mo_ldap_input_attr_label_text">Nickname Attribute <span class="mo_ldap_cloud_required_attr">*</span></label></td>
				<td><input type="text"  class="mo_ldap_cloud_input_field1 mo_ldap_cloud_attribute_mapping_input" name="mo_ldap_nickname_attribute" required placeholder="Enter Nickname attribute" 
				value="<?php echo esc_attr( get_option( 'mo_ldap_nickname_attribute' ) ); ?>" /></td>
			</tr>
			<tr class="mo_ldap_cloud_attr_mapping_tr">
				<td  class="mo_ldap_cloud_left_section"><label for="ldap_intranet_attribute_mail_name" class="mo_ldap_input_attr_label_text">Display Name <span class="mo_ldap_cloud_required_attr">*</span></label></td>
				<td>
					<select class="mo_ldap_cloud_input_field1 mo_ldap_cloud_attribute_mapping_input mo_ldap_cloud_attribute_mapping_select" name="mo_ldap_display_name_attribute" >
						<option value="nickname" 
						<?php
						$display_name_attribute = ! empty( get_option( 'mo_ldap_display_name_attribute' ) ) ? get_option( 'mo_ldap_display_name_attribute' ) : '';
						if ( 'nickname' === $display_name_attribute ) {
							echo 'selected="selected"';}
						?>
						>Nickname</option>
						<option value="email" 
						<?php
						if ( 'email' === $display_name_attribute ) {
							echo 'selected="selected"';}
						?>
						>Email</option>
						<option value="firstname" 
						<?php
						if ( 'firstname' === $display_name_attribute ) {
							echo 'selected="selected"';}
						?>
						>First Name</option>
						<option value="firstlast" 
						<?php
						if ( 'firstlast' === $display_name_attribute ) {
							echo 'selected="selected"';}
						?>
						>First Name + Last Name</option>
						<option value="lastfirst" 
						<?php
						if ( 'lastfirst' === $display_name_attribute ) {
							echo 'selected="selected"';}
						?>
						>Last Name + First Name</option>
					</select>
				</td>
			</tr>
			<?php
			$wp_options                           = wp_load_alloptions();
			$mo_ldap_cloud_custom_attribute_count = 1;
			foreach ( $wp_options as $option => $value ) {
				if ( strpos( $option, 'mo_ldap_custom_attribute' ) !== false ) {
					?>
						<tr class="mo_ldap_cloud_attr_mapping_tr">
							<td class="mo_ldap_cloud_left_section"><span><?php echo esc_html( ucfirst( $value ) ); ?> Attribute</span></td>
							<td>
							<div class="mo_ldap_cloud_custom_attr_component">
							<input type="text" class="mo_ldap_cloud_input_field1 mo_ldap_cloud_attribute_mapping_input" name="mo_ldap_nickname_attribute" required placeholder="Enter Nickname attribute"

									<?php
									echo 'value=' . esc_attr( get_option( $option ) )
									?>
									disabled/>
									<?php
									$image_id = 'mo_ldap_cloud_delete_button_' . $mo_ldap_cloud_custom_attribute_count;
									?>
								<div class="mo_ldap_cloud_delete_icon_button" >
									<a class="mo_ldap_cloud_delete_attribute_button" onmouseover="deleteButtonChange(<?php echo esc_js( $image_id ); ?>)" onmouseleave="revertDeleteButtonChange(<?php echo esc_js( $image_id ); ?>)" 
											<?php
											if ( $is_customer_registered ) {
												echo "onclick=deleteAttributeCloud('" . esc_js( $value ) . "')";
											}
											?>
										><img id="<?php echo esc_attr( $image_id ); ?>" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . '/delete.webp' ); ?>" width="15px"  alt="">
									</a>
								</div> 
							</div>   
							</td>
						</tr>
					<?php
					++$mo_ldap_cloud_custom_attribute_count;
				}
			}
			?>
			</table>
			<table>
			<tr><td><h3>Add Custom Attributes</h3></td></tr>
			<tr>
				<td>Enter extra LDAP attributes which you wish to be included in the user profile</td>
			</tr>
			<tr>
				<td><input type="text" class="mo_ldap_cloud_input_field1" name="mo_ldap_custom_attribute_1_name" placeholder="Custom Attribute Name" /></td>
				<td><input type="button" name="add_attribute" value="+" onclick="add_custom_attribute();" class="button button-primary mo_ldap_cloud_add_button" />&nbsp;
				<input type="button" name="remove_attribute" value="-" onclick="remove_custom_attribute();" class="button button-primary mo_ldap_cloud_remove_button" /></td>
			</tr>  
			<tr id="save_config_element">
				<br>
				<td class="mo_ldap_cloud_save_config_element_td" >
					<input type="submit" value="Save Configuration" class="mo_ldap_cloud_save_user_mapping" />
				</td>
			</tr>
		</table>
	</form>
	<form id="delete_custom_attribute_form" method="post">
		<?php wp_nonce_field( 'mo_ldap_cloud_delete_custom_attribute_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_cloud_delete_custom_attribute" />
		<input type="hidden" id="custom_attribute_name" name="custom_attribute_name" value="" />
	</form> 
</div>

<script>
	jQuery('#enable_attr_mapping').change(function () {
		jQuery('#enable_attr_mapping_form').submit();
	});
	<?php
	if ( ! $is_customer_registered ) {
		?>
		jQuery( document ).ready(function() {
			jQuery("#enable_attr_mapping_form :input").prop("disabled", true);
			jQuery("#attribute_config_form :input").prop("disabled", true);
			jQuery("#delete_custom_attribute_form :input").prop("disabled", true);
		});
		<?php
	}
	?>
	var countAttributes;
	function add_custom_attribute(){
		countAttributes += 1;
		jQuery("<tr id='row_" + countAttributes + "'><td><input type='text' class='mo_ldap_cloud_input_field1 mo_ldap_cloud_attribute_mapping_input' id='mo_ldap_custom_attribute_" + countAttributes + "_name' name='mo_ldap_custom_attribute_" + countAttributes + "_name' placeholder='Custom Attribute Name' /></td></tr>").insertBefore(jQuery("#save_config_element"));
	}
	function remove_custom_attribute(){
		jQuery("#row_" + countAttributes).remove();	
		countAttributes -= 1;
		if(countAttributes == 0)
			countAttributes = 1;
	}
	function deleteAttributeCloud(attributeName){
		jQuery("#custom_attribute_name").val(attributeName);
		jQuery("#delete_custom_attribute_form").submit();
	}
	function deleteButtonChange(imageId) {
		jQuery(imageId).attr("src", "<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'delete1.webp' ); ?>");
	}
	function revertDeleteButtonChange(imageId) {
		jQuery(imageId).attr("src", "<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'delete.webp' ); ?>");
	}
	jQuery(document).ready(function(){
		countAttributes = 1;
	});
</script>
