<?php
/**
 * Display customer details page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$utils::mo_ldap_cloud_update_customer_license();
$request_uri     = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
$license_details = ! empty( get_option( 'mo_ldap_cloud_license_info' ) ) ? $utils::secure_unserialize_option( $utils::decrypt( get_option( 'mo_ldap_cloud_license_info' ) ) ) : array();

?>
<div class="mo_ldap_cloud_account_detail_page">
	<div class="mo_ldap_cloud_account_detail_header">
		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'default' ), $request_uri ) ); ?>" class="mo_ldap_cloud_unset_link_affect mo_ldap_cloud_horizontal_flex_container">
			<span>
				<svg id="mo_ldap_cloud_dropdown" style="margin-top: 3%;margin-left: 5%;transform: rotate(90deg);" viewBox="0 0 448 512" height="15px" width="15px" fill="#fff" class="mo_ldap_cloud_reverse_rotate">
					<path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"/>
				</svg>
			</span>
		</a>
		<span class="mo_ldap_cloud_account_details">Account Details</span>
	</div>
	<div>
		<div class="mo_ldap_cloud_account_details_container">
			<div class="mo_ldap_cloud_account_detail_table">
				<div class="mo_ldap_cloud_account_info_field">miniOrange Account Email</div>
				<div><?php echo esc_html( get_option( 'mo_ldap_admin_email' ) ); ?></div>
			</div>
			<div class="mo_ldap_cloud_account_detail_table">
				<div class="mo_ldap_cloud_account_info_field">Customer ID</div>
				<div><?php echo esc_html( get_option( 'mo_ldap_admin_customer_key' ) ); ?></div>
			</div>
			<div class="mo_ldap_cloud_account_detail_table">
				<div class="mo_ldap_cloud_account_info_field">Telephone Number</div>
				<div><?php echo esc_html( get_option( 'mo_ldap_cloud_admin_phone' ) ? get_option( 'mo_ldap_admin_phone' ) : '-' ); ?></div>
			</div>
			<?php
			if ( ! empty( $license_details ) ) {
				$plan_name                  = isset( $license_details['trial'] ) ? 'Free Trial' : 'WP LDAP Cloud Premium Plan';
				$mo_ldap_cloud_license_stat = ! empty( get_option( 'mo_ldap_license_status' ) ) ? get_option( 'mo_ldap_license_status' ) : '';
				$license_exp                = 'Expired';
				$support_exp                = 'Expired';
				if ( '1' === $mo_ldap_cloud_license_stat ) {
					$license_exp = gmdate( 'd-M-Y', strtotime( ( $license_details['licenseExpiry'] ) ) );
					$support_exp = gmdate( 'd-M-Y', strtotime( ( $license_details['supportExpiry'] ) ) );
				} elseif ( '0' === $mo_ldap_cloud_license_stat ) {
					$license_exp = '';
					$support_exp = '';
				} elseif ( '-99' === $mo_ldap_cloud_license_stat ) {
					$plan_name   = $license_details['licenseType'];
					$license_exp = '';
					$support_exp = '';
				}
			}
			?>
			<div class="mo_ldap_cloud_account_detail_table">
				<div class="mo_ldap_cloud_account_info_field">Plan Name</div>
				<div><?php echo esc_html( $plan_name ? $plan_name : '-' ); ?></div>
			</div>	
			<div class="mo_ldap_cloud_account_detail_table">
				<div class="mo_ldap_cloud_account_info_field">No of Users</div>
				<div><?php echo esc_html( isset( $license_details['noOfUsers'] ) && ! empty( $license_details['noOfUsers'] ) ? $license_details['noOfUsers'] : '-' ); ?></div>
			</div>
			<div class="mo_ldap_cloud_account_detail_table">
				<div class="mo_ldap_cloud_account_info_field">License Expiry</div>
				<div><?php echo esc_html( $license_exp ); ?></div>
			</div>
			<div class="mo_ldap_cloud_account_detail_table">
				<div class="mo_ldap_cloud_account_info_field">Support Expiry</div>
				<div><?php echo esc_html( $support_exp ); ?></div>
			</div>
		</div>
		<div class= "mo_ldap_cloud_account_details_button_container" >
			<div>
				<form name="mo_ldap_change_account_form" method="post" action="" id="mo_ldap_change_account_form">
					<?php wp_nonce_field( 'mo_ldap_cloud_change_miniorange_account_nonce' ); ?>
					<input type="hidden" name="option" value="mo_ldap_cloud_change_miniorange_account_option"/>
					<input class="mo_ldap_cloud_change_account_button mo_ldap_cloud_save_user_mapping" type="submit" value="Change Account" class="button button-primary-ldap button-large"/>
				</form>
			</div>
			<div class="mo_ldap_cloud_custom_attr_component">
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $request_uri ) ); ?>" type="button" class="mo_ldap_cloud_check_license_btn mo_ldap_cloud_troubleshooting_btn mo_ldap_cloud_wireframe_btn">Check Licensing Plans</a>
			</div>
		</div>
	</div>
</div>

