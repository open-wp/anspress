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
	_deprecated_function( __FUNCTION__, '4.2.0' );

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
 * @deprecated 4.2.0 Replace by `ap_get_answers()`.
 */
function ap_answers() {
	_deprecated_function( __FUNCTION__, '4.2.0' );

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
	_deprecated_function( __FUNCTION__, '4.2.0' );

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
	_deprecated_function( __FUNCTION__, '4.2.0' );

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

/**
 * Check if user can view current answer
 *
 * @return boolean
 * @since 2.1
 * @deprecated 4.2.0
 */
function ap_answer_user_can_view() {
	_deprecated_function( __FUNCTION__, '4.2.0', 'ap_user_can_view_post' );

	return ap_user_can_view_post( get_the_ID() );
}

/**
 * Get selected answer object
 *
 * @param  integer $question_id Question ID.
 * @since  2.0
 * @deprecated 4.2.0
 */
function ap_get_best_answer( $question_id = false ) {
	_deprecated_function( __FUNCTION__, '4.2.0' );

	if ( false === $question_id ) {
		$question_id = get_question_id();
	}

	$args = array(
		'only_best_answer' => true,
		'question_id'      => $question_id,
	);
	return new Answers_Query( $args );
}

/**
 * Return paged position of answer.
 *
 * @param boolean|integer $question_id Question ID.
 * @param boolean|integer $answer_id Answer ID.
 * @return integer
 * @since 4.0.0
 * @deprecated 4.2.0
 */
function ap_get_answer_position_paged( $question_id = false, $answer_id = false ) {
	_deprecated_function( __FUNCTION__, '4.2.0' );

	global $wpdb;

	if ( false === $question_id ) {
		$question_id = get_question_id();
	}

	if ( false === $answer_id ) {
		$answer_id = get_query_var( 'answer_id' );
	}

	$user_id     = get_current_user_id();
	$ap_order_by = ap_get_current_list_filters( 'order_by', 'active' );
	$cache_key   = $question_id . '-' . $answer_id . '-' . $user_id;
	$cache       = wp_cache_get( $cache_key, 'ap_answer_position' );

	if ( false !== $cache ) {
		return $cache;
	}

	if ( 'voted' === $ap_order_by ) {
		$orderby = 'CASE WHEN IFNULL(qameta.votes_up - qameta.votes_down, 0) >= 0 THEN 1 ELSE 2 END ASC, ABS(qameta.votes_up - qameta.votes_down) DESC';
	} if ( 'oldest' === $ap_order_by ) {
		$orderby = "{$wpdb->posts}.post_date ASC";
	} elseif ( 'newest' === $ap_order_by ) {
		$orderby = "{$wpdb->posts}.post_date DESC";
	} else {
		$orderby = 'qameta.last_updated DESC ';
	}

	$post_status = [ 'publish' ];

	// Check if user can read private post.
	if ( ap_user_can_view_private_post() ) {
		$post_status[] = 'private_post';
	}

	// Check if user can read moderate posts.
	if ( ap_user_can_view_moderate_post() ) {
		$post_status[] = 'moderate';
	}

	// Show trash posts to super admin.
	if ( is_super_admin() ) {
		$post_status[] = 'trash';
	}

	$status = "p.post_status IN ('" . implode( "','", $post_status ) . "')";

	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT p.ID FROM $wpdb->posts p LEFT JOIN $wpdb->ap_qameta qameta ON qameta.post_id = p.ID  WHERE p.post_type = 'answer' AND p.post_parent = %d AND ( $status OR ( p.post_author = %d AND p.post_status IN ('publish', 'private_post', 'trash', 'moderate') ) ) ORDER BY $orderby", $question_id, $user_id ) ); // db call okay, unprepared sql okay.

	$pos   = (int) array_search( $answer_id, $ids ) + 1; // lose comparison ok.
	$paged = ceil( $pos / ap_opt( 'answers_per_page' ) );
	wp_cache_set( $cache_key, $paged, 'ap_answer_position' );

	return $paged;
}

/**
 * Output answers pagination. Should be used inside a loop.
 *
 * @return void.
 * @deprecated 4.2.0
 */
function ap_answers_the_pagination() {
	_deprecated_function( __FUNCTION__, '4.2.0' );

	if ( get_query_var( 'answer_id' ) ) {
		echo '<a class="ap-all-answers" href="' . get_permalink( get_question_id() ) . '">' . sprintf( __( 'You are viewing 1 out of %d answers, click here to view all answers.', 'anspress-question-answer' ), ap_get_answers_count( get_question_id() ) ) . '</a>';
	} else {
		global $answers;
		$paged = ( get_query_var( 'ap_paged' ) ) ? get_query_var( 'ap_paged' ) : 1;
		ap_pagination( $paged, $answers->max_num_pages, '?ap_paged=%#%', get_permalink( get_question_id() ) . 'page/%#%/' );
	}
}

/**
 * Echo post status of a answer.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 * @deprecated 4.2.0 Replaced by \AnsPress\Template\status().
 */
function ap_answer_status( $_post = null ) {
	_deprecated_function( __FUNCTION__, '4.2.0', 'AnsPress\Template\status()' );
	ap_question_status( $_post );
}

/**
 * Question
 *
 * This class is for retrieving answers based on $args
 * @deprecated 4.2.0
 */
class Answers_Query extends WP_Query {

	/**
	 * Answer query arguments
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * Initialize class
	 *
	 * @param array $args Query arguments.
	 * @access public
	 * @since  2.0
	 * @since  4.1.2 Fixed: pagination issue.
	 */
	public function __construct( $args = array() ) {
		_deprecated_constructor( __CLASS__, '4.2.0' );

		global $answers;
		$paged    = (int) max( 1, get_query_var( 'ap_paged', 1 ) );
		$defaults = array(
			'question_id'            => get_question_id(),
			'ap_query'               => true,
			'ap_current_user_ignore' => false,
			'ap_answers_query'       => true,
			'showposts'              => ap_opt( 'answers_per_page' ),
			'paged'                  => $paged,
			'only_best_answer'       => false,
			'ignore_selected_answer' => false,
			'post_status'            => [ 'publish' ],
			'ap_order_by'            => ap_opt( 'answers_sort' ),
		);

		if ( get_query_var( 'answer_id' ) ) {
			$defaults['p'] = get_query_var( 'answer_id' );
		}

		$this->args                = wp_parse_args( $args, $defaults );
		$this->args['ap_order_by'] = sanitize_title( $this->args['ap_order_by'] );

		// Check if user can read private post.
		if ( ap_user_can_view_private_post() ) {
			$this->args['post_status'][] = 'private_post';
		}

		// Check if user can read moderate posts.
		if ( ap_user_can_view_moderate_post() ) {
			$this->args['post_status'][] = 'moderate';
		}

		// Show trash posts to super admin.
		if ( is_super_admin() ) {
			$this->args['post_status'][] = 'trash';
		}

		if ( isset( $this->args['question_id'] ) ) {
			$question_id = $this->args['question_id'];
		}

		if ( ! isset( $this->args['author'] ) && empty( $question_id ) && empty( $this->args['p'] ) ) {
			$this->args = [];
		} else {
			$this->args['post_parent'] = $question_id;
			$this->args['post_type']   = 'answer';
			$args                      = $this->args;

			/**
			 * Initialize parent class
			 */
			parent::__construct( $args );
		}
	}

	public function get_answers() {
		return parent::get_posts();
	}

	public function next_answer() {
		return parent::next_post();
	}

	/**
	 * Undo the pointer to next
	 */
	public function reset_next() {

		$this->current_post--;
		$this->post = $this->posts[ $this->current_post ];

		return $this->post;
	}

	public function the_answer() {
		global $post;
		$this->in_the_loop = true;

		if ( $this->current_post == -1 ) {
			   do_action_ref_array( 'ap_query_loop_start', array( &$this ) );
		}

		$post = $this->next_answer();

		setup_postdata( $post );
		anspress()->current_answer = $post;
	}

	public function have_answers() {
		return parent::have_posts();
	}

	public function rewind_answers() {
		parent::rewind_posts();
	}

	public function is_main_query() {
		return $this == anspress()->answers;
	}


	public function reset_answers_data() {
		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			anspress()->current_answer = $this->post;
		}
	}

	/**
	 * Utility method to get all the ids in this request
	 *
	 * @return array of mdia ids
	 */
	public function get_ids() {
		if ( $this->ap_ids ) {
			return;
		}

		$this->ap_ids = [
			'post_ids'   => [],
			'attach_ids' => [],
			'user_ids'   => [],
		];

		foreach ( (array) $this->posts as $_post ) {
			$this->ap_ids['post_ids'][] = $_post->ID;
			$this->ap_ids['attach_ids'] = array_filter( array_merge( explode( ',', $_post->attach ), $this->ap_ids['attach_ids'] ) );

			if ( ! empty( $_post->post_author ) ) {
				$this->ap_ids['user_ids'][] = $_post->post_author;
			}

			// Add activities user_id to array.
			if ( ! empty( $_post->activities ) && ! empty( $_post->activities['user_id'] ) ) {
				$this->ap_ids['user_ids'][] = $_post->activities['user_id'];
			}
		}

		// Unique ids only.
		foreach ( (array) $this->ap_ids as $k => $ids ) {
			$this->ap_ids[ $k ] = array_unique( $ids );
		}
	}



	/**
	 * Pre fetch current users vote on all answers
	 *
	 * @since 3.1.0
	 * @since 4.1.2 Prefetch posts activity.
	 */
	public function pre_fetch() {
		$this->get_ids();
		ap_prefetch_recent_activities( $this->ap_ids['post_ids'], 'a_id' );
		ap_user_votes_pre_fetch( $this->ap_ids['post_ids'] );
		ap_post_attach_pre_fetch( $this->ap_ids['attach_ids'] );

		if ( ! empty( $this->ap_ids['user_ids'] ) ) {
			ap_post_author_pre_fetch( $this->ap_ids['user_ids'] );
		}

		do_action( 'ap_pre_fetch_answer_data', $this->ap_ids );
	}
}

