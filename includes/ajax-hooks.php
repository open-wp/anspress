<?php
/**
 * Register all ajax hooks.
 *
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-2.0+
 * @link         https://anspress.io
 * @copyright    2014 Rahul Aryan
 * @package      AnsPress
 * @subpackage   Ajax Hooks
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register all ajax callback
 */
class AnsPress_Ajax {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	public static function init() {
		anspress()->add_action( 'ap_ajax_suggest_similar_questions', __CLASS__, 'suggest_similar_questions' );
		anspress()->add_action( 'ap_ajax_load_tinymce', __CLASS__, 'load_tinymce' );
		anspress()->add_action( 'ap_ajax_vote', 'AnsPress_Vote', 'vote' );

		anspress()->add_action( 'ap_ajax_delete_comment', 'AnsPress\Ajax\Comment_Delete', 'init' );
		anspress()->add_action( 'wp_ajax_ap_comment_form', 'AnsPress\Ajax\Comment_Form', 'init' );
		anspress()->add_action( 'wp_ajax_nopriv_ap_comment_form', 'AnsPress\Ajax\Comment_Form', 'init' );
		anspress()->add_action( 'wp_ajax_ap_order_comments', 'AnsPress\Ajax\Comment_Order', 'init' );
		anspress()->add_action( 'wp_ajax_nopriv_ap_order_comments', 'AnsPress\Ajax\Comment_Order', 'init' );
		anspress()->add_action( 'wp_ajax_ap_comment_submit', 'AnsPress\Ajax\Comment_Submit', 'init' );
		anspress()->add_action( 'wp_ajax_nopriv_ap_comment_submit', 'AnsPress\Ajax\Comment_Submit', 'init' );
		anspress()->add_action( 'wp_ajax_ap_comment_approve', 'AnsPress\Ajax\Comment_Approve', 'init' );

		anspress()->add_action( 'wp_ajax_ap_toggle_best_answer', 'AnsPress\Ajax\Toggle_Best_Answer', 'init' );

		anspress()->add_action( 'wp_ajax_ap_load_more_activities_profile', 'AnsPress\Ajax\More_Activities_Profile', 'init' );
		anspress()->add_action( 'wp_ajax_nopriv_ap_load_more_activities_profile', 'AnsPress\Ajax\More_Activities_Profile', 'init' );

		anspress()->add_action( 'wp_ajax_ap_upload_user_avatar', 'AnsPress\Ajax\Upload_Avatar', 'init' );

		// Uploader hooks.
		anspress()->add_action( 'ap_ajax_delete_attachment', 'AnsPress_Uploader', 'delete_attachment' );

		// List filtering.
		anspress()->add_action( 'ap_ajax_load_filter_order_by', __CLASS__, 'load_filter_order_by' );

		// Subscribe
		anspress()->add_action( 'ap_ajax_subscribe', __CLASS__, 'subscribe_to_question' );
		anspress()->add_action( 'wp_ajax_ap_repeatable_field', 'AnsPress\Ajax\Repeatable_Field', 'init' );
		anspress()->add_action( 'wp_ajax_nopriv_ap_repeatable_field', 'AnsPress\Ajax\Repeatable_Field', 'init' );

		anspress()->add_action( 'wp_ajax_ap_form_question', 'AP_Form_Hooks', 'submit_question_form', 11, 0 );
		anspress()->add_action( 'wp_ajax_nopriv_ap_form_question', 'AP_Form_Hooks', 'submit_question_form', 11, 0 );
		anspress()->add_action( 'wp_ajax_ap_form_answer', 'AP_Form_Hooks', 'submit_answer_form', 11, 0 );
		anspress()->add_action( 'wp_ajax_nopriv_ap_form_answer', 'AP_Form_Hooks', 'submit_answer_form', 11, 0 );
		anspress()->add_action( 'wp_ajax_ap_form_comment', 'AP_Form_Hooks', 'submit_comment_form', 11, 0 );
		anspress()->add_action( 'wp_ajax_nopriv_ap_form_comment', 'AP_Form_Hooks', 'submit_comment_form', 11, 0 );
		anspress()->add_action( 'wp_ajax_ap_search_tags', __CLASS__, 'search_tags' );
		anspress()->add_action( 'wp_ajax_nopriv_ap_search_tags', __CLASS__, 'search_tags' );
		anspress()->add_action( 'wp_ajax_ap_image_upload', 'AnsPress_Uploader', 'image_upload' );
		anspress()->add_action( 'wp_ajax_ap_upload_modal', 'AnsPress_Uploader', 'upload_modal' );
		anspress()->add_action( 'wp_ajax_nopriv_ap_upload_modal', 'AnsPress_Uploader', 'upload_modal' );
	}

