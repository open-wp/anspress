<?php
/**
 * Class for anspress theme
 *
 * @package      AnsPress
 * @subpackage   Theme Hooks
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-3.0+
 * @link         https://anspress.io
 * @copyright    2014 Rahul Aryan
 */

use AnsPress\Shortcodes;
use AnsPress\Template;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Holds all hooks related to frontend layout/theme
 */
class AnsPress_Theme {
	/**
	 * Function get called on init
	 */
	public static function init_actions() {
		// Register anspress shortcode.
		//add_shortcode( 'anspress', array( AnsPress_BasePage_Shortcode::get_instance(), 'anspress_sc' ) );

		// Register question shortcode.
		add_shortcode( 'question', array( AnsPress_Question_Shortcode::get_instance(), 'anspress_question_sc' ) );
	}
	/*
	 * The main filter used for theme compatibility and displaying custom AnsPress
	 * theme files.
	 *
	 * @param string $template Current template file.
	 * @return string Template file to use.
	 *
	 * @since 4.2.0
	 */
	public static function template_include( $template = '' ) {
		return apply_filters( 'ap_template_include', $template );
	}

	/**
	 * Redirect single answer and comment to question page.
	 *
	 * @param  string $template Template.
	 * @return string
	 * @since  4.2.0
	 */
	public static function redirect_answer( $template = '' ) {
		if ( is_singular( 'answer' ) && ! ap_current_page( 'edit' ) ) {
			wp_redirect( ap_get_permalink( ap_get_answer_id() ), 301 );
			exit();
		}

		$comment_id = get_query_var( 'ap_comment_id', 0 );

		if ( 0 !== $comment_id ) {
			$comment      = get_comment( $comment_id );
			$post_link    = ap_get_permalink( $comment->comment_post_ID, false );
			$comment_link = user_trailingslashit( $post_link ) . '#comment-' . $comment_id;

			wp_redirect( $comment_link, 301 );
			exit();
		}

		return $template;
	}

