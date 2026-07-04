<?php
/**
 * Display plugin header information.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="mo_ldap_cloud_main_head" >
	<div class="mo_ldap_cloud_title_container">
		<div class="mo_ldap_cloud_title">
			<img src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'logo.png' ); ?>" height="65px" width="65px"><div class="mo_ldap_cloud_title_text"> LDAP/Active Directory Login for Cloud & Shared Hosting Platforms </div>
		</div>
	</div>
	<div class="mo_ldap_cloud_header_buttons_section">
		<div class="mo_ldap_cloud_column_flex_container mo_ldap_cloud_gap_20 mo_ldap_cloud_vertical_line">
			<a href="
			<?php
			echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) );
			?>
				" class="mo_ldap_cloud_unset_link_affect mo_ldap_cloud_rounded_rectangular_buttons mo_ldap_cloud_horizontal_flex_container">Premium Pricing <span class="mo_ldap_free_trial"><img src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'pricing.svg' ); ?>" height="20px" width="20px"></span></a>
		</div>
		<div class="mo_ldap_cloud_help_links">
		<div><a href="
		<?php
		echo esc_url( add_query_arg( array( 'tab' => 'faqs' ), $filtered_current_page_url ) );
		?>
		" class="mo_ldap_cloud_unset_link_affect">FAQs</a></div>
			<div id="mo_ldap_cloud_documentation_section" class="mo_ldap_cloud_position_relative">
				<div id="mo_ldap_cloud_documentation_dropdown" class="mo_ldap_cloud_documentation_dropdown mo_ldap_cloud_horizontal_flex_container mo_ldap_cloud_content_start mo_ldap_cloud_cursor_pointer">
					<div>Documentation</div> 
					<svg id="mo_ldap_cloud_doc_dropdown" style="margin-left: 5%;" viewBox="0 0 448 512" height="15px" width="15px" fill="#fff" class="mo_ldap_cloud_reverse_rotate">
						<path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"/>
					</svg>
				</div>
				<div id="mo_ldap_cloud_absolute_documentation_box">
					<div class="mo_ldap_cloud_documentation_box">
						<div><a href="https://plugins.miniorange.com/step-by-step-guide-for-wordpress-ldap-login-cloud" target="_blank" class="mo_ldap_cloud_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'doc.svg' ); ?>" height="20px" width="20px"></span><div>Setup LDAP/AD Cloud Plugin</div></a></div>
						<div><a href="https://www.youtube.com/watch?v=6OkcHs-Kdx4" target="_blank" class="mo_ldap_cloud_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'video.svg' ); ?>" height="20px" width="20px"></span><div>LDAP/AD Cloud Plugin Setup</div></a></div>
						<div><a href="https://www.miniorange.com/guide-to-setup-ldaps-on-windows-server" target="_blank" class="mo_ldap_cloud_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'doc.svg' ); ?>" height="20px" width="20px"></span><div>Setup LDAPS connection</div></a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="mo_ldap_cloud_column_flex_container mo_ldap_cloud_gap_20">
			<a href="
			<?php
			echo esc_url( add_query_arg( array( 'tab' => 'account' ), $filtered_current_page_url ) );
			?>
			" class="mo_ldap_cloud_my_account_styles mo_ldap_cloud_horizontal_flex_container mo_ldap_cloud_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'account.svg' ); ?>" height="18px" width="18px"></span> My Account</a>
			<div class="mo_ldap_cloud_support_icons_container">
				<div class="mo_ldap_cloud_support_icon mo_ldap_cloud_horizontal_flex_container" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_clicked(this, '')" >
				Contact Us
				<span><img src="
				<?php
				echo esc_url( MO_LDAP_CLOUD_IMAGES . 'mail.svg' );
				?>
				" height="18px" width="18px">
				</span></div>
			</div>
		</div>
	</div>
</div>
<?php

if ( ! $utils::is_extension_installed( 'openssl' ) ) {
	?>
		<div class="notice notice-error is-dismissible">
		<p style="color:#FF0000">(Warning: <a target="_blank" rel="noopener" href="http://php.net/manual/en/openssl.installation.php">PHP OpenSSL extension</a> is not installed or disabled)</p>
		</div>
	<?php
}
?>

<div id="mo_ldap_cloud_contact_us_box" class="mo_ldap_cloud_popup_box mo_ldap_cloud_contact_us_popup mo_ldap_cloud_d_none mo_ldap_cloud_contact_us_box_resize" style="padding: 1% 0% 1% 1%;">
	<div class="mo_ldap_cloud_cross_button" type="button" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_cancel_remove(this)">+</div>
	<div class="mo_ldap_cloud_contact_us_container"> 
		<div class="mo_ldap_cloud_popup_div">	
			<div class="mo_ldap_cloud_popup_title mo_ldap_cloud_contact_us_heading">
				Contact Us
			</div>
			<div class="mo_ldap_cloud_popup_description">
				<span>Need help with the plugin configuration? Just send us a query from below form.</span>
			</div>
			<div>
				<form name="mo_ldap_cloud_contact_us_form" method="post" action="">
					<input type="hidden" name="option" value="mo_cloud_ldap_login_send_query"/>
					<?php wp_nonce_field( 'mo_ldap_cloud_login_send_query_nonce' ); ?>
					<div>
						<input type="email" class="mo_ldap_cloud_email_pop_up_input_field mo_ldap_cloud_query_form_email_field" id="mo_ldap_cloud_query_email" name="query_email" value="<?php echo esc_attr( get_option( 'mo_ldap_admin_email' ) ); ?>" placeholder="Enter your email" required>
						<div class="mo_ldap_cloud_query_phone_container">
							<input type="text" class="mo_ldap_cloud_query_phone_input mo_ldap_cloud_phone_pop_up_input_field" name="query_phone" id="mo_ldap_cloud_query_phone" value="<?php echo esc_attr( get_option( 'mo_ldap_cloud_admin_phone' ) ); ?>" placeholder="Enter your phone"/>
						</div>
						<textarea id="mo_ldap_cloud_query" name="query" class="mo_ldap_cloud_email_pop_up_input_field mo_ldap_cloud_full_width_input" cols="52" rows="4"  placeholder="Write your query here" required ></textarea>	
						<div class="mo_ldap_cloud_horizontal_flex_container mo_ldap_cloud_send_config_toggle mo_ldap_cloud_query_form_email_field" >
							<input type="checkbox" id="mo_ldap_cloud_setup_call" name="mo_ldap_cloud_setup_call" class="mo_ldap_cloud_toggle_switch_hide" onChange="mo_ldap_cloud_display_setup_call_details()"/>
							<label for="mo_ldap_cloud_setup_call" class="mo_ldap_cloud_toggle_switch"></label>
							<label for="mo_ldap_cloud_setup_call" class="mo_ldap_cloud_d_inline">
								Schedule a Call
							</label>
						</div>
						<div id="mo_ldap_cloud_setup_call_details_div" class="mo_ldap_cloud_d_none">
							<div class="mo_ldap_cloud_setup_call_timezone">
								<label >Timezone:<span class="mo_ldap_cloud_input_label_text">*</span></label>
								<select class="mo_ldap_cloud_pop_up_email_input_field mo_ldap_cloud_setup_call_timezone_cloud" name="ldap_setup_call_timezone_cloud" >
									<option value="" selected disabled>---------Select your timezone--------</option>
									<?php
									foreach ( $zones as $zone => $value ) {
										if ( strcasecmp( $value, 'Etc/GMT' ) === 0 ) {
											?>
											<option value="<?php echo esc_attr( $zone ) . ' ' . esc_attr( $value ); ?>" selected><?php echo esc_html( $zone ); ?></option>
											<?php
										} else {
											?>
											<option value="<?php echo esc_attr( $zone ) . ' ' . esc_attr( $value ); ?>"><?php echo esc_html( $zone ); ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<div class="mo_ldap_cloud_setup_call_timezone">
								<label >Date:<span class="mo_ldap_cloud_input_label_text">*</span></label>
								<div class="mo_ldap_cloud_horizontal_flex_container mo_ldap_cloud_timezone_input_container">
									<input type="date" id="mo_ldap_cloud_datepicker" placeholder="Select Meeting Date" autocomplete="off" name="mo_ldap_setup_call_date_cloud" class="mo_ldap_cloud_email_pop_up_input_field" required >
									<input type="time" id="mo_ldap_cloud_timepicker" value='now' placeholder="Select Meeting Time" autocomplete="off" class="mo_ldap_cloud_email_pop_up_input_field" name="mo_ldap_setup_call_time_cloud" required >
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" value="<?php echo esc_attr( get_option( 'mo_ldap_server_url' ) ? $utils::decrypt( get_option( 'mo_ldap_server_url' ) ) : '' ); ?>" >
					<div class="mo_ldap_cloud_horizontal_flex_container mo_ldap_cloud_query_form_email_field" >
						<input type="submit" name="send_query" value="Submit Query" class="mo_ldap_cloud_save_user_mapping" />
						<button type="button" class="mo_ldap_cloud_cancel_button" data-id="mo_ldap_cloud_contact_us_box" onclick="mo_ldap_cloud_popup_card_cancel_remove(this)">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<div class="mo_ldap_cloud_overlay_back mo_ldap_cloud_d_none" id="mo_ldap_cloud_overlay"></div>


