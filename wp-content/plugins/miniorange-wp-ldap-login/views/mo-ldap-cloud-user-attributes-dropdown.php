<?php
/**
 * Display user attributes dropdown.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_attribute      = ( 'user' === $key ) ? 'sAMAccountName' : 'Please Select';
$check_license_dropdown = '';
if ( ! $utils::is_customer_registered() ) {
	$check_license_dropdown = 'disabled';
}

if ( empty( $selected_attributes ) || ! is_array( $selected_attributes ) ) {
	$html = '<div class="multiselect_' . $key . '"><div class="mo_selectBox" onclick="showCheckboxes(\'' . $key . '\')">';
	if ( 'user' !== $key ) {
		$html .= '<select ><option>' . $default_attribute . '</option></select>';
	}
	$html .= '<div class="mo_overSelect"></div></div>';
	$html .= '<div id="' . $key . '_checkboxes">';

	if ( null !== $predefined_attributes ) {
		foreach ( $predefined_attributes as $ldap_attribute ) {
			$ldap_attribute_name  = $ldap_attribute['name'];
			$ldap_attribute_value = $ldap_attribute['value'];

			if ( $is_checked_toggle ) {
				$checked = ( 'samaccountname' === $ldap_attribute_value ) ? 'unchecked' : '';
			} else {
				$checked = ( 'samaccountname' === $ldap_attribute_value ) ? 'checked' : '';
			}
			$multi_checked_class = ( 'checked' === $checked ) ? 'mo_ldap_cloud_multichecked' : '';

			$html .= '<label class = "mo_checkbox_ldap_' . $key . ' ' . $multi_checked_class . '"><input ' . $check_license_dropdown . ' name ="' . $key . '_attribute_text[]" ' . $checked . ' value = "' . $ldap_attribute_value . '" class="mo_ldap_no_checkbox_' . $key . '" type="checkbox" id="' . $ldap_attribute_name . '" style="width: max-content;"/>' . $ldap_attribute_name . '</label>';
		}
	}
	$html .= '</div></div>';
} else {
	$content = '';
	if ( null !== $predefined_attributes ) {
		foreach ( $predefined_attributes as $ldap_attribute ) {
			$matched              = false;
			$ldap_attribute_name  = $ldap_attribute['name'];
			$ldap_attribute_value = $ldap_attribute['value'];
			foreach ( $selected_attributes as $index => $attribute ) {

				if ( $attribute === $ldap_attribute_value ) {
					$matched = true;
				}
			}

			$multi_checked_class = $matched ? 'mo_ldap_cloud_multichecked' : '';
			$checked             = $matched ? 'checked' : '';
			$content            .= '<label class="mo_checkbox_ldap_' . $key . ' ' . $multi_checked_class . '" style="width: max-content;"><input ' . $check_license_dropdown . ' name ="' . $key . '_attribute_text[]" ' . $checked . ' value = "' . $ldap_attribute_value . '" class="mo_ldap_no_checkbox_' . $key . '" type="checkbox" id="' . $ldap_attribute_name . '" />' . $ldap_attribute_name . '</label>';
		}
	}
	$html  = '<div class="multiselect_' . $key . '">';
	$html .= '<div class="mo_selectBox" onclick="showCheckboxes(\'' . $key . '\')">';
	if ( 'user' !== $key ) {
		$html .= '<select ><option>Select User Groups</option></select>';
	}
	$html .= '<div class="mo_overSelect"></div></div><div id="' . $key . '_checkboxes">';
	$html .= $content;
	$html .= '</div></div>';
}

$esc_allowed = array(
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
	'i'      => array(),
	'label'  => array(
		'style' => array(),
		'class' => array(),
	),
	'input'  => array(
		'class'     => array(),
		'style'     => array(),
		'value'     => array(),
		'id'        => array(),
		'name'      => array(),
		'unchecked' => array(),
		'checked'   => array(),
		'type'      => array(),
		'checkbox'  => array(),
	),
	'select' => array(),
	'option' => array(),
	'div'    => array(
		'style' => array(),
		'class' => array(),
		'id'    => array(),
	),
);

echo wp_kses( $html, $esc_allowed );
