<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
}

/**
 * Return hover card attributes.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return string
 *
 * @deprecated 4.1.13
 */
function ap_get_hover_card_attr( $_post = null ) {
	_deprecated_function( __FUNCTION__, '4.1.13' );
}

/**
 * Echo hover card attributes.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @deprecated 4.1.13
 */
function ap_hover_card_attr( $_post = null ) {
	_deprecated_function( __FUNCTION__, '4.1.13' );
}


/**
 * Ge the post object of currently irritrated post
 *
 * @return object
 * @deprecated 4.2.0
 */
function ap_answer_the_object() {
	_deprecated_function( __FUNCTION__, '4.2.0' );

	global $answers;
	if ( ! $answers ) {
		return;
	}

	return $answers->post;
}

/**
 * Output answers tab.
 *
 * @param string|boolean $base Current page url.
 * @since 2.0.1
 * @deprecated 4.2.0
 */
function ap_answers_tab( $base = false ) {
	$sort = ap_sanitize_unslash( 'order_by', 'r', ap_opt( 'answers_sort' ) );

	if ( ! $base ) {
		$base = get_permalink();
	}

	$navs = array(
		'active' => array(
			'link'  => add_query_arg( [ 'order_by' => 'active' ], $base ),
			'title' => __( 'Active', 'anspress-question-answer' ),
		),
	);

	if ( ! ap_opt( 'disable_voting_on_answer' ) ) {
		$navs['voted'] = array(
			'link'  => add_query_arg( [ 'order_by' => 'voted' ], $base ),
			'title' => __( 'Voted', 'anspress-question-answer' ),
		);
	}

	$navs['newest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'newest' ], $base ),
		'title' => __( 'Newest', 'anspress-question-answer' ),
	);
	$navs['oldest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'oldest' ], $base ),
		'title' => __( 'Oldest', 'anspress-question-answer' ),
	);

	echo '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">';
	foreach ( (array) $navs as $k => $nav ) {
		echo '<li' . ( $sort === $k ? ' class="active"' : '' ) . '><a href="' . esc_url( $nav['link'] . '#answers-order' ) . '">' . esc_attr( $nav['title'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Output answers of current question.
 *
 * @since 2.1
 * @since 4.1.0 Removed calling function @see `ap_reset_question_query`.
 * @deprecated 4.2.0 Replace by `ap_has_answers()`.
 */
function ap_answers() {
	global $answers;
	$answers = ap_get_answers();

	ap_get_template_part( 'answers' );
	ap_reset_question_query();
}

/**
 * Get terms of a question.
 *
 * @param  boolean|string $taxonomy Taxonomy slug.
 * @param  mixed          $_post     Post object, ID or null.
 * @return string
 * @deprecated 4.2.0
 */
function ap_get_terms( $taxonomy = false, $_post = null ) {
	$_post = ap_get_post( $_post );
	if ( ! empty( $_post->terms ) ) {
		return $_post->terms;
	}
	return false;
}

/**
 * Updates terms of qameta.
 *
 * @param  integer $question_id Question ID.
 * @return integer|false
 * @since  3.1.0
 * @deprecated 4.2.0
 */
function ap_update_qameta_terms( $question_id ) {
	$terms = [];

	if ( taxonomy_exists( 'question_category' ) ) {
		$categories = get_the_terms( $question_id, 'question_category' );

		if ( $categories ) {
			$terms = $terms + $categories;
		}
	}

	if ( taxonomy_exists( 'question_tag' ) ) {
		$tags = get_the_terms( $question_id, 'question_tag' );

		if ( $tags ) {
			$terms = $terms + $tags;
		}
	}

	if ( taxonomy_exists( 'question_label' ) ) {
		$labels = get_the_terms( $question_id, 'question_label' );

		if ( $labels ) {
			$terms = $terms + $labels;
		}
	}

	$term_ids = [];

	foreach ( (array) $terms as $term ) {
		$term_ids[] = $term->term_id;
	}

	if ( ! empty( $term_ids ) ) {
		ap_insert_qameta( $question_id, [ 'terms' => $term_ids ] );
	}

	return $term_ids;
}