	/**
	 * Show similar questions while asking a question.
	 *
	 * @since 2.0.1
	 */
	public static function suggest_similar_questions() {
		// Die if question suggestion is disabled.
		if ( ap_disable_question_suggestion() ) {
			wp_die( 'false' );
		}

		$keyword = ap_sanitize_unslash( 'value', 'request' );
		if ( empty( $keyword ) || ( ! ap_verify_default_nonce() && ! current_user_can( 'manage_options' ) ) ) {
				wp_die( 'false' );
		}

		$keyword = ap_sanitize_unslash( 'value', 'request' );
		$is_admin = (bool) ap_isset_post_value( 'is_admin', false );
		$questions = get_posts( array( // @codingStandardsIgnoreLine
			'post_type' => 'question',
			'showposts' => 10,
			's'         => $keyword,
		));

		if ( $questions ) {
				$items = '<div class="ap-similar-questions-head">';
				$items .= '<p><strong>' . sprintf( _n( '%d similar question found', '%d similar questions found', count( $questions ), 'anspress-question-answer' ), count( $questions ) ) . '</strong></p>';
				$items .= '<p>' . __( 'We have found some similar questions that have been asked earlier.', 'anspress-question-answer' ) . '</p>';
				$items .= '</div>';

			$items .= '<div class="ap-similar-questions">';

			foreach ( (array) $questions as $p ) {
				$count = ap_get_answers_count( $p->ID );
				$p->post_title = ap_highlight_words( $p->post_title, $keyword );

				if ( $is_admin ) {
					$items .= '<div class="ap-q-suggestion-item clearfix"><a class="select-question-button button button-primary button-small" href="' . add_query_arg( array( 'post_type' => 'answer', 'post_parent' => $p->ID ), admin_url( 'post-new.php' ) ) . '">' . __( 'Select', 'anspress-question-answer' ) . '</a><span class="question-title">' . $p->post_title . '</span><span class="acount">' . sprintf( _n( '%d Answer', '%d Answers', $count, 'anspress-question-answer' ), $count ) . '</span></div>';
				} else {
					$items .= '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $p->ID ) . '"><span class="acount">' . sprintf( _n( '%d Answer', '%d Answers', $count, 'anspress-question-answer' ), $count ) . '</span><span class="ap-title">' . $p->post_title . '</span></a>';
				}
			}

			$items .= '</div>';
			$result = array( 'status' => true, 'html' => $items );
		} else {
			$result = array( 'status' => false, 'message' => __( 'No related questions found.', 'anspress-question-answer' ) );
		}

		ap_ajax_json( $result );
	}

	/**
	 * Send JSON response and terminate.
	 *
	 * @param array|string $result Ajax response.
	 */
	public static function send( $result ) {
		ap_send_json( ap_ajax_responce( $result ) );
	}

	/**
	 * Load tinyMCE assets using ajax.
	 *
	 * @since 3.0.0
	 */
	public static function load_tinymce() {
		ap_answer_form( ap_sanitize_unslash( 'question_id', 'r' ) );
		ap_ajax_tinymce_assets();

		wp_die();
	}

	/**
	 * Ajax callback for loading order by filter.
	 *
	 * @since 4.0.0
	 */
	public static function load_filter_order_by() {
		$filter = ap_sanitize_unslash( 'filter', 'r' );
		check_ajax_referer( 'filter_' . $filter, '__nonce' );

		ap_ajax_json( array(
			'success'  => true,
			'multiple' => false,
			'items'    => ap_get_questions_orderby(),
		));
	}

	/**
	 * Subscribe user to a question.
	 *
	 * @return void
	 * @since unknown
	 */
	public static function subscribe_to_question() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'r' );

		if ( ! is_user_logged_in() ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'You must be logged in to subscribe to a question', 'anspress-question-answer' ) ],
			) );
		}

		$_post = ap_get_post( $post_id );

		if ( 'question' === $_post->post_type && ! ap_verify_nonce( 'subscribe_' . $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, unable to subscribe', 'anspress-question-answer' ) ],
			) );
		}

		// Check if already subscribed, toggle if subscribed.
		$exists = ap_get_subscriber( false, 'question', $post_id );

		if ( $exists ) {
			ap_delete_subscriber( $post_id, get_current_user_id(), 'question' );
			ap_ajax_json( array(
				'success'  => true,
				'snackbar' => [ 'message' => __( 'Successfully unsubscribed from question', 'anspress-question-answer' ) ],
				'count'    => ap_get_post_field( 'subscribers', $post_id ),
				'label'    => __( 'Subscribe', 'anspress-question-answer' ),
			) );
		}

		// Insert subscriber.
		$insert = ap_new_subscriber( false, 'question', $post_id );

		if ( false === $insert ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, unable to subscribe', 'anspress-question-answer' ) ],
			) );
		}

		ap_ajax_json( array(
			'success'  => true,
			'snackbar' => [ 'message' => __( 'Successfully subscribed to question', 'anspress-question-answer' ) ],
			'count'    => ap_get_post_field( 'subscribers', $post_id ),
			'label'    => __( 'Unsubscribe', 'anspress-question-answer' ),
		) );
	}

	/**
	 * Ajax callback for `ap_search_tags`. This was called by tags field
	 * for fetching tags suggestions.
	 *
	 * @return void
	 * @since 4.1.5
	 */
	public static function search_tags() {
		$q = ap_sanitize_unslash( 'q', 'r' );
		$form = ap_sanitize_unslash( 'form', 'r' );
		$field_name = ap_sanitize_unslash( 'field', 'r' );

		if ( ! ap_verify_nonce( 'tags_' . $form . $field_name ) ) {
			wp_send_json( '{}' );
		}

		// Die if not valid form.
		if ( ! anspress()->form_exists( $form ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		$field = anspress()->get_form( $form )->find( $field_name );

		// Check if field exists and type is tags.
		if ( ! is_a( $field, 'AnsPress\Form\Field\Tags' ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		$taxo = $field->get( 'terms_args.taxonomy' );
		$taxo = ! empty( $taxo ) ? $taxo : 'tag';

		$terms = get_terms(array(
			'taxonomy'   => $taxo,
			'search'     => $q,
			'count'      => true,
			'number'     => 20,
			'hide_empty' => false,
			'orderby'    => 'count',
		));

		$format  = [];

		if ( $terms ) {
			foreach ( $terms as $t ) {
				$format[] = array(
					'term_id'     => $t->term_id,
					'name'        => $t->name,
					'description' => $t->description,
					'count'       => sprintf( _n( '%d Question', '%d Questions', $t->count, 'anspress-question-answer' ), $t->count ),
				);
			}
		}

		wp_send_json( $format );
	}
}
