<?php
/**
 * Question query.
 *
 * @package AnsPress
 * @subpackage Classes
 * @author Rahul Aryan <rah12@live.com>
 */

use AnsPress\Template;

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * QA Query class
 *
 * @since 3.1.0
 */
class Question_Query extends WP_Query {
	/**
	 * Store post type.
	 *
	 * @var string
	 */
	private $post_type;

	public $count_request;

	/**
	 * Initialize class.
	 *
	 * @param array|string $args Query args.
	 * @since unknown
	 * @since 4.1.5 Include future questions if user have privilege.
	 */
	public function __construct( $args = [] ) {
		if ( is_front_page() ) {
			$paged = ( isset( $_GET['ap_paged'] ) ) ? (int) $_GET['ap_paged'] : 1; // input var ok.
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		if ( isset( $args['post_parent'] ) ) {
			$post_parent = $args['post_parent'];
		} else {
			$post_parent = ( get_query_var( 'parent' ) ) ? get_query_var( 'parent' ) : false;
		}

		if ( ! isset( $args['ap_order_by'] ) ) {
			$args['ap_order_by'] = ap_get_current_list_filters( 'order_by', 'active' );
		}

		$defaults = array(
			'showposts'              => ap_opt( 'question_per_page' ),
			'paged'                  => $paged,
			'ap_query'               => true,
			'ap_order_by'            => 'active',
			'ap_question_query'      => true,
			'post_status'            => [ 'publish' ],
			'ap_current_user_ignore' => false,
		);

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

		// Check if user can read moderate posts.
		if ( ap_user_can_view_future_post() ) {
			$this->args['post_status'][] = 'future';
		}

		// Show trash posts to super admin.
		if ( is_super_admin() ) {
			$this->args['post_status'][] = 'trash';
		}

		$this->args['post_status'] = array_unique( $this->args['post_status'] );

		// Show only the unpublished post of author.
		if ( isset( $args['ap_show_unpublished'] ) && true === $this->args['ap_show_unpublished'] ) {
			$this->args['ap_current_user_ignore'] = true;
			$this->args['author']                 = get_current_user_id();
			$this->args['post_status']            = [ 'moderate', 'pending', 'draft', 'trash' ];
		}

		if ( $post_parent ) {
			$this->args['post_parent'] = $post_parent;
		}

		if ( '' !== get_query_var( 'ap_s' ) ) {
			$this->args['s'] = ap_sanitize_unslash( 'ap_s', 'query_var' );
		}

		$this->args['post_type'] = 'question';
		parent::__construct( $this->args );
	}

	/**
	 * Get posts.
	 */
	public function get_questions() {
		return parent::get_posts();
	}

	/**
	 * Update loop index to next post.
	 */
	public function next_question() {
		return parent::next_post();
	}

	/**
	 * Undo the pointer to next.
	 */
	public function reset_next() {
		$this->current_post--;
		$this->post = $this->posts[ $this->current_post ];
		return $this->post;
	}

	/**
	 * Set current question in loop.
	 */
	public function the_question() {
		global $post;
		$this->in_the_loop = true;

		if ( -1 === $this->current_post ) {
			do_action_ref_array( 'ap_query_loop_start', array( &$this ) );
		}

		$post = $this->next_question(); // override ok.

		setup_postdata( $post );
		anspress()->current_question = $post;
	}

	/**
	 * Check if loop have questions.
	 *
	 * @return boolean
	 */
	public function have_questions() {
		return parent::have_posts();
	}

	/**
	 * Rewind questions in loop.
	 */
	public function rewind_questions() {
		parent::rewind_posts();
	}

	/**
	 * Check if main question query.
	 *
	 * @return boolean
	 */
	public function is_main_query() {
		return anspress()->questions_query === $this;
	}

	/**
	 * Reset current question in loop.
	 */
	public function reset_questions_data() {
		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			anspress()->current_question = $this->post;
		}
	}

	/**
	 * Utility method to get all the ids in this request
	 *
	 * @return array of question ids
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
			$this->ap_ids['attach_ids'] = array_merge( explode( ',', $_post->attach ), $this->ap_ids['attach_ids'] );
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
	 * @since 4.1.2 Prefetch post activities.
	 */
	public function pre_fetch() {
		$this->get_ids();
		ap_prefetch_recent_activities( $this->ap_ids['post_ids'] );
		ap_user_votes_pre_fetch( $this->ap_ids['post_ids'] );
		ap_post_attach_pre_fetch( $this->ap_ids['attach_ids'] );

		// Pre fetch users.
		if ( ! empty( $this->ap_ids['user_ids'] ) ) {
			ap_post_author_pre_fetch( $this->ap_ids['user_ids'] );
		}

		do_action( 'ap_pre_fetch_question_data', $this->ap_ids );
	}
}

