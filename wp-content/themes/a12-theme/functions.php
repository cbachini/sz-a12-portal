<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

/**
 * A12 – Tag archive multi-CPT
 */
add_action('after_setup_theme', function () {

  $post_types = [
    'cpt_devotos',
    'cpt_radio_aparecida',
    'cpt_radio_pop',
    'cpt_redacao',
    'cpt_redentoristas',
    'cpt_santuario',
    'cpt_tv',
  ];

  // Garante suporte a post_tag
  add_action('init', function () use ($post_types) {
    foreach ($post_types as $pt) {
      register_taxonomy_for_object_type('post_tag', $pt);
    }
  }, 20);

  // Altera apenas o archive padrão de tags (/tag/slug/)
  add_action('pre_get_posts', function ($q) use ($post_types) {
    if (is_admin() || !$q->is_main_query()) return;

    if ($q->is_tag()) {
      $q->set('post_type', $post_types);
      $q->set('ignore_sticky_posts', true);
    }
  });
});

/**
 * Shortcode: [a12_area_termo] ou [a12_area_termo post_id="123"]
 * Detecta a taxonomia de área pelo post_type (tax_tv, tax_radio_pop, etc),
 * ignora post_tag e retorna o termo mais específico (mais profundo) com link.
 */
function a12_shortcode_area_termo($atts) {

	$atts = shortcode_atts(array(
		'post_id' => 0,
	), $atts, 'a12_area_termo');

	$post_id = (int) $atts['post_id'];
	if (!$post_id) {
		$post_id = get_the_ID();
	}
	if (!$post_id) {
		return '';
	}

	$post_type = get_post_type($post_id);
	if (!$post_type) {
		return '';
	}

	// Mapeia post_type -> taxonomia (conforme sua tabela)
	$map = array(
		'cpt_devotos'         => 'tax_devotos',
		'cpt_radio_aparecida' => 'tax_radio_aparecida',
		'cpt_radio_pop'       => 'tax_radio_pop',
		'cpt_redacao'         => 'tax_redacao',
		'cpt_redentoristas'   => 'tax_redentoristas',
		'cpt_santuario'       => 'tax_santuario',
		'cpt_tv'              => 'tax_tv',
	);

	if (empty($map[$post_type])) {
		return '';
	}

	$tax = $map[$post_type];

	if (!taxonomy_exists($tax)) {
		return '';
	}

	$terms = get_the_terms($post_id, $tax);
	if (is_wp_error($terms) || empty($terms)) {
		return '';
	}

	// Escolhe o termo mais profundo na hierarquia (mais específico)
	$best_term  = null;
	$best_depth = -1;

	foreach ($terms as $term) {
		$depth     = 0;
		$parent_id = (int) $term->parent;

		while ($parent_id) {
			$depth++;
			$p = get_term($parent_id, $tax);
			if (is_wp_error($p) || !$p) {
				break;
			}
			$parent_id = (int) $p->parent;
		}

		if ($depth > $best_depth) {
			$best_depth = $depth;
			$best_term  = $term;
		}
	}

	if (!$best_term) {
		return '';
	}

	$link = get_term_link($best_term);
	if (is_wp_error($link)) {
		return '';
	}

	return '<span class="a12-termo a12-termo-area"><a href="' . esc_url($link) . '">' . esc_html($best_term->name) . '</a></span>';
}
add_shortcode('a12_area_termo', 'a12_shortcode_area_termo');