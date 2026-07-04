<?php
/**
 * Main plugin controller.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Controllers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use MO_LDAP_CLOUD\Utils\Mo_LDAP_Cloud_Utils;

$controller             = MO_LDAP_CLOUD_DIR . 'controllers/';
$request_uri            = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
$current_page           = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.
$active_tab             = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'default'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended, - Reading GET parameter from the URL for checking the sub-tab name, doesn't require nonce verification.
$zones                  = $timezones::$zones;
$faqs                   = Mo_LDAP_Cloud_Utils::mo_ldap_cloud_get_faqs();
$is_customer_registered = $utils::is_customer_registered();
$faqs_allowed_tags      = array(
	'a'      => array(
		'href'  => array(),
		'title' => array(),
	),
	'br'     => array(),
	'em'     => array(),
	'strong' => array(),
	'b'      => array(),
	'h1'     => array(),
	'h2'     => array(),
	'h3'     => array(),
	'h4'     => array(),
	'h5'     => array(),
	'h6'     => array(),
	'span'   => array(
		'class' => array(),
	),
	'i'      => array(
		'class' => array(),
	),
	'button' => array(
		'id'    => array(),
		'class' => array(),
	),
	'ul'     => array(),
	'ol'     => array(),
	'li'     => array(
		'style' => array(),
	),
);

$filtered_current_page_url = remove_query_arg( array( 'tab', 'subtab', 'step', 'sitetype' ), $request_uri );

if ( 'mo_ldap_cloud_login' === $current_page ) {

	if ( 'faqs' !== $active_tab && 'pricing' !== $active_tab && 'account' !== $active_tab ) {
		$mo_license_status = ! empty( get_option( 'mo_ldap_license_status' ) ) ? get_option( 'mo_ldap_license_status' ) : '';
		if ( '-1' === $mo_license_status ) {
			$licensing_plans_url = esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) );
			$message             = 'Your trial license has expired. To continue using our services <a href="' . esc_url( $licensing_plans_url ) . '">click here</a> and upgrade to our premium plan.';
			echo "<div class='error'> <p>" . wp_kses( $message, MO_LDAP_CLOUD_ESC_ALLOWED ) . '</p></div>';
		}


		$email = ! empty( get_option( 'mo_ldap_admin_email' ) ) ? get_option( 'mo_ldap_admin_email' ) : get_option( 'admin_email' );

		require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-header.php';

		if ( ! $is_customer_registered ) {
			?>
			<div class="modal notice mo_ldap_cloud_notice">
				<div>
					Please <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'account' ), $filtered_current_page_url ) ); ?>">Register or Login with miniOrange</a> to configure the miniOrange LDAP Plugin.		
				</div>
			</div>
			<?php
		}

		?>
		<div class="mo_ldap_cloud_page_container">
		<?php
		require_once MO_LDAP_CLOUD_CONTROLLERS . 'mo-ldap-cloud-pages-controller.php';
		?>
	</div>
		<?php

	} elseif ( 'pricing' === $active_tab ) {

		?>
			<nav class="main-nav">
				<div class="mo_ldap_cloud_main_nav_div" ><a  class="add-new-h2 mo_ldap_cloud_main_nav_anchor" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'default' ), $filtered_current_page_url ) ); ?>"><button id="Back-To-Plugin-Configuration" type="button" value="Back-To-Plugin-Configuration" class="button button-primary button-large mo_ldap_cloud_button_next"><span class="dashicons dashicons-arrow-left-alt" style="vertical-align: middle;"></span> Plugin Configuration</button></a> </div>
				<div class="licensing-page-heading"><h2 class="mo_ldap_cloud_license_page_head">miniOrange LDAP/Active Directory Login for Cloud & Shared Hosting Platforms</h2></div>
				<div style=" visibility: <?php echo ( 'pricing' === $active_tab ) ? 'visible' : 'hidden'; ?>" >
					<input type="button"
						<?php
						if ( ! $is_customer_registered ) {
							echo 'disabled';
						}
						?>
						name="check_btn" id="check_license_btn" class="button button-primary button-large mo_ldap_cloud_check_license_button" value="Check License" onclick="document.forms['mo_ldap_check_license_form'].submit();"
					/>
				</div>
			</nav>
			<?php
			require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-pricing-page.php';
	} elseif ( 'faqs' === $active_tab ) {
		require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-faqs-page.php';
	} else {
		?>
			<br>
		<?php
		if ( ! empty( get_option( 'mo_ldap_verify_customer' ) ) && 'true' === get_option( 'mo_ldap_verify_customer' ) ) {
			require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-login-page.php';
		} elseif ( ( ! empty( get_option( 'mo_ldap_admin_email' ) ) && empty( trim( get_option( 'mo_ldap_admin_email' ) ) ) ) && ( ! empty( get_option( 'mo_ldap_admin_api_key' ) ) && empty( trim( get_option( 'mo_ldap_admin_api_key' ) ) ) ) && ( ! empty( get_option( 'mo_ldap_new_registration' ) ) && 'true' !== get_option( 'mo_ldap_new_registration' ) ) ) {
			require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-login-page.php';
		} elseif ( ( ! empty( get_option( 'mo_ldap_registration_status' ) ) && 'MO_OTP_DELIVERED_SUCCESS' === get_option( 'mo_ldap_registration_status' ) ) || ( ! empty( get_option( 'mo_ldap_registration_status' ) ) && 'MO_OTP_VALIDATION_FAILURE' === get_option( 'mo_ldap_registration_status' ) ) || ( ! empty( get_option( 'mo_ldap_registration_status' ) ) && 'MO_OTP_DELIVERED_FAILURE' === get_option( 'mo_ldap_registration_status' ) ) ) {
			require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-otp-verification-page.php';
		} elseif ( ! $is_customer_registered ) {
			require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-customer-registration-page.php';
		} elseif ( 'account' === $active_tab ) {
				require_once MO_LDAP_CLOUD_VIEWS . 'mo-ldap-cloud-customer-details-page.php';
		}
	}
}
