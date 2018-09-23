<?php
/**
 * Template and loop related functions.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 * @since     4.2.0
 */

namespace AnsPress\Template;
use AnsPress\Shortcodes;

// Bail if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print ID of current question in loop.
 *
 * @return void
 * @since 4.2.0
 */
function qustion_id() {
	echo (int) get_question_id();
}

/**
 * Get question permalink.
 *
 * @param integer $question_id Question ID.
 * @return string
 * @since 4.2.0
 */
function get_question_permalink( $question_id = 0 ) {
	$question_id = get_question_id( $question_id );
	$permalink   = get_permalink( $question_id );

	/**
	 * Filter `get_question_permalink`.
	 *
	 * @param string  $permalink   Permalink.
	 * @param integer $question_id Question ID.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_question_permalink', $permalink, $question_id );
}

/**
 * Output question permalink in a loop.
 *
 * @param integer $question_id Question id.
 * @since 4.2.0
 */
function question_permalink( $question_id = 0 ) {
	echo esc_url( get_question_permalink( $question_id ) );
}

/**
 * Get the content of question or answer in a loop.
 *
 * @param integer $post_id Question or answer id.
 * @return string
 * @since 4.2.0
 */
function get_content( $post_id = 0 ) {
	$post_id = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );

	// Check if password is required
	if ( post_password_required( $post_id ) ) {
		return get_the_password_form();
	}

	if ( ! ap_user_can_read_post( $post_id ) ) {

		ob_start();
		ap_get_template_part( 'feedback-answer-private' );
		$content = ob_get_clean();

	} else {
		$content = get_post_field( 'post_content', $post_id );
	}

	return $content;
}

/**
 * Output question content.
 *
 * @param integer $post_id
 * @since 4.2.0
 */
function question_content( $post_id = 0 ) {
	echo apply_filters( 'the_content', get_content( $post_id ), $post_id );
}

/**
 * Get answer content.
 *
 * @param integer $post_id Answer id.
 * @since 4.2.0
 */
function answer_content( $post_id = 0 ) {
	echo apply_filters( 'the_content', get_content( $post_id ), $post_id );
}

/**
 * Get numbers of comment of a question or answer in a loop.
 *
 * @param integer $post_id Post id.
 * @return integer
 * @since 4.2.0
 */
function get_comment_number( $post_id = 0 ) {
	$post_id       = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );
	$comment_count = get_post_field( 'comment_count', $post_id );

	return apply_filters( 'ap_get_comment_number', (int) $comment_count, $post_id );
}

/**
 * Output comment count in question or answer loop.
 *
 * @param integer $post_id Question or answer ID.
 * @since 4.2.0
 */
function comment_number( $post_id = 0 ) {
	$count = get_comment_number( $post_id );

	printf( _n( '%s Comment', '%s Comments', $count, 'anspress-question-answer' ), '<span itemprop="commentCount">' . (int) $count . '</span>' );
}

/**
 * A wrapper function for @see ap_the_comments() for using in
 * post templates.
 *
 * @return void
 * @since 4.2.0
 */
function comments( $post_id = 0 ) {
	$post_id = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );

	// Check if user can read.
	if ( ! ap_user_can_read_post( $post_id ) ) {
		return false;
	}

	echo '<apcomments id="comments-' . esc_attr( $post_id ) . '" class="have-comments">';
	ap_the_comments( $post_id, [], true );
	echo '</apcomments>';

	// New comment button.
	echo ap_comment_btn_html( $post_id );
}

/**
 * Post actions button.
 *
 * @param integer $post_id Question or answer id.
 * @since   4.2.0
 */
function actions_button( $post_id = 0 ) {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$post_id = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );

	$args = wp_json_encode( [
		'post_id' => $post_id,
		'nonce'   => wp_create_nonce( 'post-actions-' . $post_id ),
	] );

	echo '<postActions class="ap-dropdown"><button class="ap-btn apicon-gear ap-actions-handle ap-dropdown-toggle" ap="actiontoggle" apquery="' . esc_js( $args ) . '"></button><ul class="ap-actions ap-dropdown-menu"></ul></postActions>';
}

/**
 * Output vote button for question or answer.
 *
 * @param integer $post_id Post id.
 * @return void
 * @since 4.2.0
 */
function vote_buttons( $post_id = 0 ) {
	$post_id = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );

	ap_vote_btn( $post_id );
}

/**
 * Output question metas.
 *
 * @param integer $question_id Question ID.
 * @return void
 * @since 4.2.0
 */
function question_metas( $question_id = 0 ) {
	$question_id = get_question_id( $question_id );
	ap_question_metas( $question_id );
}

