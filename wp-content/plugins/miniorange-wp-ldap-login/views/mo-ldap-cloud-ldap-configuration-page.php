<?php
/**
 * Display LDAP configuration settings page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MO_LDAP_CLOUD\Utils\Mo_LDAP_Cloud_Utils;

if ( $is_customer_registered ) {
	$directory_server_value = ! empty( get_option( 'mo_ldap_cloud_directory_server_value' ) ) ? get_option( 'mo_ldap_cloud_directory_server_value' ) : '';
} else {
	$directory_server_value = '';
}

$mo_ldap_cloud_server_url_status = isset( $mo_ldap_cloud_server_url_status ) ? $mo_ldap_cloud_server_url_status : '';
$mo_filter_check                 = ! empty( get_option( 'mo_ldap_cloud_filter_check' ) ) ? get_option( 'mo_ldap_cloud_filter_check' ) : '';
$user_attribute                  = ! empty( get_option( 'mo_ldap_username_attributes' ) ) ? Mo_LDAP_Cloud_Utils::secure_unserialize_option( get_option( 'mo_ldap_username_attributes' ) ) : '';
$username_ldap_attributes        = array(
	array(
		'name'  => 'sAMAccountName',
		'value' => 'samaccountname',
	),
	array(
		'name'  => 'mail',
		'value' => 'mail',
	),
	array(
		'name'  => 'userPrincipalName',
		'value' => 'userprincipalname',
	),
	array(
		'name'  => 'uid',
		'value' => 'uid',
	),
	array(
		'name'  => 'cn',
		'value' => 'cn',
	),
	array(
		'name'  => 'Extra attributes',
		'value' => 'extraUserAttribute',
	),
);
$extra_user_attribute            = ! empty( get_option( 'mo_ldap_extra_user_attribute' ) ) ? get_option( 'mo_ldap_extra_user_attribute' ) : '';

?>

<div class="mo_ldap_small_layout" id="ldap-config" >
	<div>
		<a style="margin-top: 8px;float: right;" href="<?php echo esc_url( add_query_arg( array( 'subtab' => 'rolemapping' ), $request_uri ) ); ?>" >
			<button id="mo_ldap_cloud_ldap_conf_next_button" class="mo_cloud_ldap_next_btn mo_ldap_cloud_next_btn mo_ldap_cloud_conf_nxt_btn" >
				Next ❯ 
			</button>
		</a>
		<br>
		<h2>LDAP Connection Information</h2>
	  
		<hr>
		<br>
		<div class="mo_ldap_cloud_note">
			<p><strong class="mo_ldap_cloud_conf_main_notice" >NOTE: </strong> You need to allow incoming requests from hosts - <span class="mo_ldap_cloud_notice_ip_col">52.55.147.107</span> by a firewall rule for the port <span class="mo_ldap_cloud_notice_ip_col">389 </span>(<span class="mo_ldap_cloud_notice_ip_col">636</span> for SSL or ldaps) on LDAP Server.</p>
		</div>
	</div>
	<div id = "mo_form_ldap_config">
		<form id="mo_ldap_cloud_ldap_config_form" name="f" method="post" action="">
			<?php wp_nonce_field( 'mo_ldap_cloud_save_config_nonce' ); ?>
			<input id="mo_ldap_configuration_form_action" type="hidden" name="option" value="mo_ldap_save_config" />
			<input id="mo_ldap_ldap_server_port_no" type="hidden" name="mo_ldap_ldap_server_port_no" value="<?php echo esc_attr( $ldap_server_port_number ); ?>" />
			<input id="mo_ldap_ldaps_server_port_no" type="hidden" name="mo_ldap_ldaps_server_port_no" value="<?php echo esc_attr( $ldaps_server_port_number ); ?>" />
			<div id="panel1">
				<table aria-hidden="true" class="mo_ldap_settings_table mo_ldap_cloud_customer_registration_attr_input">
					<tr><td><br></td></tr>
					<tr>
						<td class=" mo_ldap_cloud_conf_label mo_ldap_cloud_left_section">
						<label for="mo_ldap_directory_server_value" class="mo_ldap_input_label_text">Directory Server <span class="mo_ldap_cloud_required_attr">*</span></label>
						</td>
						<td>
						<select name="mo_ldap_cloud_directory_server_value" id="mo_ldap_cloud_directory_server_value" onchange="mo_ldap_display_custom_directory()" class="mo_ldap_cloud_directory_server_value mo_ldap_cloud_standard_input mo_ldap_cloud_select_directory_server " required>
							<option class="mo_ldap_select_option" value="">Select</option>
							<option value="msad"
							<?php
							if ( strcmp( $directory_server_value, 'msad' ) === 0 ) {
								echo 'selected';}
							?>
							>Microsoft Active Directory</option>
							<option class="mo_ldap_select_option" value="openldap" 
							<?php
							if ( strcmp( $directory_server_value, 'openldap' ) === 0 ) {
								echo 'selected';}
							?>
							>OpenLDAP</option>
							<option class="mo_ldap_select_option" value="freeipa" 
							<?php
							if ( strcmp( $directory_server_value, 'freeipa' ) === 0 ) {
								echo 'selected';}
							?>
							>FreeIPA</option>
							<option class="mo_ldap_select_option" value="jumpcloud" 
							<?php
							if ( strcmp( $directory_server_value, 'jumpcloud' ) === 0 ) {
								echo 'selected';}
							?>
							>JumpCloud</option>
							<option class="mo_ldap_select_option" value="other" 
							<?php
							if ( strcmp( $directory_server_value, 'other' ) === 0 ) {
								echo 'selected';}
							?>
							>Other</option>
						</select>
						</td>
					</tr>
					<?php
					if ( 'other' === $directory_server_value ) {
						?>
					<tr>
						<td></td>
						<td>
							<input class="mo_ldap_table_textbox" style="width: 238px;" type="text" id="mo_ldap_cloud_directory_server_custom_value"
								name="mo_ldap_cloud_directory_server_custom_value" placeholder="Enter your directory name" value="<?php echo esc_attr( get_option( 'mo_ldap_cloud_directory_server_custom_value' ) ); ?>">
						</td>
					</tr>
						<?php
					} else {
						?>
					<tr>
						<td></td>
						<td>
							<input class="mo_ldap_table_textbox" style="width: 238px;display: none;" type="text" id="mo_ldap_cloud_directory_server_custom_value" name="mo_ldap_cloud_directory_server_custom_value"  placeholder="Enter your directory name"  value="<?php echo esc_attr( get_option( 'mo_ldap_cloud_directory_server_custom_value' ) ); ?>">
						</td>
					</tr>
						<?php
					}
					?>
					<tr></tr>
					<script type="text/javascript">
						function mo_ldap_display_custom_directory() {
							var element = document.getElementById("mo_ldap_cloud_directory_server_value").selectedIndex;
							var allOptions = document.getElementById("mo_ldap_cloud_directory_server_value").options;
							if (allOptions[element].index == 5){
								document.getElementById("mo_ldap_cloud_directory_server_custom_value").style.display = "";
							} else {
								document.getElementById("mo_ldap_cloud_directory_server_custom_value").style.display = "none";
							}
							var checkboxes = document.getElementById("user_checkboxes");
							var selectedCheckboxes = checkboxes.querySelectorAll('input[type="checkbox"]:checked');
							if(selectedCheckboxes.length == 0 || (selectedCheckboxes.length <= 1 && (selectedCheckboxes[0].value == "samaccountname" || selectedCheckboxes[0].value == "uid"))) {
								if(allOptions[element].index == 0 || allOptions[element].index == 1) {
									checkboxes.querySelectorAll('input[type="checkbox"]')[0].checked = true;
									checkboxes.querySelectorAll('input[type="checkbox"]')[3].checked = false;
								} else {
									checkboxes.querySelectorAll('input[type="checkbox"]')[3].checked = true;
									checkboxes.querySelectorAll('input[type="checkbox"]')[0].checked = false;
								}
							}
							attributeID = jQuery(this).find(".mo_ldap_no_checkbox_user").attr("id");
							jQuery("#search_filter").empty();
							searchFilter = createSearchFilter(attributeID);
							jQuery("#search_filter").val(searchFilter);
						}
					</script>
					<tr>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr>
					<td class="mo_ldap_cloud_conf_label mo_ldap_cloud_left_section">
						<label for="mo_ldap_server" class="mo_ldap_input_label_text">LDAP Server Domain/Url <span class="mo_ldap_cloud_required_attr">*</span></label>
					</td>
					<td class="mo_ldap_cloud_server_url_protocol">
				<div class=" mo_ldap_cloud_server_align ">
						<select name="mo_ldap_protocol" id="mo_ldap_protocol" class="  mo_ldap_cloud_select_server" >
							<?php
							if ( strcmp( $ldap_server_protocol, 'ldap' ) === 0 ) {
								?>
							<option value="ldap" selected>ldap</option>
							<option value="ldaps">ldaps</option>
								<?php
							} elseif ( strcmp( $ldap_server_protocol, 'ldaps' ) === 0 ) {
								?>
							<option value="ldap">ldap</option>
							<option value="ldaps" selected>ldaps</option>
								<?php
							}
							?>
						</select>	
						<input type="text" id="mo_ldap_server" name="ldap_server" placeholder="LDAP Server hostname or IP address" class="mo_ldap_cloud_input_ip <?php echo esc_attr( $mo_ldap_cloud_server_url_status ); ?>" value="<?php echo esc_attr( $ldap_server_address ); ?>" required />
						<input type="text" id="mo_ldap_server_port_no" name="mo_ldap_server_port_no" placeholder="port number" class="  mo_ldap_cloud_input_port" value="<?php echo strcmp( $ldap_server_protocol, 'ldaps' ) === 0 ? esc_attr( $ldaps_server_port_number ) : esc_attr( $ldap_server_port_number ); ?>" required/>
						</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>
							<div class="mo_ldap_cloud_note">
								<span>
									<em><b>Note: </b>Select LDAP or LDAPS from the above dropdown list. Specify the host name for the LDAP server in the above text field. Edit the port number if you have custom port number.<br/>Confirm connection to your LDAP server from <span class="mo_ldap_cloud_notice_ip_col">52.55.147.107</span> through port <span class="mo_ldap_cloud_notice_ip_col">389</span>(<span class="mo_ldap_cloud_notice_ip_col">636</span> for LDAPS).</em>
								</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td></td>
					</tr>
				</table>

				<table aria-hidden="true" class="mo_ldap_cloud_customer_registration_attr_input">
					<tr><td class="mo_ldap_cloud_conf_label mo_ldap_cloud_left_section"></td></tr>
					<tr>
						<td class="mo_ldap_cloud_conf_label mo_ldap_cloud_left_section"><label for="dn" class="mo_ldap_input_label_text">Service Account Username <span class="mo_ldap_cloud_required_attr">*</span></label></td>
						<td>
						<input type="text" id="dn" name="dn" class="mo_ldap_cloud_standard_input mo_ldap_cloud_user_credentials" placeholder="Enter Username" value="<?php echo esc_attr( $dn ); ?>" />
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><em>You can specify the Username of the LDAP server in the either way as follows<br/><strong> Username@domainname or Distinguished Name(DN) format</strong></em></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td></td>
					</tr>
					<tr><td></td></tr>
					<tr><td></td></tr>
					<tr><td></td></tr>
					<tr>
						<td class="mo_ldap_cloud_conf_label mo_ldap_cloud_left_section"><label for="ldap_server_password_field" class="mo_ldap_input_label_text">Service Account Password <span class="mo_ldap_cloud_required_attr">*</span></label></td>
						<td>
						<input type="password" id="ldap_server_password_field" name="admin_password" class="mo_ldap_cloud_standard_input mo_ldap_cloud_user_credentials" placeholder="Enter Password" required/>
						</td>
					</tr>
					<tr><td></td></tr>
					<tr><td></td></tr>
					<tr>
						<td>&nbsp;</td>
						<td><strong>The above username and password will be used to establish the connection to your LDAP server.</strong></td>
					</tr>
					<tr><td>&nbsp;</td></tr>
				</table>
			</div>

			<h3 id="mo_ldap_cloud_user_mapping_section">LDAP User Mapping Configuration</h3>
			<div id="panel1">
				<table aria-hidden="true" class="mo_ldap_settings_table mo_ldap_cloud_customer_registration_attr_input">
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td class="mo_ldap_cloud_conf_label mo_ldap_cloud_left_section"><label for="ldap_server_password_field" class="mo_ldap_input_label_text">Search Base(s):<span class="mo_ldap_cloud_required_attr">*</span></label></td>
						<td><input type="text" class="mo_ldap_cloud_standard_input mo_ldap_cloud_user_credentials mo_ldap_cloud_attribute_input_field" id="search_base" name="search_base" required placeholder="dc=domain,dc=com" value="<?php echo esc_attr( $search_base ); ?>" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>
							<div class="mo_ldap_cloud_note">
								<em><b>Note: </b>This is the LDAP Tree under which we will search for the users for authentication. If we are not able to find a user in LDAP it means they are not present in this search base or any of its sub trees. They may be present in some other search base.<br> Provide the distinguished name of the Search Base object. <strong>eg. cn=Users,dc=domain,dc=com</strong>.If you have users in different locations in the directory(OU's), separate the distinguished names of the search base objects by a semi-colon(;). <strong>eg. cn=Users,dc=domain,dc=com; ou=people,dc=domain,dc=com</strong></em>
							</div>
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
					<tr>
						<td class="mo_ldap_cloud_conf_label mo_ldap_cloud_left_section"><label for="ldap_server_password_field" class="mo_ldap_input_label_text">Dynamic Search Filter:<span class="mo_ldap_cloud_required_attr">*</span></label></td>
						<td>
							<table class="mo_ldap_cloud_custom_search_filter_table">
								<td class="mo_ldap_cloud_conf_enable_custom_search_filter">
									<div class="search_filter_slider" style="margin: -8px;">
										<?php
										$is_checked_toggle   = false;
										$filter_check_option = get_option( 'mo_ldap_cloud_filter_check' );
										if ( 'enabled' === $filter_check_option ) {
											$is_checked_toggle = true;
										} elseif ( 'disabled' === $filter_check_option ) {
											$is_checked_toggle = false;
										}
										?>
										<input type="checkbox" class="mo_ldap_cloud_toggle_switch_hide" id="mo_ldap_cloud_search_filter_check" name="ldap_cloud_search_filters"  value="1" <?php checked( $is_checked_toggle ); ?>/>
										<label for="mo_ldap_cloud_search_filter_check" class="mo_ldap_cloud_toggle_switch"></label>
									</div>
								</td>
								<td colspan="2"><label class= "mo_ldap_cloud_d_inline mo_ldap_cloud_bold_label">Enable this to provide your custom search filter manually.</label></td>
							</table>
						</td>
					</tr>
					<tr>
						<td></td>
						<td colspan="2">
							<div id="mo_ldap_cloud_user_attributes" class="mo_ldap_cloud_user_attributes" >
								<div id="mo_ldap_cloud_custom_search_filter_notice" class="mo_ldap_cloud_note" >
									<span><b>Note: </b>You can select User Attribute(s) to auto-create Custom Search Filter. You can also add your own Attribute in the Custom Search Filter.</span>
								</div>
								<br>
								<div>
									<table>
										<td class="mo_ldap_cloud_conf_username_attr">
											<div class="mo_ldap_cloud_conf_username_attr_label"><div><strong class="mo_ldap_cloud_conf_username_size" >Username Attribute:<span class="mo_ldap_cloud_required_attr">*</span></strong></div></td>
										<td>
											<div id="mo_ldap_cloud_search_filter_ldap" >
												<?php
													$selected_attributes   = $user_attribute;
													$predefined_attributes = $username_ldap_attributes;
													$key                   = 'user';
													require_once 'mo-ldap-cloud-user-attributes-dropdown.php';
												?>
										</td>
									</table>
								</div>
							</div>
							<div id = "mo_ldap_cloud_user_attributes" style="display: none">
								<table>
									<tbody>
									<tr >
										<td class="mo_ldap_cloud_conf_username_attr">
											<div id='mo_ldap_cloud_extra_user_attribute_div' hidden> <strong>Extra User Attributes:</strong></div></td>
										<td>
											<div> <input class="mo_ldap_table_textbox " type="text" id="mo_ldap_cloud_extra_user_attributes" hidden name="mo_ldap_cloud_extra_user_attributes" placeholder="sAMAccountName,cn"  value="<?php echo ( ! empty( $extra_user_attribute ) ) ? esc_attr( $extra_user_attribute ) : ''; ?>" title="Enter comma-seperated User Attributes eg sAMAccountName,mail,cn" style="width:300px"></div>
										</td>
									</tr>
									<tr>
										<td></td>
										<td><div id='mo_ldap_cloud_extra_user_attribute_div' hidden><em>Note : In case of multiple attributes use comma (',') to seperate.</em></div></td>
									</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
					<tr>
						<td></td>
						<td colspan="2">
							<div id="ldap_cloud_search_filter_div" style="display: block;">
								<table>
									<tr></tr>
									<div id = "mo_ldap_cloud_user_attributes" style="display: none">
										<tr>
											<td>
												<div id="mo_ldap_cloud_search_filter_div">
													<strong>Custom Search Filter:<span class="mo_ldap_cloud_required_attr">*</span></strong>
												</div>
											</td>
											<td>
												<div id="mo_ldap_cloud_search_filter_div">
													<input class="mo_ldap_table_textbox"
														type="text" id="search_filter" name="search_filter" placeholder="(&(|(objectClass=person)(objectClass=user))(cn=?))"
														<?php
														if ( '1' === $mo_filter_check ) {
															echo 'required';
														}
														?>
														value="<?php echo esc_attr( $search_filter ); ?>"
														pattern=".*\?.*"
														title="Must contain Question Mark(?) for attributes you want to match e.g. (&(|(objectClass=person)(objectClass=user))(uid=?))"/></div>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<div id="mo_ldap_cloud_custom_filter_notice">
													<em>This field is important for two reasons. <br>1. While
														searching for users, this is the attribute that is going to be matched
														to see if the user exists. <br>2. If you want your users to login with
														their username or firstname.lastname or email - you need to specify
														those options in this field. e.g. <strong>(&(|(objectClass=person)(objectClass=user))(&lt;LDAP_ATTRIBUTE&gt;=?))</strong>.
														Replace <strong>&lt;LDAP_ATTRIBUTE&gt;</strong> with the attribute where your
														username is stored.
													</em>
												</div>
											</td>
										</tr>
									</div>
								</table>
								<p id="mo_ldap_cloud_search_filter_security_text" style="font-size: 13px; color: red; font-size: 500;">Note: Using objectClass=* may lead to security vulnerabilities. It is recommended to use a more restrictive search filter.</p>
							</div>
						</td>
					</tr>
					<tr><td>&nbsp;</td></tr>
					<tr>
					</table>
					<table>
						<td>
							<div>
								<input type="submit" id="mo_ldap_cloud_test_connection_button" class="mo_ldap_cloud_save_user_mapping" value="Test Connection & Save" onclick="moCloudFilterCheck()" />
								<button type="button" id="conn_help_cloud" class="mo_ldap_cloud_troubleshooting_btn mo_ldap_cloud_wireframe_btn" > Troubleshooting </button>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" id="conn_troubleshoot_cloud" hidden>
							<div class="mo_ldap_cloud_troubleshoot_desc">
								<p>
									<strong>Are you having trouble connecting to your LDAP server from this plugin?</strong>
									<ol>
										<li>Please check to make sure that all the values entered in the <strong>LDAP Connection Information</strong> section are correct.</li>
										<li>If all those values are correct, then you need to make sure that if there is a firewall, you open the firewall to allow incoming requests to your LDAP. Please open port 389(636 for SSL or ldaps). Host - 52.55.147.107 - This is the host from where the LDAP connection as well as authentication requests are going to be made.</li>
										<li>If you are still having problems, submit a query using the support panel on the right hand side.</li>
									</ol>
								</p>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<script>
				jQuery("#mo_ldap_protocol").change(function() {
					var current_selected_protocol_name = jQuery("#mo_ldap_protocol").val();
					var port_number_field = document.getElementById("mo_ldap_server_port_no");
					var ldap_port_number_value = document.getElementById("mo_ldap_ldap_server_port_no").value;
					var ldaps_port_number_value = document.getElementById("mo_ldap_ldaps_server_port_no").value;
					if (current_selected_protocol_name == "ldaps") {
						port_number_field.value = ldaps_port_number_value;
					} else {
							port_number_field.value = ldap_port_number_value;
					}
				});
				<?php
				if ( ! $is_customer_registered ) {
					?>
					jQuery( document ).ready(function() {
						jQuery("#is_gateway_configured :input").prop("disabled", true);
						jQuery("#mo_ldap_cloud_ldap_config_form :input").prop("disabled", true);
					});
					<?php
				}
				?>
			</script>
		</form>
	</div>
</div>

<div class="mo_ldap_small_layout" id="test-authentication">
	<form id="mo_ldap_cloud_test_auth_form" name="mo_ldap_cloud_test_auth_form" method="post" action="">
		<?php wp_nonce_field( 'mo_ldap_cloud_test_auth_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_test_auth" />
		<h2>Test Authentication</h2>
		<hr><br>	
		<div id="test_conn_msg"></div>
		<div id="panel1">
			<table aria-hidden="true" class="mo_ldap_settings_table mo_ldap_cloud_contact_us_container">
				<tr>
					<td class="mo_ldap_cloud_conf_label mo_ldap_cloud_left_section"><label for="ldap_username" class="mo_ldap_input_label_text">Username: <span class="mo_ldap_cloud_required_attr">*</span></label></td>
					<td>
						<input id="mo_ldap_cloud_test_auth_username_field" class="mo_ldap_cloud_standard_input mo_ldap_cloud_user_credentials" type="text" name="test_username" required placeholder="Enter username" />
					</td>
				</tr>
				<br>	
				<tr>
					<td class="mo_ldap_cloud_conf_label mo_ldap_cloud_left_section"><label for="ldap_user_password" class="mo_ldap_input_label_text">Password: <span class="mo_ldap_cloud_required_attr">*</span></label></td>
					<td ><div id="ldap_server_password">
						<input class="mo_ldap_cloud_standard_input mo_ldap_cloud_user_credentials" type="password" name="test_password" required placeholder="Enter password" id="ldap_cloud_test_auth_password_field" />
					</div></td>
				</tr>
			</table>
			<table>
				<tr>
					<td>
						<div class="mo_ldap_cloud_test_btns_container">
							<div>
								<input type="submit" id="mo_ldap_cloud_test_auth_button" class="mo_ldap_cloud_save_user_mapping"  value="Test Authentication" />
								<button type="button" id="auth_help_cloud" class="mo_ldap_cloud_troubleshooting_btn mo_ldap_cloud_wireframe_btn"> Troubleshooting</button>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" id="auth_troubleshoot_cloud" hidden>
						<div class="mo_ldap_cloud_troubleshoot_desc">
							<p>
								<strong>User is not getting authenticated? Check the following:</strong>
								<ol>
									<li>The username-password you are entering is correct.</li>
									<li>The user is present in the search bases you have specified against <strong>SearchBase(s)</strong> above.</li>
								</ol>
								</p>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</form>
</div>
