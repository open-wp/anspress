<?php
/**
 * Question CPT functions.
 *
 * @package AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

namespace AnsPress;

// Bail if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register answer custom post type.
 *
 * @return void
 * @since 4.2.0 Moved from class `AnsPress_PostTypes`.
 */
function _register_answer() {
	// Answer CPT labels.
	$labels = array(
		'name'               => _x( 'Answers', 'Post Type General Name', 'anspress-question-answer' ),
		'singular_name'      => _x( 'Answer', 'Post Type Singular Name', 'anspress-question-answer' ),
		'menu_name'          => __( 'Answers', 'anspress-question-answer' ),
		'parent_item_colon'  => __( 'Parent answer:', 'anspress-question-answer' ),
		'all_items'          => __( 'All answers', 'anspress-question-answer' ),
		'view_item'          => __( 'View answer', 'anspress-question-answer' ),
		'add_new_item'       => __( 'Add new answer', 'anspress-question-answer' ),
		'add_new'            => __( 'New answer', 'anspress-question-answer' ),
		'edit_item'          => __( 'Edit answer', 'anspress-question-answer' ),
		'update_item'        => __( 'Update answer', 'anspress-question-answer' ),
		'search_items'       => __( 'Search answers', 'anspress-question-answer' ),
		'not_found'          => __( 'No answer found', 'anspress-question-answer' ),
		'not_found_in_trash' => __( 'No answer found in trash', 'anspress-question-answer' ),
	);

	/**
	 * Filter default answer labels.
	 *
	 * @param array $labels Default answer labels.
	 */
	$labels = apply_filters( 'ap_answer_cpt_label', $labels );

	// Answers CPT arguments.
	$args = array(
		'label'               => __( 'answer', 'anspress-question-answer' ),
		'description'         => __( 'Answer', 'anspress-question-answer' ),
		'labels'              => $labels,
		'supports'            => array(
			'editor',
			'author',
			'comments',
			'excerpt',
			'revisions',
			'custom-fields',
		),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'show_in_nav_menus'   => false,
		'show_in_admin_bar'   => false,
		'menu_icon'           => ANSPRESS_URL . '/assets/answer.png',
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
		'rewrite'             => false,
		'query_var'           => 'answer',
	);

	/**
	 * Filter default answer arguments.
	 *
	 * @param array $args Arguments.
	 */
	$args = apply_filters( 'ap_answer_cpt_args', $args );

	// Register CPT answer.
	register_post_type( 'answer', $args );
}

/**
 * Modify answer cpt permalink.
 *
 * @param  string $link Link.
 * @param  object $post Post object.
 * @return string
 * @since 4.2.0
 */
function _answer_type_link( $link, $post ) {
	if ( 'answer' !== $post->post_type ) {
		return $link;
	}

	if ( get_option( 'permalink_structure' ) ) {
		$link = home_url( '/answer/' . $post->post_name . '/' );
	}

	/**
	 * Allow overriding of answer post type permalink
	 *
	 * @param string $link Answer link.
	 * @param object $post Post object.
	 */
	return apply_filters( 'ap_answer_post_type_link', $link, $post );
}