/**
 * Get posts with qameta fields.
 *
 * @param  mixed $post Post object.
 * @return object
 */
function ap_get_post( $post = null ) {
	if ( empty( $post ) && isset( $GLOBALS['post'] ) ) {
		$post = $GLOBALS['post']; // override ok.
	}

	if ( $post instanceof WP_Post || is_object( $post ) ) {
		$_post = $post;
	} elseif ( false !== $post_o = wp_cache_get( $post, 'posts' ) ) {
		$_post = $post_o;
	} else {
		$_post = WP_Post::get_instance( $post );
	}

	if ( $_post && ! isset( $_post->ap_qameta_wrapped ) ) {
		$_post = ap_append_qameta( $_post );
		wp_cache_set( $_post->ID, $_post, 'posts' );
	}

	return $_post;
}

/**
 * Check if there is post in loop
 *
 * @return boolean
 */
function ap_have_questions() {
	$q = anspress()->questions_query;
	if ( ! $q instanceof \WP_Query ) {
		return false;
	}

	$have_posts = anspress()->questions_query->have_posts();

	// Reset the post data when finished.
	if ( empty( $have_posts ) ) {
		wp_reset_postdata();
	}

	return $have_posts;
}

/**
 * Set current question in loop.
 *
 * @return Object
 */
function ap_the_question() {
	return anspress()->questions_query->the_post();
}

/**
 * Return total numbers of questions found.
 *
 * @return integer
 */
function ap_total_questions_found() {
	return anspress()->questions_query->found_posts;
}

/**
 * Reset original question query.
 *
 * @return boolean
 * @since unknown
 * @since 4.1.0 Check if global `$questions` exists.
 */
function ap_reset_question_query() {
	if ( anspress()->questions_query ) {
		return anspress()->questions_query->reset_postdata();
	}
}

/**
 * Return link of user profile page
 *
 * @return string
 */
function ap_get_profile_link() {
	global $post;

	if ( ! $post ) {
		return false;
	}

	return ap_user_link( $post->post_author );
}

/**
 * Echo user profile link
 */
function ap_profile_link() {
	echo ap_get_profile_link(); // xss ok.
}

/**
 * Return question author avatar.
 *
 * @param  integer $size Avatar size.
 * @param  mixed   $_post Post.
 * @return string
 */
function ap_get_author_avatar( $size = 45, $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( ! $_post ) {
		return;
	}

	$author = 0 == $_post->post_author ? 'anonymous_' . $_post->ID : $_post->post_author; // @codingStandardsIgnoreLine

	// @codingStandardsIgnoreLine
	if ( false !== strpos( $author, 'anonymous' ) && is_array( $_post->fields ) && ! empty( $_post->fields['anonymous_name'] ) ) {
		$author = $_post->fields['anonymous_name'];
	}

	return get_avatar( $author, $size );
}

/**
 * Echo question author avatar.
 *
 * @param  integer $size Avatar size.
 * @param  mixed   $_post Post.
 */
function ap_author_avatar( $size = 45, $_post = null ) {
	echo ap_get_author_avatar( $size, $_post ); // xss ok.
}

/**
 * Return total published answer count.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return integer
 */
function ap_get_answers_count( $_post = null ) {
	$_post = ap_get_post( $_post );
	return $_post->answers;
}

/**
 * Echo total votes count of a post.
 *
 * @param  mixed $_post Post ID, Object or null.
 */
function ap_answers_count( $_post = null ) {
	echo ap_get_answers_count( $_post ); // xss ok.
}

/**
 * Return count of net vote of a question.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return integer
 */
function ap_get_votes_net( $_post = null ) {
	$_post = ap_get_post( $_post );
	return (int) $_post->votes_net;
}

/**
 * Echo count of net vote of a question.
 *
 * @param  mixed $_post Post ID, Object or null.
 */
function ap_votes_net( $_post = null ) {
	echo ap_get_votes_net( $_post ); // xss ok.
}