/**
 * Get active list filter by filter key.
 *
 * @param  string|null $filter  Filter key.
 * @return false|string|array
 * @since  4.0.0
 * @deprecated 4.2.0
 *
 * @todo Deprecate this.
 */
function ap_get_current_list_filters( $filter = null ) {
	_deprecated_function( __FUNCTION__, '4.2.0', 'AnsPress\Template\get_current_questions_sorting()');

	$get_filters = [];
	$filters     = array_keys( ap_get_list_filters() );

	if ( in_array( 'order_by', $filters, true ) ) {
		$get_filters['order_by'] = ap_opt( 'question_order_by' );
	}

	if ( empty( $filters ) || ! is_array( $filters ) ) {
		$filters = [];
	}

	foreach ( (array) $filters as $k ) {
		$val = ap_isset_post_value( $k );

		if ( ! empty( $val ) ) {
			$get_filters[ $k ] = $val;
		}
	}

	if ( null !== $filter ) {
		return ! isset( $get_filters[ $filter ] ) ? null : $get_filters[ $filter ];
	}

	return $get_filters;
}

/**
 * Check if current page is search page
 *
 * @return boolean
 * @deprecated 4.2.0 Replace by ap_is_search().
 */
function is_ap_search() {
	_deprecated_function( __FUNCTION__, '4.2.0', 'ap_is_search');

	if ( is_anspress() && get_query_var( 'ap_s' ) ) {
		return true;
	}

	return false;
}