	/**
	* Reset main query vars and filter 'the_content' to output a AnsPress
	* template part as needed.
	*
	* @param string $template
	* @return string
	*
	* @since 4.2.0
	*/
	public static function template_include_theme_compat( $template = '' ) {

		if ( ap_current_page( 'archive' ) ) {
			// Page exists where this archive should be
			$page = get_page_by_path( ap_base_page_slug() );

			// Should we replace the content.
			if ( empty( $page->post_content ) ) {
				$new_content = Shortcodes::get_instance()->display_archive();
			} else {
				$new_content = apply_filters( 'the_content', $page->post_content );
			}

			if ( ap_is_search() ) {
				$new_title = sprintf(
					apply_filters( 'ap_search_the_title', __( 'Search questions: "%s"', 'anspress-question-answer' ) ),
					ap_get_search_terms()
				);
			} else {
				$new_title = apply_filters( 'the_title', $page->post_title );
			}

			// Reset post
			ap_theme_compat_reset_post( array(
				'ID'             => ! empty( $page->ID ) ? $page->ID : 0,
				'post_title'     => $new_title,
				'post_author'    => 0,
				'post_date'      => 0,
				'post_content'   => $new_content,
				'post_type'      => 'question',
				'post_status'    => 'publish',
				'is_archive'     => true,
				'is_single'      => false,
				'comment_status' => 'closed',
			) );

		} elseif ( ap_current_page( 'edit' ) ) {
			$status_header = 200;
			$post_id       = ap_sanitize_unslash( 'id', 'r' );

			// Check if user have permission to read and add proper status header code.
			if ( ! ap_user_can_read_post( $post_id ) ) {
				$status_header = 403;
			}

			$_post = ap_get_post( $post_id );

			$type_label = 'question' === $_post->post_type ? __( 'question', 'anspress-question-answer' ) : __( 'answer', 'anspress-question-answer' );

			ap_theme_compat_reset_post( array(
				'ID'             => $_post->ID,
				'post_title'     => sprintf( __( 'Editing %s', 'anspress-question-answer' ), $type_label ),
				'post_author'    => $_post->post_author,
				'post_date'      => 0,
				'post_content'   => Shortcodes::get_instance()->display_edit(),
				'post_type'      => 'question',
				'post_status'    => $_post->post_status,
				'is_single'      => true,
				'comment_status' => 'closed',
				'status_header'  => $status_header,
			) );
		} elseif ( ap_current_page( 'question' ) ) {
			$status_header = 200;

			// Check if user have permission to read and add proper status header code.
			$answer_id = get_query_var( 'answer_id', false );
			if ( false !== $answer_id && ! ap_user_can_read_answer( $answer_id ) ) {
				$status_header = 403;
			} elseif ( ! ap_user_can_read_question( get_question_id() ) ) {
				$status_header = 403;
			}

			ap_theme_compat_reset_post( array(
				'ID'             => get_question_id(),
				'post_title'     => get_the_title( get_question_id() ),
				'post_author'    => get_post_field( 'post_author', get_question_id() ),
				'post_date'      => 0,
				'post_content'   => Shortcodes::get_instance()->display_question(),
				'post_type'      => 'question',
				'post_status'    => get_post_status( get_question_id() ),
				'is_single'      => true,
				'comment_status' => 'closed',
				'status_header'  => $status_header,
			) );
		} elseif ( ap_current_page( 'ask' ) ) {
			// Ask page.
			$page_id = ap_main_pages_id( 'ask' );

			$page = get_page( $page_id );

			// Replace the content.
			if ( empty( $page->post_content ) ) {
				$new_content =  Shortcodes::get_instance()->display_ask();
			} else {
				$new_content = apply_filters( 'the_content', $page->post_content );
			}

			// Replace the title.
			if ( empty( $page->post_title ) ) {
				$new_title = __( 'Post a question', 'anspress-question-answer' );
			} else {
				$new_title = apply_filters( 'the_title', $page->post_title );
			}

			ap_theme_compat_reset_post( array(
				'ID'             => ! empty( $page->ID ) ? $page->ID : 0,
				'post_title'     => $new_title,
				'post_author'    => 0,
				'post_date'      => 0,
				'post_content'   => $new_content,
				'post_type'      => 'question',
				'post_status'    => 'publish',
				'is_single'      => true,
				'comment_status' => 'closed'
			) );
		} elseif ( ap_current_page( 'activities' ) ) {

			// Ask page.
			$page_id = ap_main_pages_id( 'activities' );

			$page = get_page( $page_id );

			// Replace the content.
			if ( empty( $page->post_content ) ) {
				$new_content =  Shortcodes::get_instance()->display_activities();
			} else {
				$new_content = apply_filters( 'the_content', $page->post_content );
			}

			// Replace the title.
			if ( empty( $page->post_title ) ) {
				$new_title = __( 'Questions activities', 'anspress-question-answer' );
			} else {
				$new_title = apply_filters( 'the_title', $page->post_title );
			}

			ap_theme_compat_reset_post( array(
				'ID'             => ! empty( $page->ID ) ? $page->ID : 0,
				'post_title'     => $new_title,
				'post_author'    => 0,
				'post_date'      => 0,
				'post_content'   => $new_content,
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'is_single'      => true,
				'comment_status' => 'closed'
			) );
		}

		if ( true === anspress()->theme_compat->active ) {
			ap_remove_all_filters( 'the_content' );
		}

		return $template;
   	}

	/**
	 * AnsPress theme function as like WordPress theme function.
	 *
	 * @return void
	 */
	public static function includes_theme() {
		require_once ap_get_theme_location( 'functions.php' );
	}