/**
 * Get the ID of answer within the loop.
 *
 * @return void
 * @since  4.2.0
 */
function answer_id() {
	echo (int) ap_get_answer_id();
}

/**
 * Return select answer button HTML.
 *
 * @param integer $answer_id Answer ID.
 * @return string
 * @since 4.2.0
 */
function get_select_button( $answer_id = 0 ) {
	if ( ! ap_user_can_select_answer( $answer_id ) ) {
		return;
	}

	$_post = ap_get_post( $answer_id );
	$nonce = wp_create_nonce( 'select-answer-' . $_post->ID );

	$q = esc_js( wp_json_encode( [
		'answer_id' => $_post->ID,
		'__nonce'   => $nonce,
	] ) );

	$active = false;

	$title = __( 'Select this answer as best', 'anspress-question-answer' );
	$label = __( 'Select', 'anspress-question-answer' );

	$have_best = ap_have_answer_selected( $_post->post_parent );
	$selected  = ap_is_selected( $_post );
	$hide      = false;

	if ( $have_best && $selected ) {
		$title  = __( 'Unselect this answer', 'anspress-question-answer' );
		$label  = __( 'Unselect', 'anspress-question-answer' );
		$active = true;
	}

	if ( $have_best && ! $selected ) {
		$hide = true;
	}

	return '<a href="#" class="ap-btn-select ap-btn ' . ( $active ? ' active' : '' ) . ( $hide ? ' hide' : '' ) . '" ap="select_answer" apquery="' . $q . '" title="' . $title . '">' . $label . '</a>';
}

/**
 * Output select answer button in a loop.
 *
 * @param integer $answer_id Answer ID.
 * @since 4.2.0
 */
function select_button( $answer_id = 0 ) {
	echo get_select_button( $answer_id );
}

/**
 * Get the count of found answers in loop.
 *
 * @return integer
 * @since 4.2.0
 */
function get_answer_count() {
	$ap = anspress()->answers_query;
	return isset( $ap ) ? (int) anspress()->answers_query->found_posts : 0;
}

/**
 * Output the count of found answers in loop.
 *
 * @return integer
 * @since 4.2.0
 */
function answer_count() {
	echo (int) get_answer_count();
}

/**
 * Answer pagination count.
 *
 * @return string
 * @since 4.2.0
 */
function get_answer_pagination_count() {
	$query     = anspress()->answers_query;
	$ret       = '';
	$start_num = intval( ( $query->paged - 1 ) * $query->posts_per_page ) + 1;
	$from_num  = number_format_i18n( $start_num );
	$to_num    = ( $start_num + ( $query->posts_per_page - 1 ) > $query->found_posts ) ? $query->found_posts: $start_num + ( $query->posts_per_page - 1 );
	$total_int = (int) $query->found_posts;
	$total     = number_format_i18n( $total_int );

	$ret = sprintf(
		_n( 'Viewing %2$s answer (of %4$s total)', 'Viewing %1$s answers - %2$s through %3$s (of %4$s total)', $query->post_count, 'anspress-question-answer' ),
		$query->post_count, $from_num, $to_num, $total
	);

	// Filter and return
	return apply_filters( 'ap_answer_pagination_count', esc_html( $ret ) );
}

/**
 * Output answers pagination count.
 *
 * @return void
 * @since 4.2.0
 */
function answer_pagination_count() {
	echo esc_html( get_answer_pagination_count() );
}

/**
 * Get answer pagination links.
 *
 * @return string
 * @since 4.2.0
 */
function get_answer_pagination_links() {
	$query = anspress()->answers_query;

	if ( ! isset( $query->pagination_links ) || empty( $query->pagination_links ) ) {
		return false;
	}

	return apply_filters( 'ap_get_answer_pagination_links', $query->pagination_links );
}

/**
 * Output answers pagination links.
 *
 * @return void
 * @since 4.2.0
 */
function answer_pagination_links() {
	echo get_answer_pagination_links();
}

/**
 * Get questions pagination links.
 *
 * @return string
 * @since 4.2.0
 */
function get_question_pagination_links() {
	$query = anspress()->questions_query;

	if ( ! isset( $query->pagination_links ) || empty( $query->pagination_links ) ) {
		return false;
	}

	return apply_filters( 'ap_get_question_pagination_links', $query->pagination_links );
}

/**
 * Output questions pagination links.
 *
 * @return void
 * @since 4.2.0
 */
function question_pagination_links() {
	echo get_question_pagination_links();
}

/**
 * Questions pagination count.
 *
 * @return string
 * @since 4.2.0
 */
