<?php
/**
 * Display deactivation Feedback form.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_SERVER['PHP_SELF'] ) && ( 'plugins.php' !== basename( esc_url_raw( wp_unslash( $_SERVER['PHP_SELF'] ) ) ) ) ) {
	return;
}

wp_enqueue_style( 'mo_ldap_admin_plugins_page_style_', MO_LDAP_CLOUD_INCLUDES . 'css/mo_ldap_plugins_page_cloud.min.css', array(), MO_LDAP_CLOUD_VERSION );
?>

</head>
<body>
	<div id="LdapModalCloud" class="mo_ldap_modal_feedback_cloud"  style="width:90%; margin-left:12%; margin-top:5%; text-align:center">
		<div class="moldap-modal-contatiner-feedback" style="color:black"></div>
		<div class="mo_ldap_cloud_modal_content_feedback_cloud" style="width:50%;">
			<h3 style="margin: 2%; text-align:center;"><strong>Your feedback</strong><button class="close_feedback_form" onclick="getElementById('LdapModalCloud').style.display = 'none'">X</button></h3>
			<hr style="width:75%;">

			<form name="f2" method="post" action="" id="mo_ldap_feedback_cloud">
				<?php wp_nonce_field( 'mo_ldap_cloud_feedback_cloud_nonce' ); ?>
				<input type="hidden" name="option" value="mo_ldap_feedback_cloud"/>
				<div>
					<p style="margin:2%">
					<h4 style="margin: 2%; text-align:center;">Please help us improve our plugin by giving your opinion.<br></h4>
					<div id="smi_rate_cloud" style="text-align:center">
						<input type="radio" name="rate_cloud" id="angry_cloud" value="1"/>
						<label for="angry_cloud"><img class="sm_cloud" alt="" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'angry.webp' ); ?>" />
						</label>

						<input type="radio" name="rate_cloud" id="sad_cloud" value="2"/>
						<label for="sad_cloud"><img class="sm_cloud" alt="" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'sad.webp' ); ?>" />
						</label>

						<input type="radio" name="rate_cloud" id="neutral_cloud" value="3"/>
						<label for="neutral_cloud"><img class="sm_cloud" alt="" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'normal.webp' ); ?>" />
						</label>

						<input type="radio" name="rate_cloud" id="smile_cloud" value="4"/>
						<label for="smile_cloud">
							<img class="sm_cloud" alt="" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'smile.webp' ); ?>" />
						</label>

						<input type="radio" name="rate_cloud" id="happy_cloud" value="5" checked/>
						<label for="happy_cloud"><img class="sm_cloud" alt="" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'happy.webp' ); ?>" />
						</label>

						<div id="outer_cloud" style="visibility:visible"><span id="result_cloud">Thank you for appreciating our work</span></div>
					</div><br>
					<hr style="width:75%;">
					<?php
					$email = get_option( 'mo_ldap_admin_email' );
					if ( empty( $email ) ) {
						$user  = wp_get_current_user();
						$email = $user->user_email;
					}
					?>

					<div style="text-align:center;">
						<div style="display:inline-block; width:60%;">
							<label for="mail"><strong>Email Address:</strong></label>
							<input type="email" id="query_mail_cloud" name="query_mail_cloud" style="text-align:center; border:0px solid black; border-style:solid; background:#f0f3f7; width:15vw;border-radius: 6px;" placeholder="your email address" required value="<?php echo esc_attr( $email ); ?>" readonly="readonly"/>
							<input type="radio" name="edit_cloud" id="edit_cloud" onclick="editName_cloud()" value=""/>
							<label for="edit_cloud"><img class="editable_cloud" alt="" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . '61456.webp' ); ?>" />
							</label>
						</div>

						<br><br>
						<input type="checkbox" name="get_reply_cloud" value="YES">Check this option if you want a miniOrange representative to contact you.</input>
						<br><br>
						<textarea id="query_feedback_cloud" name="query_feedback_cloud" rows="4" style="width: 60%" placeholder="Tell us what happened!" ></textarea>
						<br>
					</div><br>

					<div class="mo_ldap_modal-footer_cloud" style="text-align: center;margin-bottom: 2%">
						<input type="submit" name="miniorange_ldap_feedback_submit" id="miniorange_ldap_feedback_submit" class="button button-primary button-large" value="Submit"/>
						<span width="30%">&nbsp;&nbsp;</span>
						<input type="button" name="miniorange_skip_feedback_cloud" class="button button-large" value="Skip feedback & deactivate" onclick="document.getElementById('mo_ldap_feedback_form_close_cloud').submit();"/>
					</div>
				</div>

				<script>
					function editName_cloud(){
						document.querySelector('#query_mail_cloud').removeAttribute('readonly');
						document.querySelector('#query_mail_cloud').focus();
						return false;
						}
				</script>
				<style>
					.editable_cloud{
						text-align:center;
						width:1em;
						height:1em;
					}
					.sm_cloud {
						text-align:center;
						width: 2vw;
						height: 2vw;
						padding: 1vw;
					}

					input[type=radio] {
						display: none;
					}

					.sm_cloud:hover {
						opacity:0.6;
						cursor: pointer;
					}

					.sm_cloud:active {
						opacity:0.4;
						cursor: pointer;
					}

					input[type=radio]:checked + label > .sm_cloud {
						border: 2px solid #21ecdc;
					}
				</style>
			</form>
			<form name="mo_ldap_feedback_form_close_cloud" method="post" action="" id="mo_ldap_feedback_form_close_cloud">
				<?php wp_nonce_field( 'mo_ldap_cloud_skip_feedback_cloud_nonce' ); ?>
				<input type="hidden" name="option" value="mo_ldap_skip_feedback_cloud"/>
			</form>
		</div>
	</div>
	<script>
		var active_plugins = document.getElementsByClassName('deactivate');
		for (i = 0; i<active_plugins.length;i++) {
			var plugin_deactivate_link = active_plugins.item(i).getElementsByTagName('a').item(0);
			var plugin_name = plugin_deactivate_link.href;
			if (plugin_name.includes('plugin=miniorange-wp-ldap-login')) {
				jQuery(plugin_deactivate_link).click(function () {
				var mo_ldap_modal_cloud = document.getElementById('LdapModalCloud');

				var span = document.getElementsByClassName("mo_ldap_close_cloud")[0];

				mo_ldap_modal_cloud.style.display = "block";

				window.onclick = function (event) {
					if (event.target == mo_ldap_modal_cloud) {
						mo_ldap_modal_cloud.style.display = "none";
					}
				}
				return false;
				});
				break;
			}
		}
	</script>
</body>