	/**
	 * Add answer-seleted class in post_class.
	 *
	 * @param  array $classes Post class attribute.
	 * @return array
	 * @since 2.0.1
	 * @since 4.1.8 Fixes #426: Undefined property `post_type`.
	 */
	public static function question_answer_post_class( $classes ) {
		global $post;

		if ( ! $post ) {
			return $classes;
		}

		if ( 'question' === $post->post_type ) {
			if ( ap_have_answer_selected( $post->ID ) ) {
				$classes[] = 'answer-selected';
			}

			if ( ap_is_featured_question( $post->ID ) ) {
				$classes[] = 'featured-question';
			}

			$classes[] = 'answer-count-' . ap_get_answers_count();

		}

		return $classes;
	}

	/**
	 * Add anspress classes to body.
	 *
	 * @param  array $classes Body class attribute.
	 * @return array
	 * @since 2.0.1
	 */
	public static function body_class( $classes ) {
		// Add anspress class to body.
		if ( is_anspress() ) {
			$classes[] = 'anspress-content';
			$classes[] = 'ap-page-' . ap_current_page();
		}

		return $classes;
	}

	/**
	 * Filter wp_title.
	 *
	 * @param string $title WP page title.
	 * @return string
	 * @since 4.1.1 Do not override title of all pages except single question.
	 */
	public static function ap_title( $title ) {
		if ( is_anspress() ) {
			remove_filter( 'wp_title', [ __CLASS__, 'ap_title' ] );

			if ( ap_is_search() ) {
				$title = sprintf(
					__( 'Search question: "%s"', 'anspress-questions-answer' ),
					ap_get_search_terms()
				);
			} elseif ( is_question() ) {
				$title = ap_question_title_with_solved_prefix() . ' | ';
			}
		}

		/**
		 * Filter AnsPress page titles.
		 */
		return apply_filters( 'ap_title', $title );
	}