function get_question_pagination_count() {
	$query     = anspress()->questions_query;
	$ret       = '';
	$start_num = intval( ( $query->paged - 1 ) * $query->posts_per_page ) + 1;
	$from_num  = number_format_i18n( $start_num );
	$to_num    = ( $start_num + ( $query->posts_per_page - 1 ) > $query->found_posts ) ? $query->found_posts: $start_num + ( $query->posts_per_page - 1 );
	$total_int = (int) $query->found_posts;
	$total     = number_format_i18n( $total_int );

	$ret = sprintf(
		_n( 'Viewing %2$s question (of %4$s total)', 'Viewing %1$s questions - %2$s through %3$s (of %4$s total)', $query->post_count, 'anspress-question-answer' ),
		$query->post_count, $from_num, $to_num, $total
	);

	// Filter and return
	return apply_filters( 'ap_question_pagination_count', esc_html( $ret ) );
}

/**
 * Output questions pagination count.
 *
 * @return void
 * @since 4.2.0
 */
function question_pagination_count() {
	echo esc_html( get_question_pagination_count() );
}

/**
 * Answers tab links.
 *
 * @param string|boolean $base Current page url.
 * @since 4.2.0
 */
function get_answers_tab_links( $base = false ) {
	if ( false === $base ) {
		$base = get_permalink();
	}

	$links = [];

	// Show unpublished answers tab.
	$unpublished_posts = ap_get_unpublished_post_count( 'answer', get_current_user_id(), get_question_id() );
	if ( is_user_logged_in() && $unpublished_posts > 0 ) {
		$links['unpublished'] = array(
			'link'  => add_query_arg( [ 'order_by' => 'unpublished' ], $base ),
			'title' => __( 'Unpublished', 'anspress-question-answer' ),
			'count' => $unpublished_posts,
		);
	}

	$links['active'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'active' ], $base ),
		'title' => __( 'Active', 'anspress-question-answer' ),
	);

	if ( ! ap_opt( 'disable_voting_on_answer' ) ) {
		$links['voted'] = array(
			'link'  => add_query_arg( [ 'order_by' => 'voted' ], $base ),
			'title' => __( 'Voted', 'anspress-question-answer' ),
		);
	}

	$links['newest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'newest' ], $base ),
		'title' => __( 'Newest', 'anspress-question-answer' ),
	);

	$links['oldest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'oldest' ], $base ),
		'title' => __( 'Oldest', 'anspress-question-answer' ),
	);

	/**
	 * Answers tabs links.
	 *
	 * @param array $links Answers link.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_answers_tab_links', $links );
}

/**
 * Get active tab slug of answers.
 *
 * @return void
 * @since 4.2.0
 */
function get_answers_active_tab() {
	$active = ap_isset_post_value( 'order_by', ap_opt( 'answers_sort' ) );
	$tab    = get_answers_tab_links();

	// Check if tab exists.
	if ( empty( $tab[ $active ] ) ) {
		$active = ap_opt( 'answers_sort' );
	}

	/**
	 * Filter active tab of answers.
	 *
	 * @param string $active Currently active tab sub.
	 */
	return apply_filters( 'ap_get_answers_active_tab', $active );
}

/**
 * Questions tab links.
 *
 * @param string|boolean $base Current page url.
 * @since 4.2.0
 */