/**
 * Get current user id for AnsPress profile.
 *
 * This function must be used only in AnsPress profile. This function checks for
 * user ID in queried object, hence if not in user page
 *
 * @return integer Always returns 0 if not in AnsPress profile page.
 * @since 4.1.1
 */
function ap_current_user_id() {
	_deprecated_function( __FUNCTION__, '4.2.0', 'ap_get_displayed_user_id' );

	if ( ap_current_page( 'profile' ) ) {
		$query_object = get_queried_object();

		if ( $query_object instanceof WP_User ) {
			return $query_object->ID;
		}
	}

	return get_current_user_id();
}

/**
 * Count all answers excluding best answer.
 *
 * @return int
 * @deprecated 4.2.0
 */
function ap_count_other_answer( $question_id = false ) {
	_deprecated_function( __FUNCTION__, '4.2.0' );

	if ( ! $question_id ) {
		$question_id = get_question_id();
	}

	$count = ap_get_answers_count( $question_id );

	if ( ap_have_answer_selected( $question_id ) ) {
		return (int) ( $count - 1 );
	}

	return (int) $count;
}

/**
 * Check if current page is question page.
 *
 * @return boolean
 * @since 0.0.1
 * @since 4.1.0 Also check and return true if singular question.
 * @deprecated 4.2.0 Replaced by ap_is_single_question().
 */
function is_question() {
	_deprecated_function( __FUNCTION__, '4.2.0', 'ap_is_single_question' );
	if ( is_singular( 'question' ) || ap_is_query_name( 'single-question' ) ) {
		return true;
	}

	return false;
}
