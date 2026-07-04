<?php
/**
 * Display FAQs Page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="mo_ldap_cloud_page_box">
	<div class="mo_ldap_cloud_account_box_container">
		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'default' ), $request_uri ) ); ?>" class="mo_ldap_cloud_view_faq_page_anchor mo_cloud_ldap_back_btn mo_ldap_cloud_plugin_config_back_btn mo_ldap_cloud_unset_link_affect"><span><img src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'back.svg' ); ?>" height="10px" width="15px"></span>Back to Plugin Configuration</a>
	</div>
	<div class="mo_ldap_cloud_central_header">
		FAQs
	</div>
	<div class="mo_ldap_cloud_faqs_container mo_ldap_cloud_column_flex_container" >
		<?php
		$index = 1;
		foreach ( $faqs as $ques => $answer ) {
			?>
			<div class="mo_ldap_cloud_faq_box" data-faq-id="<?php echo esc_attr( $index ); ?>">
				<div class="mo_ldap_cloud_horizontal_flex_container mo_ldap_cloud_faq_ques_container">
					<div onclick="showFAQbox(this)"><?php echo wp_kses( $ques, $faqs_allowed_tags ); ?></div>
					<div class="mo_ldap_cloud_plus_icon" onclick="showFAQbox(this)">+</div>
				</div>
				<div class="mo_ldap_cloud_answer_section">
					<div>
						<?php echo wp_kses( $answer, $faqs_allowed_tags ); ?>
					</div>
				</div>
			</div>
			<?php
			++$index;
		}
		?>
	</div>
</div>