function get_questions_tab_links( $base = false ) {
	if ( false === $base ) {
		$base = get_permalink();
	}

	$links = [];

	$links['all'] = array(
		'link'  => add_query_arg( [ 'tab' => 'all' ], $base ),
		'title' => __( 'All', 'anspress-question-answer' ),
	);

	$links['solved'] = array(
		'link'  => add_query_arg( [ 'tab' => 'solved' ], $base ),
		'title' => __( 'Solved', 'anspress-question-answer' ),
	);

	$links['unanswered'] = array(
		'link'  => add_query_arg( [ 'tab' => 'unanswered' ], $base ),
		'title' => __( 'Unanswered', 'anspress-question-answer' ),
	);

	// Show unpublished answers tab.
	$unpublished_posts = ap_get_unpublished_post_count( 'question', get_current_user_id(), get_question_id() );
	if ( is_user_logged_in() && $unpublished_posts > 0 ) {
		$links['unpublished'] = array(
			'link'  => add_query_arg( [ 'tab' => 'unpublished' ], $base ),
			'title' => __( 'Unpublished', 'anspress-question-answer' ),
			'count' => $unpublished_posts,
		);
	}

	/**
	 * Questions tabs links.
	 *
	 * @param array $links Questions link.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_questions_tab_links', $links );
}

/**
 * Get active tab slug of answers.
 *
 * @return void
 * @since 4.2.0
 */
function get_questions_active_tab() {
	$active = ap_isset_post_value( 'tab', ap_opt( 'tab' ) );
	$tab    = get_questions_tab_links();

	// Check if tab exists.
	if ( empty( $tab[ $active ] ) ) {
		$active = 'all';
	}

	/**
	 * Filter active tab of questions.
	 *
	 * @param string $active Currently active tab sub.
	 */
	return apply_filters( 'ap_get_questions_active_tab', $active );
}

/**
 * Get status of the question or answer in loop.
 *
 * @param integer $post_id Post id.
 * @return string
 * @since 4.2.0
 */
function get_status( $post_id = 0 ) {
	$post_id = ap_is_answer() ? ap_get_answer_id( $post_id ) : get_question_id( $post_id );
	return get_post_field( 'post_status', $post_id );
}

/**
 * Output question or answer status label.
 *
 * @param integer $post_id Question or answer id.
 * @since 4.2.0
 */
function status( $post_id = 0 ) {
	$obj = get_post_status_object( get_status( $post_id ) );
	echo esc_attr( $obj->label );
}

/**
 * Check if question or answer is private.
 *
 * @param integer $post_id Post id.
 * @return boolean
 * @since 4.2.0
 */
function is_private( $post_id = 0 ) {
	$status = get_status( $post_id );
	$ret    = (bool) ( 'private_post' === $status );

	/**
	 * Filter for overriding returned value of function `is_private`.
	 *
	 * @param bool    $ret     Return value.
	 * @param integer $post_id Post id.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_is_private', $ret, $post_id );
}

/**
 * Check if question or answer is scheduled.
 *
 * @param integer $post_id Post id.
 * @return boolean
 * @since 4.2.0
 */
function is_scheduled( $post_id = 0 ) {
	$status = get_status( $post_id );
	$ret    = (bool) ( 'future' === $status );

	/**
	 * Filter for overriding returned value of function `is_scheduled`.
	 *
	 * @param bool    $ret     Return value.
	 * @param integer $post_id Post id.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_is_scheduled', $ret, $post_id );
}

/**
 * Check if question or answer is trashed.
 *
 * @param integer $post_id Post id.
 * @return boolean
 * @since 4.2.0
 */
function is_trashed( $post_id = 0 ) {
	$status = get_status( $post_id );
	$ret    = (bool) ( 'trash' === $status );

	/**
	 * Filter for overriding returned value of function `is_trashed`.
	 *
	 * @param bool    $ret     Return value.
	 * @param integer $post_id Post id.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_is_trashed', $ret, $post_id );
}

/**
 * Check if question or answer is moderate.
 *
 * @param integer $post_id Post id.
 * @return boolean
 * @since 4.2.0
 */
function is_moderate( $post_id = 0 ) {
	$status = get_status( $post_id );
	$ret    = (bool) ( 'moderate' === $status );

	/**
	 * Filter for overriding returned value of function `is_moderate`.
	 *
	 * @param bool    $ret     Return value.
	 * @param integer $post_id Post id.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_is_moderate', $ret, $post_id );
}

/**
 * Get post classes.
 *
 * @param integer|\WP_Post $post_id Post id or object.
 * @return array
 * @since 4.2.0
 */
function get_post_classes( $post_id = 0 ) {
	$_post = ap_get_post( $post_id );

	if ( ! ap_is_cpt( $_post ) ) {
		return;
	}

	$classes = [];
	$classes[] = $_post->post_type;
	$classes[] = 'post-' . $_post->ID;
	$classes[] = 'ap-status-' . $_post->post_status;

	if ( 'question' === $_post->post_type ) {
		$classes[] = 'ap-questions-item';
		if ( ap_have_answer_selected( $_post->ID ) ) {
			$classes[] = 'answer-selected';
		}

		if ( ap_is_featured_question( $_post->ID ) ) {
			$classes[] = 'featured-question';
		}

		$classes[] = 'answer-count-' . ap_get_answers_count();

	}

	// Best answer.
	if ( 'answer' === $_post->post_type && ap_is_selected( $_post ) ) {
		$classes[] = 'best-answer';
	}

	// Have no permission to read.
	if ( ! ap_user_can_read_question( $_post ) ) {
		$classes[] = 'no-read-permission';
	}

	/**
	 * Filter AnsPress post classes.
	 *
	 * @param array    $classes Class list.
	 * @param \WP_Post $_post   Post object.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_post_status', $classes, $_post );
}

/**
 * Output post classes.
 *
 * @param integer|\WP_Post $post_id Post id or object.
 * @return array
 * @since 4.2.0
 */
function post_classes( $post_id = 0 ) {
	$list = get_post_classes( $post_id );

	if ( is_array( $list ) ) {
		echo esc_attr( implode( ' ', $list ) );
	}
}