/**
 * Echo post status of a question.
 *
 * @param  mixed $_post Post ID, Object or null.
 */
function ap_question_status( $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( 'publish' === $_post->post_status ) {
		return;
	}

	$status_obj = get_post_status_object( $_post->post_status );
	echo '<span class="ap-post-status ' . esc_attr( $_post->post_status ) . '">' . esc_attr( $status_obj->label ) . '</span>';
}

/**
 * Question meta to display.
 *
 * @param false|integer $question_id question id.
 * @since unknown
 * @since 4.1.2 Use @see ap_recent_activity() for showing activity.
 * @since 4.1.8 Show short views count.
 * @since 4.2.0 Use `get_question_id`.
 */
function ap_question_metas( $question_id = 0 ) {
	$question_id = get_question_id( $question_id );
	$metas       = [];

	// If featured question.
	if ( ap_is_featured_question( $question_id ) ) {
		$metas['featured'] = __( 'Featured', 'anspress-question-answer' );
	}

	if ( ap_have_answer_selected( $question_id ) ) {
		$metas['solved'] = '<i class="apicon-check"></i><i>' . __( 'Solved', 'anspress-question-answer' ) . '</i>';
	}

	$view_count     = ap_get_post_field( 'views', $question_id );
	$metas['views'] = '<i class="apicon-eye"></i><i>' . sprintf( __( '%s views', 'anspress-question-answer' ),  ap_short_num( $view_count ) ) . '</i>';

	if ( is_question() ) {
		$last_active     = ap_get_last_active( $question_id );
		$metas['active'] = '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime="' . mysql2date( 'c', $last_active ) . '">' . $last_active . '</time></i>';
	}

	if ( ! is_question() ) {
		$metas['history'] = '<i class="apicon-pulse"></i>' . ap_recent_activity( $question_id, false );
	}

	/**
	 * Used to filter question display meta.
	 *
	 * @param array $metas
	 */
	$metas = apply_filters( 'ap_display_question_metas', $metas, $question_id );

	$output = '';
	if ( ! empty( $metas ) && is_array( $metas ) ) {
		foreach ( $metas as $meta => $display ) {
			$output .= "<span class='ap-display-meta-item {$meta}'>{$display}</span>";
		}
	}

	echo $output; // xss ok.
}

/**
 * Get recent activity of a post.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return string
 */
function ap_get_recent_post_activity( $_post = null ) {
	$_post = ap_get_post( $_post );
	return ap_latest_post_activity_html( $_post->ID );
}

/**
 * Echo recent activity of a post.
 */
function ap_recent_post_activity() {
	echo ap_get_recent_post_activity(); // xss ok.
}

/**
 * Get last active time in human readable format.
 *
 * @param  mixed $post_id Post ID/Object.
 * @return string
 * @since  2.4.8 Convert mysql date to GMT.
 */
function ap_get_last_active( $post_id = null ) {
	$p    = ap_get_post( $post_id );
	$date = ! empty( $p->last_updated ) ? $p->last_updated : $p->post_modified_gmt;
	return ap_human_time( get_gmt_from_date( $date ), false );
}

/**
 * Echo last active time in human readable format.
 *
 * @param  mixed $post_id Post ID/Object.
 * @since  2.4.8 Convert mysql date to GMT.
 */
function ap_last_active( $post_id = null ) {
	echo esc_attr( ap_get_last_active( $post_id ) );
}


/**
 * Check if question have answer selected.
 *
 * @param  mixed $question Post object or ID.
 * @return boolean
 */
function ap_have_answer_selected( $question = null ) {
	$question = ap_get_post( $question );

	return ! empty( $question->selected_id );
}

/**
 * Return the ID of selected answer from a question.
 *
 * @param object|null|integer $_post Post object, ID or null.
 * @return integer
 */
function ap_selected_answer( $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( ! $_post ) {
		return false;
	}

	return $_post->selected_id;
}

/**
 * Check if current answer is selected as a best
 *
 * @param mixed $_post Post.
 * @return boolean
 * @since 2.1
 */
function ap_is_selected( $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( ! $_post ) {
		return false;
	}

	return (bool) $_post->selected;
}

/**
 * Return post time.
 *
 * @param  mixed  $_post   Post ID, Object or null.
 * @param  string $format Date format.
 * @return String
 */
