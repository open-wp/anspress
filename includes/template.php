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

namespace AnsPress;
use AnsPress\Shortcodes;

defined( 'ABSPATH' ) || exit;

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
 * Get answer title.
 *
 * @param integer $post_id Answer id.
 * @since 4.2.0
 */
function answer_title( $post_id = 0 ) {
	$post     = get_post( $post_id );
	$question = get_post( $post_id );

	echo apply_filters( 'the_title', $question->post_title, $post_id );
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
	$label = __( 'Accept', 'anspress-question-answer' );

	$have_best = ap_have_answer_selected( $_post->post_parent );
	$selected  = ap_is_selected( $_post );
	$hide      = false;

	if ( $have_best && $selected ) {
		$title  = __( 'Unset this answer as a best answer', 'anspress-question-answer' );
		$label  = __( 'Unaccept', 'anspress-question-answer' );
		$active = true;
	}

	if ( $have_best && ! $selected ) {
		$hide = true;
	}

	$btn = '<a href="#" class="ap-btn-select ' . ( $active ? ' active' : '' ) . ( $hide ? ' hide' : '' ) . '" ap="toggleAnswer" apquery="' . $q . '" title="' . $title . '">';

	$btn .= '<i class="' . ( ! $selected ? 'apicon-check' : 'apicon-x' ) . '"></i>';
	$btn .= $label . '</a>';

	return $btn;
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
function get_answers_sorting( $base = false ) {
	if ( false === $base ) {
		$base = get_permalink();
	}

	$arr = array(
		'active' => [
			'label' => __( 'Active', 'anspress-question-answer' ),
			'sql'   => 'qameta.last_updated DESC',
		],
		'votes' => [
			'label' => __( 'Votes', 'anspress-question-answer' ),
			'sql'   => 'CASE WHEN IFNULL(votes_net, 0) >= 0 THEN 1 ELSE 2 END ASC, ABS(votes_net) DESC',
		],
		'published' => [
			'label' => __( 'Published', 'anspress-question-answer' ),
			'sql'   => '%1$s.post_date DESC',
		],
	);

	// Show unpublished answers tab.
	$unpublished_posts = ap_get_unpublished_post_count( 'answer', get_current_user_id(), get_question_id() );
	if ( is_user_logged_in() && $unpublished_posts > 0 ) {
		$arr['unpublished'] = array(
			'label' => __( 'Unpublished', 'anspress-question-answer' ),
			'count' => $unpublished_posts,
		);
	}

	/**
	 * Answers tabs links.
	 *
	 * @param array $links Answers link.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_answers_sorting', $arr );
}

/**
 * Get active tab slug of answers.
 *
 * @return void
 * @since 4.2.0
 */
function get_current_answer_sorting() {
	$active = ap_isset_post_value( 'asort', ap_opt( 'answers_sort' ) );
	$tab    = get_answers_sorting();

	// Check if tab exists.
	if ( ! isset( $tab[ $active ] ) ) {
		$active = 'active';
	}

	/**
	 * Filter active tab of answers.
	 *
	 * @param string $active Currently active tab sub.
	 */
	return apply_filters( 'ap_get_current_answer_sorting', $active );
}

/**
 * Questions tab links.
 *
 * @since 4.2.0
 */
function get_questions_filter() {
	$links = [];

	$links[''] = array(
		'label' => __( 'All', 'anspress-question-answer' ),
		'sql'   => '',
	);

	$links['solved'] = array(
		'label' => __( 'Solved', 'anspress-question-answer' ),
		'sql'   => 'IFNULL(qameta.selected_id, 0) > 0',
	);

	$links['unsolved'] = array(
		'label' => __( 'Unsolved', 'anspress-question-answer' ),
		'sql'   => 'IFNULL(qameta.selected_id, 0) < 1',
	);

	$links['unanswered'] = array(
		'label' => __( 'Unanswered', 'anspress-question-answer' ),
		'sql'   => 'qameta.answers < 1',
	);

	// Show unpublished answers tab.
	$unpublished_posts = ap_get_unpublished_post_count( 'question', get_current_user_id(), get_question_id() );
	if ( is_user_logged_in() && $unpublished_posts > 0 ) {
		$links['unpublished'] = array(
			'label' => __( 'Unpublished', 'anspress-question-answer' ),
			'count' => $unpublished_posts,
			'sql' => '',
		);
	}

	/**
	 * Questions filter links.
	 *
	 * @param array $links Questions link.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_questions_filter', $links );
}

/**
 * Get current filter of questions.
 *
 * @return void
 * @since 4.2.0
 * @todo Add `qfilter` to options. Previous option was `qtab`.
 */
function get_current_questions_filter() {
	$active = ap_isset_post_value( 'qfilter' );
	$tab    = get_questions_filter();

	// Check if tab exists.
	if ( ! isset( $tab[ $active ] ) ) {
		$active = '';
	}

	/**
	 * Filter active tab of questions.
	 *
	 * @param string $active Currently active tab sub.
	 */
	return apply_filters( 'get_current_questions_filter', $active );
}

/**
 * Get post classes.
 *
 * @param integer|\WP_Post $post_id Post id or object.
 * @return array
 * @since 4.2.0
 */
function get_post_classes( $class = '', $post_id = 0 ) {
	$_post = ap_get_post( $post_id );

	if ( ! ap_is_cpt( $_post ) ) {
		return;
	}

	$classes = [];
	$classes[] = esc_attr( $class );
	$classes[] = $_post->post_type;
	$classes[] = 'post-' . $_post->ID;
	$classes[] = 'ap-status-' . $_post->post_status;

	if ( 'question' === $_post->post_type ) {
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
 * Get sorting of questions.
 *
 * @return array
 * @since 4.2.0
 */
function get_questions_sorting() {
	$arr = array(
		'active' => [
			'label' => __( 'Active', 'anspress-question-answer' ),
			'sql'   => 'qameta.last_updated DESC',
		],
		'answers' => [
			'label' => __( 'Answers', 'anspress-question-answer' ),
			'sql'   => 'IFNULL(qameta.answers, 0) DESC',
		],
		'votes' => [
			'label' => __( 'Votes', 'anspress-question-answer' ),
			'sql'   => 'CASE WHEN IFNULL(votes_net, 0) >= 0 THEN 1 ELSE 2 END ASC, ABS(votes_net) DESC',
		],
		'published' => [
			'label' => __( 'Published', 'anspress-question-answer' ),
			'sql'   => '%1$s.post_date DESC',
		],
		'views' => [
			'label' => __( 'Views', 'anspress-question-answer' ),
			'sql'   => 'IFNULL(qameta.views, 0) DESC',
		],
	);

	/**
	 * Filter questions sorting.
	 *
	 * @param array $arr Sorting.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_questions_sorting', $arr );
}

/**
 * Get current sorting of questions.
 *
 * @return string
 * @since 4.2.0
 */
function get_current_questions_sorting() {
	$current = ap_sanitize_unslash( ap_isset_post_value( 'qsort' ) );
	$sorting = get_questions_sorting();

	/**
	 * Current question sorting.
	 *
	 * @param string $filters Current filters.
	 * @since 4.2.0
	 */
	$current = apply_filters( 'get_current_questions_sorting', $current );

	if ( ! empty( $current ) && isset( $sorting[ $current ] ) ) {
		return $current;
	}

	return 'active';
}

/**
 * Get current questions filters.
 *
 * @since 4.2.0
 */
function get_current_questions_filters() {
	$filters = [];

	$current_qfilter = get_current_questions_filter();

	if ( '' !== $current_qfilter ) {
		$qfilters = get_questions_filter();
		$filters[]         = array(
			'name'  => 'qfilter',
			'label' => $qfilters[ $current_qfilter ]['label'],
		);
	}

	return apply_filters( 'get_current_questions_filters', $filters );
}

/**
 * Show alert message.
 *
 * @param string  $title      Alert title.
 * @param string  $message    Alert message.
 * @param boolean $show_close Show close button.
 * @return void
 * @since 4.2.0
 */
function alert( $title = '', $message = '', $class = 'success', $show_close = true ) {
	?>
	<div class="ap-alert <?php echo esc_attr( $class ); ?>">
		<?php if ( $show_close ) : ?>
			<button class="ap-alert-close ap-remove-parent">&times;</button>
		<?php endif; ?>
		<?php if ( ! empty( $title ) ) : ?>
			<strong><?php echo esc_html( $title ); ?></strong>
		<?php endif; ?>
		<?php echo esc_html( $message ); ?>
	</div>
	<?php
}