	/**
	 * Add default before body sidebar in AnsPress contents
	 */
	public static function ap_before_html_body() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$data         = wp_json_encode(
				array(
					'user_login'   => $current_user->data->user_login,
					'display_name' => $current_user->data->display_name,
					'user_email'   => $current_user->data->user_email,
					'avatar'       => get_avatar( $current_user->ID ),
				)
			);
			?>
				<script type="text/javascript">
					apCurrentUser = <?php echo $data; // xss okay. ?>;
				</script>
			<?php
		}
		dynamic_sidebar( 'ap-before' );
	}

	/**
	 * Add feed and links in HEAD of the document
	 *
	 * @since 4.1.0 Removed question sortlink override.
	 */
	public static function wp_head() {
		if ( ap_current_page( 'base' ) ) {
			$q_feed = get_post_type_archive_feed_link( 'question' );
			$a_feed = get_post_type_archive_feed_link( 'answer' );
			echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr__( 'Question Feed', 'anspress-question-answer' ) . '" href="' . esc_url( $q_feed ) . '" />';
			echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr__( 'Answers Feed', 'anspress-question-answer' ) . '" href="' . esc_url( $a_feed ) . '" />';
		}
	}

	/**
	 * Ajax callback for post actions dropdown.
	 *
	 * @since 3.0.0
	 */
	public static function post_actions() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'r' );

		if ( ! check_ajax_referer( 'post-actions-' . $post_id, 'nonce', false ) || ! is_user_logged_in() ) {
			ap_ajax_json( 'something_wrong' );
		}

		ap_ajax_json(
			[
				'success' => true,
				'actions' => ap_post_actions( $post_id ),
			]
		);
	}

	/**
	 * Shows lists of attachments of a question
	 */
	public static function question_attachments() {
		if ( ap_have_attach() ) {
			include ap_get_theme_location( 'attachments.php' );
		}
	}

	/**
	 * Check if anspress.php file exists in theme. If exists
	 * then load this template for AnsPress.
	 *
	 * @param  string $template Template.
	 * @return string
	 * @since  3.0.0
	 * @since  4.1.0 Give priority to page templates and then anspress.php and lastly fallback to page.php.
	 * @since  4.1.1 Load single question template if exists.
	 * @todo Check if this works with new template compatibility @critical.
	 */
	public static function anspress_basepage_template( $template ) {
		if ( is_anspress() ) {
			$templates = [ 'anspress.php', 'page.php', 'singular.php', 'index.php' ];

			if ( is_page() ) {
				$_post = get_queried_object();

				array_unshift( $templates, 'page-' . $_post->ID . '.php' );
				array_unshift( $templates, 'page-' . $_post->post_name . '.php' );

				$page_template = get_post_meta( $_post->ID, '_wp_page_template', true );

				if ( ! empty( $page_template ) && 'default' !== $page_template ) {
					array_unshift( $templates, $page_template );
				}
			} elseif ( is_single() ) {
				$_post = get_queried_object();

				array_unshift( $templates, 'single-' . $_post->ID . '.php' );
				array_unshift( $templates, 'single-' . $_post->post_name . '.php' );
				array_unshift( $templates, 'single-' . $_post->post_type . '.php' );
			} elseif ( is_tax() ) {
				$_term     = get_queried_object();
				$term_type = str_replace( 'question_', '', $_term->taxonomy );
				array_unshift( $templates, 'anspress-' . $term_type . '.php' );
			}

			$new_template = locate_template( $templates );

			if ( '' !== $new_template ) {
				return $new_template;
			}
		}

		return $template;
	}

	/**
	 * Generate question excerpt if there is not any already.
	 *
	 * @param string      $excerpt Default excerpt.
	 * @param object|null $post    WP_Post object.
	 * @return string
	 * @since 4.1.0
	 */
	public static function get_the_excerpt( $excerpt, $post = null ) {
		$post = get_post( $post );

		if ( 'question' === $post->post_type ) {
			if ( get_query_var( 'answer_id' ) ) {
				$post = ap_get_post( get_query_var( 'answer_id' ) );
			}

			// Check if excerpt exists.
			if ( ! empty( $post->post_excerpt ) ) {
				return $post->post_excerpt;
			}

			$excerpt_length = apply_filters( 'excerpt_length', 55 );
			$excerpt_more   = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
			return wp_trim_words( $post->post_content, $excerpt_length, $excerpt_more );
		}

		return $excerpt;
	}

	/**
	 * Remove hentry class from question, answers and main pages .
	 *
	 * @param array   $post_classes Post classes.
	 * @param array   $class        An array of additional classes added to the post.
	 * @param integer $post_id      Post ID.
	 * @return array
	 * @since 4.1.0
	 */
	public static function remove_hentry_class( $post_classes, $class, $post_id ) {
		$_post = ap_get_post( $post_id );

		if ( $_post && ( in_array( $_post->post_type, [ 'answer', 'question' ], true ) || in_array( $_post->ID, ap_main_pages_id() ) ) ) {
			return array_diff( $post_classes, [ 'hentry' ] );
		}

		return $post_classes;
	}

	/**
	 * Callback for showing content below question and answer.
	 *
	 * @return void
	 * @since 4.1.2
	 * @since 4.2.0 Show only if user has permission to read.
	 */
	public static function after_question_content() {
		if ( ! ap_user_can_read_post() ) {
			return;
		}

		echo ap_post_status_badge(); // xss safe.

		$_post    = ap_get_post();
		$activity = ap_recent_activity( null, false );

		if ( ! empty( $activity ) ) {
			echo '<div class="ap-post-updated"><i class="apicon-clock"></i>' . $activity . '</div>';
		}
	}

	/**
	 * Show actions in answer footer.
	 *
	 * @since 4.2.0
	 */
	public static function question_footer() {
		if ( ap_user_can_read_question() ) {
			Template\select_button();
		}

		// Comment button id.
		echo ap_comment_btn_html( get_question_id() );

		if ( ap_user_can_read_question() ) {
			Template\actions_button();
		}
	}

	/**
	 * Show actions in answer footer.
	 *
	 * @since 4.2.0
	 */
	public static function answer_footer() {
		if ( ap_user_can_read_answer() ) {
			Template\select_button();
		}

		// Comment button id.
		echo ap_comment_btn_html( ap_get_answer_id() );

		if ( ap_user_can_read_answer() ) {
			Template\actions_button();
		}
	}
}