function ap_get_time( $_post = null, $format = '' ) {
	$_post = ap_get_post( $_post );

	if ( ! $_post ) {
		return;
	}

	return get_post_time( $format, true, $_post->ID, true );
}

/**
 * Check if current post is marked as featured
 *
 * @param  boolean|integer $question    Question ID to check.
 * @return boolean
 * @since 2.2.0.1
 */
function ap_is_featured_question( $question = null ) {
	$question = ap_get_post( $question );
	return isset( $question->featured ) ? (bool) $question->featured : false;
}

/**
 * Check if post have terms of a taxonomy.
 *
 * @param  boolean|integer $post_id  Post ID.
 * @param  string          $taxonomy Taxonomy name.
 * @return boolean
 */
function ap_post_have_terms( $post_id = false, $taxonomy = 'question_category' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$terms = get_the_terms( $post_id, $taxonomy );

	if ( ! empty( $terms ) ) {
		return true;
	}

	return false;
}

/**
 * Check if question or answer have attachments.
 *
 * @param  mixed $_post Post.
 * @return boolean
 */
function ap_have_attach( $_post = null ) {
	$_post = ap_get_post( $_post );
	if ( ! empty( $_post->attach ) ) {
		return true;
	}
	return false;
}

/**
 * Get attachment ids of a question or answer.
 *
 * @param  mixed $_post Post.
 * @return string|boolean
 */
function ap_get_attach( $_post = null ) {
	$_post = ap_get_post( $_post );
	if ( ! empty( $_post->attach ) ) {
		return explode( ',', $_post->attach );
	}
	return false;
}


/**
 * Get latest activity of question or answer.
 *
 * @param  mixed           $post_id Question or answer ID.
 * @param  integer|boolean $answer_activities Show answers activities as well.
 * @return string
 */
function ap_latest_post_activity_html( $post_id = false, $answer_activities = false ) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	$_post    = ap_get_post( $post_id );
	$activity = $_post->activities;

	if ( false !== $answer_activities && ! empty( $_post->activities['child'] ) ) {
		$activity = $_post->activities['child'];
	}

	if ( ! empty( $activity ) && ! empty( $activity['date'] ) ) {
		$activity['date'] = get_gmt_from_date( $activity['date'] );
	}

	if ( false === $answer_activities && ( ! isset( $activity['type'] ) || in_array( $activity['type'], [ 'new_answer', 'new_question' ], true ) ) ) {
		return;
	}

	$html = '';

	if ( $activity ) {
		$user_id       = ! empty( $activity['user_id'] ) ? $activity['user_id'] : 0;
		$activity_type = ! empty( $activity['type'] ) ? $activity['type'] : '';
		$html         .= '<span class="ap-post-history">';
		$html         .= '<a href="' . ap_user_link( $user_id ) . '" itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name">' . ap_user_display_name( $user_id ) . '</span></a>';
		$html         .= ' ' . ap_activity_short_title( $activity_type );

		if ( ! empty( $activity['date'] ) ) {
			$html .= ' <a href="' . get_permalink( $_post ) . '">';
			$html .= '<time itemprop="dateModified" datetime="' . mysql2date( 'c', $activity['date'] ) . '">' . ap_human_time( $activity['date'], false ) . '</time>';
			$html .= '</a>';
		}

		$html .= '</span>';
	}

	if ( $html ) {
		return apply_filters( 'ap_latest_post_activity_html', $html );
	}

	return false;
}

/**
 * The main answer loop.
 *
 * @param string|array $args Arguments.
 * @return boolean
 * @since 2.0
 * @since 4.2.0 Improved logic.
 */
