<?php
/**
 * Display licensing page.
 *
 * @package miniOrange_LDAP_AD_Cloud_Integration
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plan = $utils::mo_ldap_is_customer_validated();

if ( ! $utils::is_customer_registered() ) {
	$content_for_free_plan_tile = 'You will be in this plan upon Registration';
	$content_color              = '#2f6062';
} elseif ( ! $plan ) {

		$mo_license_status = ! empty( get_option( 'mo_ldap_license_status' ) ) ? get_option( 'mo_ldap_license_status' ) : '';

	if ( '-1' === $mo_license_status ) {
		$content_for_free_plan_tile = 'Your trial license has expired.';
		$content_color              = '#DF0D0D';
	} else {
		$content_for_free_plan_tile = 'Click on check license for more info';
		$content_color              = '#2f6062';
	}
} else {
	$content_for_free_plan_tile = '';
	$content_color              = '#2f6062';
}

wp_enqueue_style( 'cloud_grid_layout', MO_LDAP_CLOUD_INCLUDES . 'css/grid.min.css', array(), MO_LDAP_CLOUD_VERSION );
wp_enqueue_style( 'cloud_font_awesome_icons', MO_LDAP_CLOUD_INCLUDES . 'css/font-awesome.min.css', array(), MO_LDAP_CLOUD_VERSION );

echo '<style>.update-nag,  .error, .is-dismissible, .notice, .notice-error { display: none; }</style>';

?>
<style>
	*, *::after, *::before {
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	html {
		font-size: 62.5%;
	}
	html * {
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}
	.pricing-text-line{
		font-size:16px;
		font-weight:500;
		font-style:italic;
	}
	.main-nav{
		margin-top:15px;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	.licensing-page-heading{
		display:block;
		text-align: center;
		padding: 0 10px;
	}
	.pricing-container {
		font-size: 1.6rem;
		font-family: "Open Sans", sans-serif;
		color: #fff;
	}
	.section-features{
		background-color:#f9f9f9;
		padding:20px 40px;
	}
	.feature{
		margin:10px 10px;
		line-height:1.6;
		text-align:center;
		color:#666;
		font-size:16px;
	}
	.feature-heading{
		font-size: 28px;
		line-height: 1.1;
		text-align: center;
		font-weight:600;
	}
	.feature-row{
		margin:50px 0;
	}
	.feature-box{
		text-align:center;
	}
	.feature-box:hover {
	animation: shake 0.9s;
	}
	@keyframes shake{ 
		0%{ 
		transform: translateX(0) 
		} 
		25%{ 
		transform: translateX(10px); 
		} 
		50%{ 
		transform: translateX(-10px); 
		} 
		100%{ 
		transform: translateX(0px); 
		} 
	} 
	.mo_ldap_small_layout,.mo_ldap_cloud_support_layout,.mo2f_container .nav-tab-wrapper, .mo_ldap_cloud_table_layout{
		display:none;
	}
	.call-setup-divbox-cloud-ldap,.call-setup-div-cloud-ldap{
		display:none;
	}
	.cd-header{
		margin-top:100px;
	}
	.cd-header>h1{
		text-align: center;
		color: #FFFFFF;
		font-size: 3.2rem;
	}
	.cd-pricing-container {
		width: 100%;
		max-width: 1360px;
		/* margin: 4em auto; */
		margin-top: 0em;
		margin-right: auto;
		margin-left: auto;
	}
	.cd-pricing-list {
		margin: 2em 0 0;
		list-style-type:none;
		display:flex;
		justify-content: center;
	}
	.cd-pricing-list > li {
		position: relative;
		margin-bottom: 1em;
	}
	@media only screen and (min-width: 768px) {
		.cd-pricing-list {
			margin: 3em;
		}
		.cd-pricing-list:after {
			content: "";
			display: table;
			clear: both;
		}
		.cd-pricing-list > li {
			width: 35.3333333333%;
			float: left;
		}
		.cd-has-margins .cd-pricing-list > li {
			width: 25%;
			float: left;
			margin-right: 5%;
		}
		.cd-has-margins .cd-pricing-list > li:last-of-type {
			margin-right: 0;
		}
	}
	.cd-pricing-wrapper {
		/* this is the item that rotates */
		overflow: visible;
		position: relative;
	}
	.touch .cd-pricing-wrapper {
		-webkit-perspective: 2000px;
		-moz-perspective: 2000px;
		perspective: 2000px;
	}
	.cd-pricing-wrapper > li {
		background-color: #FFFFFF;
		-webkit-backface-visibility: hidden;
		backface-visibility: hidden;
		outline: 1px solid transparent;
	}
	.cd-pricing-wrapper > li::after {
		content: '';
		position: absolute;
		top: 0;
		right: 0;
		height: 100%;
		width: 50px;
		pointer-events: none;
		background: -webkit-linear-gradient( right , #FFFFFF, rgba(255, 255, 255, 0));
		background: linear-gradient(to left, #FFFFFF, rgba(255, 255, 255, 0));
	}
	.cd-pricing-wrapper > li.is-ended::after {
		display: none;
	}
	.cd-pricing-wrapper .is-visible {
		position: relative;
		background-color: #fff;
	}
	.cd-pricing-wrapper .is-hidden {
		position: absolute;
		top: 0;
		left: 0;
		height: 100%;
		width: 100%;
		z-index: 1;
		-webkit-transform: rotateY(180deg);
		-moz-transform: rotateY(180deg);
		-ms-transform: rotateY(180deg);
		-o-transform: rotateY(180deg);
		transform: rotateY(180deg);
	}
	.cd-pricing-wrapper .is-hidden2 {
		position: absolute;
		top: 0;
		left: 0;
		height: 100%;
		width: 100%;
		z-index: 1;
		-webkit-transform: rotateY(180deg);
		-moz-transform: rotateY(180deg);
		-ms-transform: rotateY(180deg);
		-o-transform: rotateY(180deg);
		transform: rotateY(180deg);
	}
	.cd-pricing-wrapper .is-selected {
		z-index: 3 !important;
	}
	@media only screen and (min-width: 768px) {
		.cd-pricing-wrapper > li::before {
			content: '';
			position: absolute;
			z-index: 6;
			left: -1px;
			top: 50%;
			bottom: auto;
			-webkit-transform: translateY(-50%);
			-moz-transform: translateY(-50%);
			-ms-transform: translateY(-50%);
			-o-transform: translateY(-50%);
			transform: translateY(-50%);
			height: 50%;
			width: 1px;
			background-color: #b1d6e8;
		}
		.cd-pricing-wrapper > li::after {
			display: none;
		}
		.cd-popular .cd-pricing-wrapper > li {
			box-shadow: inset 0 0 0 15px #f2881be0;
		}
		.cd-black .cd-pricing-wrapper > li {
			box-shadow: inset 0 0 0 3px #000000;
		}
		.cd-has-margins .cd-pricing-wrapper > li, .cd-has-margins .cd-popular .cd-pricing-wrapper > li {
			box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
		}
		.cd-secondary-theme .cd-pricing-wrapper > li {
			background: #3aa0d1;
			background: -webkit-linear-gradient( bottom , #3aa0d1, #3ad2d1);
			background: linear-gradient(to top, #3aa0d1, #3ad2d1);
		}
		.cd-secondary-theme .cd-popular .cd-pricing-wrapper > li {
			background: #e97d68;
			background: -webkit-linear-gradient( bottom , #e97d68, #e99b68);
			background: linear-gradient(to top, #e97d68, #e99b68);
			box-shadow: none;
		}
		:nth-of-type(1) > .cd-pricing-wrapper > li::before {
			/* hide table separator for the first table */
			display: none;
		}
		:nth-of-type(2) > .cd-pricing-wrapper > li::before {
			display: table-row;
		}
		.cd-has-margins .cd-pricing-wrapper > li {
			border-radius: 4px 4px 6px 6px;
		}
		.cd-has-margins .cd-pricing-wrapper > li::before {
			display: none;
		}
	}
	@media only screen and (min-width: 1500px) {
		.cd-full-width .cd-pricing-wrapper > li {
			padding: 2.5em 0;
		}
	}
	.no-js .cd-pricing-wrapper .is-hidden {
		position: relative;
		-webkit-transform: rotateY(0);
		-moz-transform: rotateY(0);
		-ms-transform: rotateY(0);
		-o-transform: rotateY(0);
		transform: rotateY(0);
		margin-top: 1em;
	}
	.no-js .cd-pricing-wrapper .is-hidden2 {
		position: relative;
		-webkit-transform: rotateY(0);
		-moz-transform: rotateY(0);
		-ms-transform: rotateY(0);
		-o-transform: rotateY(0);
		transform: rotateY(0);
		margin-top: 1em;
	}
	@media only screen and (min-width: 768px) {
		.cd-popular .cd-pricing-wrapper > li::before {
			display: none;
		}
		.cd-popular + li .cd-pricing-wrapper > li::before {
			display: none;
		}
	}
	.cd-pricing-header {
		position: relative;
		height: 80px;
		padding: 1em;
		pointer-events: none;
		background-color: #3aa0d1;
		color: #FFFFFF;
	}
	.cd-pricing-header h2 {
		margin-bottom: 3px;
		font-weight: 700;
		text-transform: uppercase;
	}
	.cd-popular .cd-pricing-header {
		background-color: transparent;
		color:#e67e22;
	}
	.cd-black .cd-pricing-header:hover{
		color:#fff;
		background-color:#e97d68;
	}
	.cd-black .cd-pricing-header:hover h2 {
		color:#fff;
	}
	.cd-popular .cd-pricing-header:hover .cd-price{
		color:#fff !important;
	}
	.cd-popular .cd-pricing-header:hover .trial-status {
		color:#fff !important;
	}
	.cd-popular .cd-pricing-header .cd-price {
		color:#2f6062;
	}
	@media only screen and (min-width: 768px) {
		.cd-pricing-header {
			height: auto;
			padding: 1.9em 0.9em 1.6em;
			pointer-events: auto;
			text-align: center;
			color: #2f6062;
			background-color: transparent;
		}
		.cd-popular .cd-pricing-header {
			color: #e67e22;
			background-color: #fff;
		}
		.cd-popular .cd-pricing-header:hover {
			background-color: #e97d68;
			color:#fff;
		}
		.cd-popular .cd-pricing-header:hover h2 {
			color:#fff;
		}
		.cd-secondary-theme .cd-pricing-header {
			color: #FFFFFF;
		}
		.cd-pricing-header h2 {
			font-size: 1.8rem;
			letter-spacing: 2px;
		}
	}
	.cd-popular .cd-duration {
		color: #f3b6ab;
	}
	@media only screen and (min-width: 768px) {
		.cd-value {
			font-size: 4rem;
			font-weight: 300;
		}
		.cd-contact {
			font-size: 3rem;
		}
		.cd-popular .cd-currency, .cd-popular .cd-duration {
			color: #e97d68;
		}
		.cd-secondary-theme .cd-currency, .cd-secondary-theme .cd-duration {
			color: #2e80a7;
		}
		.cd-secondary-theme .cd-popular .cd-currency, .cd-secondary-theme .cd-popular .cd-duration {
			color: #ba6453;
		}
	}
	.cd-pricing-body {
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
	}
	.is-switched .cd-pricing-body {
		overflow: hidden;
	}
	@media only screen and (min-width: 768px) {
		.cd-pricing-body {
			overflow-x: visible;
		}
	}
	.cd-pricing-features {
		width: 600px;
	}
	.cd-pricing-features:after {
		content: "";
		display: table;
		clear: both;
	}
	.cd-pricing-features li {
		width: 100px;
		float: left;
		padding: 1.6em 1em;
		font-size: 1.4rem;
		text-align: center;
		white-space: initial;
		line-height:1.4em;
		text-overflow: ellipsis;
		color: black;
		overflow-wrap: break-word;
		margin: 0 !important;
	}
	.cd-pricing-features em {
		display: block;
		margin-bottom: 5px;
		font-weight: 600;
		color: black;
	}
	@media only screen and (min-width: 768px) {
		.cd-pricing-features {
			width: auto;
			word-wrap: break-word;
		}
		.cd-pricing-features li {
			float: none;
			width: auto;
			padding: 1em;
			word-wrap: break-word;
		}
		.cd-popular .cd-pricing-features li {
			margin: 0 3px;
		}
		.cd-pricing-features li:nth-of-type(2n+1) {
			background-color: rgba(23, 61, 80, 0.06);
		}
		.cd-pricing-features em {
			display: inline-block;
			margin-bottom: 0;
			word-wrap: break-word;
		}
		.cd-has-margins .cd-popular .cd-pricing-features li, .cd-secondary-theme .cd-popular .cd-pricing-features li {
			margin: 0;
		}
		.cd-secondary-theme .cd-pricing-features li {
			color: #FFFFFF;
		}
		.cd-secondary-theme .cd-pricing-features li:nth-of-type(2n+1) {
			background-color: transparent;
		}
	}
	.cd-pricing-footer {
		position: absolute;
		z-index: 1;
		top: 0;
		left: 0;
		height: 80px;
		width: 100%;
	}
	.cd-pricing-footer::after {
		content: '';
		position: absolute;
		right: 1em;
		top: 50%;
		bottom: auto;
		-webkit-transform: translateY(-50%);
		-moz-transform: translateY(-50%);
		-ms-transform: translateY(-50%);
		-o-transform: translateY(-50%);
		transform: translateY(-50%);
		height: 20px;
		width: 20px;
		background: url(../img/cd-icon-small-arrow.svg);
	}
	@media only screen and (min-width: 768px) {
		.cd-pricing-footer {
			position: relative;
			height: auto;
			text-align: center;
		}
		.cd-pricing-footer::after {
			display: none;
		}
		.cd-has-margins .cd-pricing-footer {
			padding-bottom: 0;
		}
	}
	.cd-select {
		position: relative;
		z-index: 1;
		display: block;
		height: 100%;
		/* hide button text on mobile */
		overflow: hidden;
		text-indent: 100%;
		white-space: nowrap;
		color: transparent;
		text-decoration: none;
		font-weight: 600;
	}
	@media only screen and (min-width: 768px) {
		.cd-select {
			position: static;
			display: inline-block;
			height: auto;
			padding: 1.3em 3em;
			color: #FFFFFF;
			border-radius: 2px;
			background-color: #0c1f28;
			font-size: 1.4rem;
			text-indent: 0;
			text-transform: uppercase;
			letter-spacing: 2px;
		}
		.no-touch .cd-select:hover {
			background-color: #112e3c;
		}
		.cd-popular .cd-select {
			background-color: #0c1f28;
		}
		.no-touch .cd-popular .cd-select:hover {
			background-color: #ec907e;
		}
		.cd-secondary-theme .cd-popular .cd-select {
			background-color: #0c1f28;
		}
		.no-touch .cd-secondary-theme .cd-popular .cd-select:hover {
			background-color: #112e3c;
		}
		.cd-has-margins .cd-select {
			display: block;
			padding: 1.7em 0;
			border-radius: 0 0 4px 4px;
		}
	}
	.tab-content {
		margin-left: 0%!important;
		margin-top: 0%!important;
	}
	.tab-content>.active {
		width: 100% !important;
	}
	.nav-pills>li{
		width:250px;
	}
	.nav-pills>li+li {
		margin-left: 0px;
	}
	.nav-pills>li.active>a, .nav-pills>li.active>a:hover, .nav-pills>li.active>a:focus,.nav-pills>li.active>a:active{
		color: #1e3334;
		background-color:white;
		height:47px;
	}
	.nav-pills>li>a:hover {
		color:#fff;
		background: #E97D68;
		height:46px;
	}
	.nav-pills>li>a:focus{
		color:#fff;
		background:grey;
		height:47px;
	}
	.nav-pills>li.active{
		background-color: #fff;
	}
	.nav-pills>li>a {
		border-radius: 0px;
		height:47px;
		border-color:#E85700;
		font-weight: 500;
		color: #d3f3d3;
		text-transform:uppercase;
	}
	.ui-slider .ui-slider-handle {
		position: absolute !important;
		z-index: 2 !important;
		width: 3.2em !important;
		height: 2.2em !important;
		cursor: default !important;
		margin: 0 -20px auto !important;
		text-align: center !important;
		line-height: 30px !important;
		color: #FFFFFF !important;
		font-size: 15px !important;
	}
	.ui-state-default,
	.ui-widget-content .ui-state-default {
		background: #393a40 !important;
	}
	.ui-slider .ui-slider-handle {width:2em;left:-.6em;text-decoration:none;text-align:center;}
	.ui-slider-horizontal .ui-slider-handle {
		margin-left: -0.5em !important;
	}
	.ui-slider .ui-slider-handle {
		cursor: pointer;
	}
	.ui-slider a,
	.ui-slider a:focus {
		cursor: pointer;
		outline: none;
	}
	.price, .lead p {
		font-weight: 600;
		font-size: 32px;
		display: inline-block;
		line-height: 60px;
	}
	.price-form label {
		font-weight: 200;
		font-size: 21px;
	}
	.ui-slider-horizontal .ui-slider-handle {
		top: -.6em !important;
	}
	.pricing-tooltip .pricing-tooltiptext {
		visibility: hidden;
		background-color: black;
		line-height: 1.5em;
		font-size:12px;
		min-width: 300px;
		color: rgb(253, 252, 252);
		padding: 10px;
		border-radius: 6px;
		position: absolute;
		z-index: 5;
		text-align: center;
	}
	.pricing-tooltiptext .body{
		font-weight:100;
	}
	.pricing-tooltip:hover .pricing-tooltiptext {
		visibility: visible;
	}
	.cd-pricing-features>li>a{
		color:#E97D68;
	}
	.cd-row .col-md-4, .cd-row .col-md-6 {
		padding-left: 30px!important;
		font-size: 16px;
		padding: 4px;
	}
	.cd-row .col-md-6 {
		width: 60.33333333%;
	}
	.ribbon .ribbon-content:before, .ribbon .ribbon-content:after {
		content: "";
		position: absolute;
		display: block;
		border-style: solid;
		border-color: #804f7c transparent transparent transparent;
		bottom: -1em;
	}
	.ribbon .ribbon-content:before {
		left: 0;
		border-width: 0em 0 0 1em;
	}
	.ribbon .ribbon-content:after {
		right: 0;
		border-width: 0em 1em 0 0;
	}
	.cd-pricing-wrapper:hover .is-visible{
		-webkit-transform: scale(1);
		-ms-transform: scale(1);
		transform: scale(1);
	}
	.recommended .table-buy .pricing-action:hover {
		background: #228799;
	}
	.cd-popular :hover #singlesite_tab.is-visible{
		-webkit-transform: scale(1.03);
		-moz-transform: scale(1.03);
		transform: scale(1.03);
		-webkit-transition: all 0.3s;
		-moz-transition: all 0.3s;
		transition: all 0.3s;
		z-index:2;
	}
	.cd-black :hover #singlesite_tab.is-visible{
		-webkit-transform: scale(1.03);
		-moz-transform: scale(1.03);
		transform: scale(1.03);
		-webkit-transition: all 0.3s;
		-moz-transition: all 0.3s;
		transition: all 0.3s;
		z-index:2;
	}
	.cd-pricing-container {
		/* width: 90%;
		max-width: 1160px;
		margin: 4em auto; */
		width: 100%;
		max-width: 1360px;
		/* margin: 4em auto; */
		margin-top: 0em;
		margin-right: auto;
		margin-left: auto;
	}
	.third-party-title
	{
		text-align: left;
		font-weight: 500;
		padding-top: 8px;
	}
	.ldap-plan-title{
		padding: 35px;
		text-align: center;
		margin-bottom:35px;
	}
	.mo_ldap_wrapper {
		display: flex;
		flex-direction: column;
		width: 99%;
		margin: 0 auto;
	}
	.mo_ldap_cloud_row1 {
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.cd-pricing-wrapper-addons {
		/* this is the item that rotates */
		word-wrap: break-word;
		overflow: visible;
		position: relative;
		width: 32%;
	}
	.touch .cd-pricing-wrapper-addons {
		/* fix a bug on IOS8 - rotating elements dissapear*/
		-webkit-perspective: 2000px;
		-moz-perspective: 2000px;
		perspective: 2000px;
	}
	.cd-pricing-wrapper-addons.is-switched .is-visible {
		/* totate the tables - anticlockwise rotation */
		-webkit-transform: rotateY(180deg);
		-moz-transform: rotateY(180deg);
		-ms-transform: rotateY(180deg);
		-o-transform: rotateY(180deg);
		transform: rotateY(180deg);
		-webkit-animation: cd-rotate 0.5s;
		-moz-animation: cd-rotate 0.5s;
		animation: cd-rotate 0.5s;
	}
	.cd-pricing-wrapper-addons.is-switched .is-hidden {
		/* totate the tables - anticlockwise rotation */
		-webkit-transform: rotateY(0);
		-moz-transform: rotateY(0);
		-ms-transform: rotateY(0);
		-o-transform: rotateY(0);
		transform: rotateY(0);
		-webkit-animation: cd-rotate-inverse 0.5s;
		-moz-animation: cd-rotate-inverse 0.5s;
		animation: cd-rotate-inverse 0.5s;
		opacity: 0;
	}
	.cd-pricing-wrapper-addons.is-switched .is-selected {
		opacity: 1;
	}
	.cd-pricing-wrapper-addons.is-switched.reverse-animation .is-visible {
		/* invert rotation direction - clockwise rotation */
		-webkit-transform: rotateY(-180deg);
		-moz-transform: rotateY(-180deg);
		-ms-transform: rotateY(-180deg);
		-o-transform: rotateY(-180deg);
		transform: rotateY(-180deg);
		-webkit-animation: cd-rotate-back 0.5s;
		-moz-animation: cd-rotate-back 0.5s;
		animation: cd-rotate-back 0.5s;
	}
	.cd-pricing-wrapper-addons.is-switched.reverse-animation .is-hidden {
		/* invert rotation direction - clockwise rotation */
		-webkit-transform: rotateY(0);
		-moz-transform: rotateY(0);
		-ms-transform: rotateY(0);
		-o-transform: rotateY(0);
		transform: rotateY(0);
		-webkit-animation: cd-rotate-inverse-back 0.5s;
		-moz-animation: cd-rotate-inverse-back 0.5s;
		animation: cd-rotate-inverse-back 0.5s;
		opacity: 0;
	}
	.cd-pricing-wrapper-addons.is-switched.reverse-animation .is-selected {
		opacity: 1;
	}
	.cd-pricing-wrapper-addons > li {
		background-color: #FFFFFF;
		-webkit-backface-visibility: hidden;
		backface-visibility: hidden;
		/* Firefox bug - 3D CSS transform, jagged edges */
		outline: 1px solid transparent;
	}
	.cd-pricing-wrapper-addons > li::after {
		/* subtle gradient layer on the right - to indicate it's possible to scroll */
		content: '';
		position: absolute;
		top: 0;
		right: 0;
		height: 100%;
		width: 50px;
		pointer-events: none;
		background: -webkit-linear-gradient( right , #FFFFFF, rgba(255, 255, 255, 0));
		background: linear-gradient(to left, #FFFFFF, rgba(255, 255, 255, 0));
	}
	.cd-pricing-wrapper-addons > li.is-ended::after {
		/* class added in jQuery - remove the gradient layer when it's no longer possible to scroll */
		display: none;
	}
	.cd-pricing-wrapper-addons .is-visible {
		/* the front item, visible by default */
		position: relative;
		background-color: #f2f5f8;
	}
	.cd-pricing-wrapper-addons .is-hidden {
		/* the hidden items, right behind the front one */
		position: absolute;
		top: 0;
		left: 0;
		height: 100%;
		width: 100%;
		z-index: 1;
		-webkit-transform: rotateY(180deg);
		-moz-transform: rotateY(180deg);
		-ms-transform: rotateY(180deg);
		-o-transform: rotateY(180deg);
		transform: rotateY(180deg);
	}
	.cd-pricing-wrapper-addons .is-selected {
		/* the next item that will be visible */
		z-index: 3 !important;
	}
	@media only screen and (min-width: 768px) {
		.cd-pricing-wrapper-addons > li::before {
			/* separator between pricing tables - visible when number of tables > 3 */
			content: '';
			position: absolute;
			z-index: 6;
			left: -1px;
			top: 50%;
			bottom: auto;
			-webkit-transform: translateY(-50%);
			-moz-transform: translateY(-50%);
			-ms-transform: translateY(-50%);
			-o-transform: translateY(-50%);
			transform: translateY(-50%);
			height: 50%;
			width: 1px;
			background-color: #b1d6e8;
		}
		.cd-pricing-wrapper-addons > li::after {
			/* hide gradient layer */
			display: none;
		}
		.cd-popular .cd-pricing-wrapper-addons > li {
			box-shadow: inset 0 0 0 3px #e97d68;
		}
		.cd-has-margins .cd-pricing-wrapper-addons > li, .cd-has-margins .cd-popular .cd-pricing-wrapper-addons > li  {
			box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
		}
		.cd-secondary-theme .cd-pricing-wrapper-addons  > li {
			background: #3aa0d1;
			background: -webkit-linear-gradient( bottom , #3aa0d1, #3ad2d1);
			background: linear-gradient(to top, #3aa0d1, #3ad2d1);
		}
		.cd-secondary-theme .cd-popular .cd-pricing-wrapper-addons > li  {
			background: #e97d68;
			background: -webkit-linear-gradient( bottom , #e97d68, #e99b68);
			background: linear-gradient(to top, #e97d68, #e99b68);
			box-shadow: none;
		}
		.cd-pricing-wrapper-addonsTitle > li{
			background:#c1d2d5;
			/* background: -webkit-linear-gradient( bottom , #e97d68, #e97d68);
			background: linear-gradient(to top, #e97d68, #e97d68);
			box-shadow: none; */
		}
		:nth-of-type(1) > .cd-pricing-wrapper-addons > li::before {
			/* hide table separator for the first table */
			display: none;
		}
		.cd-has-margins .cd-pricing-wrapper-addons > li {
			border-radius: 4px 4px 6px 6px;
		}
		.cd-has-margins .cd-pricing-wrapper-addons > li::before {
			display: none;
		}
	}
	@media only screen and (min-width: 1500px) {
		.cd-full-width .cd-pricing-wrapper-addons > li {
			padding: 2.5em 0;
		}
	}
	.no-js .cd-pricing-wrapper-addons .is-hidden .cd-pricing-wrapper-addonsTitle{
		position: relative;
		-webkit-transform: rotateY(0);
		-moz-transform: rotateY(0);
		-ms-transform: rotateY(0);
		-o-transform: rotateY(0);
		transform: rotateY(0);
		margin-top: 1em;
	}
	@media only screen and (min-width: 768px) {
		.cd-popular .cd-pricing-wrapper-addons > li::before {
			/* hide table separator for .cd-popular table */
			display: none;
		}
		.cd-popular + li .cd-pricing-wrapper-addons > li::before {
			/* hide table separator for tables following .cd-popular table */
			display: none;
		}
	}
	.ldap-addon-box{
		box-shadow: 6px 3px 18px 3px #b4bfc1;
		margin: 15px;
		border: .9px solid whitesmoke;
	}
	.subtitle h3{
		color: black;
		text-align:center;
		font-weight: 500;
		font-size:18px;
		position:absolute;
		line-height:normal;
		padding-left: 18%;
		padding-right: 16%;
	}
	.activeCurrent {
		background-color:#e97d68;
		color: white;
		border-radius: 3px;
		display: inline-block;
		height: auto;
		/* color: blue; */
		vertical-align: top;
		position: relative;
		/* overflow: hidden; */
		/* box-shadow: 0 5px 4px rgba(0, 0, 0, 0.24), 0 5px 6px rgba(0, 0, 0, 0.12);
		transition:all 0.9s cubic-bezier(0.3, 0.6, 0.2, 1.8); */
		/* box-shadow: 0 11px 9px rgba(10, 0, 0, 0.24), 0 11px 9px rgba(10, 0, 0, 0.12),0 15px 14px rgba(0, 0, 0, 0.24); */
		box-shadow: 0 8px 20px  rgba(10, 0, 0, 0.5), 0 6px 11px rgba(10, 0, 0, 0.5),0 15px 14px rgba(0, 0, 0, 0.24);
		/* transition:all 0.9s cubic-bezier(0.4, 0.7, 0.3, 1.9); */
		transition:all 0.9s cubic-bezier(0.3, 0.3, 0.3, 1.0);
		width: 100%;
		/* border: 0px solid #e97d68;  */
	}
</style>
<script>
	if(jQuery(".mo_ldap_cloud_table_layout").length > 0)
		jQuery(".mo_ldap_cloud_table_layout").parent("form").hide();
	jQuery(document).ready(function(){
		$ldap = jQuery;
		$ldap(".individual-container-addons").click(function(){
			$ldap('.individual-container-addons').addClass('activeCurrent');
			$ldap('.individual-container-addons').not(this).removeClass('activeCurrent');
		})})
</script>
<?php
	$email = ! empty( get_option( 'mo_ldap_admin_email' ) ) ? get_option( 'admin_email' ) : get_option( 'mo_ldap_admin_email' );
?>
<div hidden id="licensingContactUsModalPopUp" name="licensingContactUsModalPopUp" class="mo_ldap_modal_popup" style="margin-left: 26%">
<div class="moldap-modal-contatiner-contact-us" style="color:black"></div>
	<div class="mo_ldap_modal_popup-content" id="contactUsPopUp" style="width: 700px; padding:20px;">
		<div style="text-align: center"><h1>Contact Us For Upgrading Your Plan </h1></div>
		<form name="f" method="post" action="" id="mo_ldap_licensing_contact_us" style="font-size: large;">
			<?php wp_nonce_field( 'mo_ldap_cloud_login_send_query_nonce' ); ?>
			<input type="hidden" name="option" value="mo_cloud_ldap_login_send_query"/>
			<div>
				<p style="font-size: large;" id="popup_query" name="popup_query">
					<br>
					<strong >Email: </strong>
					<input style=" width: 77%; margin-left: 69px; " type="email" class="mo_ldap_table_textbox" id="query_email" name="query_email" value="<?php echo esc_attr( $email ); ?>" placeholder="Enter email address through which we can reach out to you" required />
					<br><br>
					<strong style="display:inline-block; vertical-align: top;">Description: </strong>
					<textarea style="width:77%; margin-left: 21px;" id="query_popup" name="query" required rows="5" style="width: 100%" placeholder="Tell us which features you require" minlength="10" required ></textarea>
				</p>
				<p style="text-align: center; font-size: 14px;"><strong>We will get back to you shortly.</strong></p>
				<br>
				<div class="mo_ldap_modal_popup-footer" style="text-align: center">
					<input type="button" style="font-size: medium" name="miniorange_ldap_licensing_contact_us_close" id="miniorange_ldap_licensing_contact_us_close" class="button button-primary button-small" value="Close" />
					<input type="submit" style="font-size: medium" name="miniorange_ldap_feedback_submit" id="miniorange_ldap_feedback_submit" class="button button-primary button-small" value="Submit"/>
				</div>
			</div>
		</form>
	</div>
</div>
<div style="text-align: center; font-size: 14px; color: white; padding-top: 4px; padding-bottom: 4px; border-radius: 16px;"></div>
<input type="hidden" id="mo_license_plan_selected" value="licensing_plan" />
<div class="tab-content">
	<div class="tab-pane active text-center" id="cloud">
		<div class="cd-pricing-container cd-has-margins"><br>
			<h1 style="font-size: 32px ; text-align:center;">Choose Your Licensing Plan</h1>
			<br><br>
			<input type="hidden"
			<?php
			if ( $utils::is_customer_registered() ) {
				echo 'value="1"';
			} else {
				echo 'value=""';
			}
			?>
			id="mo_customer_registered">
			<ul class="cd-pricing-list cd-bounce-invert" >
				<?php
				$mo_license_status = ! empty( get_option( 'mo_ldap_license_status' ) ) ? get_option( 'mo_ldap_license_status' ) : '';
				if ( '0' === $mo_license_status ) {
					?>
				<li class="cd-popular">
					<ul class="cd-pricing-wrapper">
						<li id="singlesite_tab" data-type="singlesite" class="mosslp is-visible cd-singlesite ">
							<a id="popover1" data-toggle="popover">
								<header class="cd-pricing-header" id="plan-box" style="height: 335px;">
									<h2 style="margin-bottom: 10px" >30 Days Free Trial Plan<span style="font-size:0.5em"></span></h2>
									<h3 style="color:black;"><br /><br /></h3><br>
									<div class="cd-price">
										<span class="trial-status" style="font-weight: bold; font-size: large; color: <?php echo esc_attr( $content_color ); ?>;"><?php echo esc_html( $content_for_free_plan_tile ); ?></span><br><br>
									</div><br><br>
								</header>
							</a>                                
						</li>
						<footer class="cd-pricing-footer">
							<?php
							$mo_license_status = ! empty( get_option( 'mo_ldap_license_status' ) ) ? get_option( 'mo_ldap_license_status' ) : '';
							if ( '-1' === $mo_license_status ) {
								echo '<a class="cd-select" href="https://plugins.miniorange.com/wordpress-ldap-login-cloud" target="_blank">Extend Trial</a>';
							} else {
								echo '<a class="cd-select">Active Plan</a>';
							}
							?>
						</footer>
					</ul>
				</li>
				<?php } ?>
				<li class="cd-black">
					<ul class="cd-pricing-wrapper">
						<li id="singlesite_tab" data-type="singlesite" class="mosslp is-visible">
							<a id="popover2" data-toggle="popover">
							<header class="cd-pricing-header" style="height: 335px;">
								<h2 style="margin-bottom: 10px" >Monthly Plan<br/><br/></h2>
								<div class="cd-price" ><br>
									<p class="pricing-text-line">Pay User Usage Charges on Monthly Basis</p>
									</div><br><br>
									<div id="Basic_no_of_instances_drop_down_div" name="Basic_no_of_instances_drop_down_div">
										<select style="width: 90%;" id="basic_no_of_instances_drop_down" name="basic_no_of_instances_drop_down">
											<option value="0"><strong>Number of Users</strong></option>
											<option value="1">$20 / month - Upto 30 Users</option>
											<option value="2">$30 / month - Upto 100 Users</option>
											<option value="3">$40 / month - Upto 200 Users</option>
											<option value="4">$52.50 / month - Upto 350 Users</option>
											<option value="5">$60 / month - Upto 500 Users</option>
											<option value="6">More than 500 Users - Contact Us</option>
										</select>
									</div>
								</header>
							</a>
						</li>
						<footer class="cd-pricing-footer">
							<?php if ( $utils::$p_code !== $plan ) { ?>
								<a href="#" class="cd-select" name="basic_no_of_instances_drop_down" onclick="contactus(this,false,false)" >Upgrade Now</a>
							<?php } ?>
						</footer>
					</ul>
				</li>
				<li class="cd-popular">
					<ul class="cd-pricing-wrapper">
						<li id="singlesite_tab" data-type="singlesite" class="mosslp is-visible">
							<a id="popover3" data-toggle="popover">
								<header class="cd-pricing-header" style="height: 335px;">
									<h2 style="margin-bottom: 10px;">Yearly Plan<br/><br/></h2>
									<div class="cd-price" ><br>
										<p class="pricing-text-line">Pay User Usage Charges for 10 Months & Get 2 Months Subscription FREE</p>
									</div><br>
									<div id="plus_no_of_instances_drop_down_div" name="plus_no_of_instances_drop_down_div">
										<select style="width: 90%;" id="plus_no_of_instances_drop_down" name="plus_no_of_instances_drop_down">
											<option value="0"><strong>Number of Users</strong></option>
											<option value="1">$300 / year - Upto 100 Users</option>
											<option value="2">$400 / year - Upto 200 Users</option>
											<option value="3">$525 / year - Upto 350 Users</option>
											<option value="4">$600 / year - Upto 500 Users</option>
											<option value="5">More than 500 Users - Contact Us</option>
										</select>
									</div>
								</header>
							</a>                                
						</li>
						<footer class="cd-pricing-footer">
							<?php if ( $utils::$p_code !== $plan ) { ?>
								<a href="#" class="cd-select" name="plus_no_of_instances_drop_down" onclick="upgradeform('wp_ldap_cloud_premium_plan')" >Upgrade Now</a>
							<?php } ?>
						</footer>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</div>
<section class="section-features">
	<div class="row">
		<h2 class="feature-heading mo-ldap-h2">Key Features</h2>
	</div>
	<div class="row feature-row">
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-key icon-big" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">LDAP Authentication</h3>
		</div>
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-shield" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Support for LDAPS for secure connection to LDAP Server</h3>
		</div>
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-user-secret" style="font-size:40px;color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Assign WordPress role(s) based on LDAP groups</h3>
		</div>
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-exchange" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Custom WordPress profile mapping</h3>               
		</div>
	</div>
	<div class="row feature-row">
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-sitemap" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Authenticate users from multiple search bases</h3>
		</div>
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-sign-in" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Login with username, email or any LDAP attribute of your choice</h3>
		</div>
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-search-plus" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Support for dynamic custom search filter</h3>
		</div>
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-wordpress" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Auto-Registration of LDAP users in WordPress site</h3>
		</div>
	</div>
	<div class="row feature-row">
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-random" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Authenticate users from both LDAP and WordPress</h3>
		</div>
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-external-link" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Redirect to custom URL after authentication</h3>                
		</div>
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-user" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">WordPress to LDAP user profile update</h3>                
		</div>
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-lock" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">Protect all website content by login</h3>               
		</div>
	</div>    
	<div class="row feature-row">
		<div class="col span-1-of-4 feature-box">
			<em class="fa fa-window-restore" style="font-size:40px; color:#1779ba;margin-bottom:20px;"></em>
			<h3 class="feature">WordPress Multisite Support</h3>
		</div>
	</div>                      
</section>                                   
<section class="section-add-ons">
	<div class="row">
		<h2 class="feature-heading mo-ldap-h2">Premium Add-ons</h2>
	</div>
	<div class="row">
		<?php
		$adddon_object = $addons;
		$adddon_object->mo_ldap_cloud_show_cloud_addons_content();
		?>
	</div>
	<form style="display:none;" id="loginform" action="<?php echo esc_attr( MO_LDAP_CLOUD_HOST_NAME . '/moas/login' ); ?>" target="_blank" method="post">
		<input type="email" name="username" value="<?php echo esc_attr( get_option( 'mo_ldap_admin_email' ) ); ?>"/>
		<input type="text" name="redirectUrl" value="<?php echo esc_attr( MO_LDAP_CLOUD_HOST_NAME . '/moas/initializepayment' ); ?>"/>
		<input type="text" name="requestOrigin" id="requestOrigin"/>
	</form>
	<form id="mo_ldap_check_license_form"   method="post">
		<?php wp_nonce_field( 'mo_ldap_check_license_nonce' ); ?>
		<input type="hidden" name="option" value="mo_ldap_cloud_check_license" />
	</form>  
	<script>
		function moldap_upgradeform(planType){
			jQuery('#requestOrigin').val(planType);
			jQuery('#loginform').submit();
		}
		function contactus(elem,isMultisite){
			selectedUserOption = jQuery("#"+elem.name+" option:selected")[0].label;
			selectedUserOptionText = selectedUserOption.includes("Number of Users") ? "multiple users" : selectedUserOption.split(" - ")[1];
			selectedUserOptionText = selectedUserOptionText.includes("Contact Us") ? "more than 500 users" : selectedUserOptionText;
			planName = jQuery("#"+elem.name).parents(".cd-pricing-header").find("h2").text();
			if(isMultisite){
				selectedSubsiteOption = jQuery("#"+elem.name).closest(".cd-pricing-header").find("select[id*='subsites'] option:selected")[0].label
				selectedUserSubsiteText = selectedSubsiteOption.includes("Number of Subsites") ? " " : " - "+selectedSubsiteOption.split(" - ")[1].split(" / ")[0]+" ";
			}
			multisiteText = isMultisite ? selectedUserSubsiteText : " ";
			queryText = "Hi! I am interested in your "+planName+ multisiteText +"for "+selectedUserOptionText+". Could you please let me know more about this plan? ";
			jQuery("#query_popup").val(queryText);
			jQuery("#licensingContactUsModalPopUp").show();
		}
		function openSupportForm(planType){
			query = "Hi!! I am interested in "+planType+" Add-on and want to know more about it.";
			jQuery("#query_popup").val(query);
			jQuery("#licensingContactUsModalPopUp").show();
		}
		jQuery('#miniorange_ldap_licensing_contact_us_close').click(
			function(){
				jQuery("#mo_ldap_licensing_contact_us #query").val('');
				jQuery('#licensingContactUsModalPopUp').hide();
			});
	</script>
	<a  id="mo_backto_ldap_accountsetup_tab" style="display:none;" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'account' ), isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ) ); ?>">Back</a>
</section>
<style>
	.section-add-ons{
		background-color:#f9f9f9;
		margin: 8px 0;
		padding: 20px 0;
		padding-bottom: 40px;
	}
	.table-onpremisetable2 th {
		background-color: #fcfdff;
		text-align: center;
		vertical-align:center;
	}
	.table-onpremisetable2 td {
		background-color: #fcfdff;
		text-align: center;
		vertical-align:center;
	}
	h1 {
		margin: .67em 0;
		font-size: 2em;
	}
	.tab-content-plugins-pricing div {
		background: #173d50;
	}
</style>
<script>
	function upgradeform(planType) {
		jQuery('#requestOrigin').val(planType);
		if(jQuery('#mo_customer_registered').val() == 1)
			jQuery('#loginform').submit();
		else
			location.href = jQuery('#mo_backto_ldap_accountsetup_tab').attr('href');
	}
</script>
<style>
	.supported-payment-methods{
		background-color: #f9f9f9;
		padding:2% 15% 4% 15%;
		margin: 10px 0;
	}
	.mo-ldap-h2{
		word-spacing: 4px;
		font-size: 180%;
		text-align: center;
		margin-bottom: 30px;
		letter-spacing: 1px;
		font-weight: 400;
		text-transform: uppercase;
		font-weight: 600;
	}
	.mo-ldap-h2::after{
		height: 2px;
		display: block;
		background-color: #e67e22;
		content: " ";
		width: 200px;
		margin:0 auto;
		margin-top: 20px;
	}
	.plan-box{
		border: 1px solid #aaa;
		min-height:330px;
		background-color: #cab9c81a;
		border-radius: 10px;
		margin: 0 10px;
		box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
		transition: 0.3s;
	}
	.plan-box:hover{
		background-color: #e97d68;
		color:#fff;
		border-color: #e97d68;
		box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
	}
	.plan-box div{
		padding:20px;
		height: 100px;
		font-size: 14px;
		line-height: normal;
		font-weight:400;    
	}
	.plan-box div:first-child{
		height: 90px;
		text-align: center;
		border-bottom: 4px solid #fff;
	}
	.payment-images{
		width:140px;
	}
	.plan-box div:last-child{
		font-size: 15px;
		line-height: 23px;
	}
	.return-policy{
		width: 100%;
		background-color: #f9f9f9;
		padding: 20px 80px 50px 80px;
	}
</style>
<section class="supported-payment-methods">
	<div class="row">
		<h2 class="mo-ldap-h2">Supported Payment Methods</h2>
	</div>
	<div class="row">
		<div class="col span-1-of-3">
			<div class="plan-box">
				<div>
					<i style="font-size:30px;" class="fa fa-cc-amex" aria-hidden="true"></i>
					<i style="font-size:30px;" class="fa fa-cc-visa" aria-hidden="true"></i>
					<i style="font-size:30px;" class="fa fa-cc-mastercard" aria-hidden="true"></i>
				</div>
				<div>
					If the payment is made through Credit Card/International Debit Card, the license will be created automatically once the payment is completed.
				</div>
			</div>
		</div>
		<div class="col span-1-of-3">
			<div class="plan-box">
				<div>
					<img class="payment-images" src="<?php echo esc_url( MO_LDAP_CLOUD_IMAGES . 'paypal.webp' ); ?>" alt="">
				</div>
				<div>
					Use the PayPal ID <span style="color:blue;text-decoration:underline;">info@xecurify.com</span> for making the payment via PayPal.<br><br>  
				</div>
			</div>
		</div>
		<div class="col span-1-of-3">
			<div class="plan-box">
				<div>
					<i style="font-size:30px;" class="fa fa-university" aria-hidden="true"><span style="font-size: 20px;font-weight:500;">&nbsp;&nbsp;Bank Transfer</span></i>
				</div>
				<div>
					If you want to use bank transfer for the payment then contact us at <span style="color:blue;text-decoration:underline;">info@xecurify.com</span>  so that we can provide you the bank details.
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<p style="margin-top:20px;font-size:16px;">
			<span style="font-weight:500;"> Note :</span> Once you have paid through PayPal/Net Banking, please inform us so that we can confirm and update your license.
		</p>
	</div>                       
</section>
<section class="return-policy">
	<div class="row">
		<h2 class="mo-ldap-h2">10 days Return Policy</h2>
	</div>
	<div class="row">
		<p style="font-size:16px;">
			If the premium plugin you purchased is not working as advertised and you’ve attempted to resolve any feature issues with our support team, which couldn't get resolved, we will refund the whole amount within 10 days of the purchase. <br><br>
			<span style="color:red;font-weight:500;font-size:18px;">Note that this policy does not cover the following cases: </span> <br><br>
			<span> 1. Change of mind or change in requirements after purchase. <br>
			2. Infrastructure issues not allowing the functionality to work.
			</span> <br><br>
			Please email us at <a href="mailto:info@xecurify.com">info@xecurify.com</a> for any queries regarding the return policy.
		</p>
	</div>
</section>