function ap_get_answers( $args = '' ) {
	global $wp_rewrite;

	$paged    = (int) max( 1, get_query_var( 'ap_paged', 1 ) );
	$order_by = Template\get_current_answer_sorting();
	$status   = [ 'publish', 'private_post' ];

	// Default query args
	$default = array(
		'post_type'              => 'answer', // Only replies
		'post_parent'            => ( is_question() ? get_question_id(): 'any' ), // Of this topic
		'paged'                  => $paged, // On this page
		'ignore_sticky_posts'    => true,
		'ap_query'               => true,
		'ap_current_user_ignore' => false,
		'ap_answers_query'       => true,
		'posts_per_page'         => ap_opt( 'answers_per_page' ),
		'only_best_answer'       => false,
		'ignore_selected_answer' => false,
		'post_status'            => $status,
		'ap_order_by'            => $order_by,
	);

	if ( 'unpublished' === $default['ap_order_by'] ) {
		$default['ap_order_by'] = 'active';
		$default['post_status'] = [ 'moderate', 'trash', 'future' ];
	}

	// Parse arguments against default values
	$r = wp_parse_args( $args, $default );

	// Set posts_per_page value if replies are threaded
	$per_page = $r['posts_per_page'];

	$ap = anspress();
	$ap->answers_query = new WP_Query( $r );

	// Add pagination values to query object
	$ap->answers_query->posts_per_page = $per_page;
	$ap->answers_query->paged          = $r['paged'];
	$ap->answers_query->is_home        = false;

	// Only add pagination if query returned results
	if ( (int) $ap->answers_query->found_posts && (int) $ap->answers_query->posts_per_page ) {

		// Make our pagination pretty.
		if ( $wp_rewrite->using_permalinks() ) {
			$base = get_permalink( $r['post_parent'] );
			$base = trailingslashit( $base ) . user_trailingslashit( 'answer-page-%#%/' );
		} else {
			$base = add_query_arg( 'ap_paged', '%#%' );
		}

		// Figure out total pages
		$total_pages = ceil( (int) $ap->answers_query->found_posts / (int) $per_page );

		// Add pagination to query object
		$ap->answers_query->pagination_links = paginate_links( apply_filters( 'ap_answers_pagination', array(
			'base'      => $base,
			'format'    => '',
			'total'     => $total_pages,
			'current'   => (int) $ap->answers_query->paged,
			'prev_text' => is_rtl() ? '&raquo;' : '&laquo;',
			'next_text' => is_rtl() ? '&laquo;' : '&raquo;',
			'mid_size'  => 1,
		) ) );
	}

	// Prefetch ids.
	$ids = [];

	foreach ( $ap->answers_query->posts as $p ) {
		$ids['post_ids'][] = $p->ID;

		if ( ! empty( $p->post_author ) ) {
			$ids['user_ids'][] = $p->post_author;
		}
	}

	// Prefetch data.
	if ( ! empty( $ids['post_ids'] ) ) {
		ap_prefetch_recent_activities( $ids['post_ids'], 'a_id' );
		ap_user_votes_pre_fetch( $ids['post_ids'] );
		ap_post_author_pre_fetch( $ids['user_ids'] );
	}

	/**
	 * Filter return of function `ap_get_answers`.
	 *
	 * @param boolean   $have_posts Have posts.
	 * @param \WP_Query $query      WP_Query.
	 *
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_get_answers', $ap->answers_query->have_posts(), $ap->answers_query );
}

/**
 * The main question loop.
 *
 * @param string|array $args Arguments.
 * @return boolean
 * @since 4.2.0
 *
 * @todo Migrate old private_post
 */
function ap_get_questions( $args = '' ) {
	global $wp_rewrite;

	$paged     = (int) max( 1, get_query_var( 'paged', 1 ) );
	$order_by  = Template\get_current_questions_sorting();
	$filter_by = Template\get_current_questions_filter();
	$status    = [ 'publish', 'private' ];

	// Default query args
	$default = array(
		'paged'                  => $paged,
		'ignore_sticky_posts'    => true,
		'ap_query'               => true,
		'ap_current_user_ignore' => false,
		'ap_question_query'      => true,
		'posts_per_page'         => ap_opt( 'question_per_page' ),
		'post_status'            => $status,
		'ap_order_by'            => $order_by,
		'ap_filter_by'           => 'all',
		'perm'                   => 'readable',
	);

	if ( 'unpublished' === $filter_by ) {
		$default['post_status'] = [ 'moderate', 'trash', 'future' ];
	} else {
		$default['ap_filter_by'] = $filter_by;
	}

	// Search string.
	$search_str = ap_get_search_terms();
	if ( ! empty( $search_str ) ) {
		$default['s'] = $search_str;
	}

	/**
	 * Filter for overriding default arguments of `ap_get_questions`.
	 *
	 * @since 4.2.0
	 */
	$default = apply_filters( 'ap_get_questions_default_args', $default );

	// Parse arguments against default values
	$r = wp_parse_args( $args, $default );

	$r['post_type'] = 'question';

	// Set posts_per_page value if replies are threaded
	$posts_per_page = $r['posts_per_page'];

	$ap = anspress();
	$ap->questions_query = new WP_Query( $r );

	// Add pagination values to query object
	$ap->questions_query->posts_per_page = $posts_per_page;
	$ap->questions_query->paged          = $r['paged'];
	$ap->questions_query->is_home        = false;

	// Only add pagination if query returned results
	if ( (int) $ap->questions_query->found_posts && (int) $ap->questions_query->posts_per_page ) {

		// Make our pagination pretty.
		if ( $wp_rewrite->using_permalinks() ) {
			$base = ap_get_link_to( '/' );
			$base = trailingslashit( $base ) . user_trailingslashit( $wp_rewrite->pagination_base . '/%#%/' );
		} else {
			$base = add_query_arg( 'paged', '%#%' );
		}

		// Figure out total pages
		$total_pages = ceil( (int) $ap->questions_query->found_posts / (int) $posts_per_page );

		// Add pagination to query object
		$ap->questions_query->pagination_links = paginate_links( apply_filters( 'ap_questions_pagination', array(
			'base'      => $base,
			'format'    => '',
			'total'     => $total_pages,
			'current'   => (int) $ap->questions_query->paged,
			'prev_text' => is_rtl() ? '&raquo;' : '&laquo;',
			'next_text' => is_rtl() ? '&laquo;' : '&raquo;',
			'mid_size'  => 1,
		) ) );
	}

	// Return object
	return apply_filters( 'ap_get_questions', $ap->questions_query->have_posts(), $ap->questions_query );
}

/**
 * Check if currently post in loop is an answer.
 *
 * @return boolean
 * @since 4.2.0
 */
function ap_is_answer() {
	$ap = anspress();

	if ( ! empty( $ap->answers_query->in_the_loop ) && isset( $ap->answers_query->post->ID ) ) {
		return true;
	}

	return false;
}


/**
 * Get the ID of answer in a loop.
 *
 * @param integer $answer_id Answer id.
 * @return integer
 * @since 4.2.0
 */
function ap_get_answer_id( $answer_id = 0 ) {
	$ap = anspress();

	if ( ! empty( $answer_id ) && is_numeric( $answer_id ) ) {
		$id = $answer_id;
	} elseif ( ! empty( $ap->answers_query->in_the_loop ) && isset( $ap->answers_query->post->ID ) ) {
		$id = $ap->answers_query->post->ID;
	} elseif ( is_singular( 'answer' ) ) {
		$id = get_queried_object_id();
	} elseif ( false !== $qv = get_query_var( 'answer_id', false ) ) {
		$id = $qv;
	}

	return $id;
}

/**
 * Get an answer by ID
 *
 * @param  integer $answer_id Answers ID.
 * @return \WP_Post
 * @since 2.1
 * @since 4.2.0 Moved from `answer-loop.php` file.
 */
function ap_get_answer( $answer_id ) {
	return get_post( $answer_id );
}

/**
 * Check if there are posts in the loop.
 *
 * @return boolean
 * @since 4.2.0 Moved from `answer-loop.php` file.
 */
function ap_have_answers() {
	$q = anspress()->answers_query;
	if ( ! $q instanceof \WP_Query ) {
		return false;
	}

	$have_posts = anspress()->answers_query->have_posts();

	// Reset the post data when finished.
	if ( empty( $have_posts ) ) {
		wp_reset_postdata();
	}

	return $have_posts;
}

/**
 * Loads up the current answer in the loop.
 *
 * @return void
 * @since 4.2.0 Moved from `answer-loop.php` file.
 */
function ap_the_answer() {
	return anspress()->answers_query->the_post();
}

/**
 * Count total numbers of posts found in a loop.
 *
 * @return integer
 * @since 4.2.0
 */
function ap_total_answers_found() {
	return anspress()->answers_query->found_posts;
}

/**
 * Return numbers of published answers.
 *
 * @param  integer $question_id Question ID.
 * @return integer
 * @since 4.2.0 Moved from `answer-loop.php` file.
 */
function ap_count_published_answers( $question_id ) {
	global $wpdb;

	$query = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND post_status IN ('publish', 'private_post') AND post_type = 'answer'", $question_id );

	$key = md5( $query );

	$cache = wp_cache_get( $key, 'counts' );
	if ( false !== $cache ) {
		return $cache;
	}

	$count = $wpdb->get_var( $query );
	wp_cache_set( $key, $count, 'counts' );
	return $count;
}
