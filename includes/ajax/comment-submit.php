<?php
/**
 * Class used for ajax callback `ap_comment_submit`.
 * This class is auto loaded by AnsPress loader on demand.
 *
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 * @subpackage Ajax
 * @since 4.1.8
 */

namespace AnsPress\Ajax;

// Die if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The `ap_comment_submit` ajax callback.
 *
 * @since 4.1.8
 */
class Comment_Submit extends \AnsPress\Abstracts\Ajax {
	/**
	 * Instance of this class.
	 */
	static $instance;

	/**
	 * Post object.
	 *
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * Post id.
	 *
	 * @var integer
	 */
	private $post_id;

	/**
	 * Comment id.
	 *
	 * @var integer
	 */
	private $comment_id;

	/**
	 * Comment data.
	 *
	 * @var object|null
	 */
	private $comment_data;

	/**
	 * Current comment object.
	 *
	 * @var object
	 */

	/**
	 * The class constructor.
	 *
	 * Set requests and nonce key.
	 */
	protected function __construct() {
		$this->post_id    = (int) ap_sanitize_unslash( 'post_id', 'r' );
		$this->comment_id = (int) ap_sanitize_unslash( 'comment_id', 'r' );
		$this->post       = get_post( $this->post_id );

		if ( ! empty( $this->comment_id ) ) {
			$this->comment = get_comment( $this->comment_id );
		}

		$comment_data          = (object) ap_isset_post_value( 'comment', [] );
		$comment_data->content = sanitize_textarea_field( trim( $comment_data->content ) );

		if ( ! is_user_logged_in() ) {
			$comment_data->name  = sanitize_text_field( $comment_data->name );
			$comment_data->email = sanitize_text_field( $comment_data->email );
		}

		$this->comment_data = $comment_data;
		$this->validate_fields();

		$this->nonce_key = 'submit_comment_' . $this->post_id;

		// Call parent.
		parent::__construct();
	}

	/**
	 * Check if currently editing a comment.
	 *
	 * @return boolean
	 */
	private function is_editing() {
		return is_object( $this->comment ) && $this->comment->comment_ID == $this->comment_id;
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {

		if ( $this->is_editing() && ! ap_user_can_edit_comment( $this->comment_id ) ) {
			$this->set_fail();
			$this->snackbar( __( 'You are not allowed to edit this comment', 'anspress-question-answer' ) );
			$this->send();
		}

		// Send errors if there are any.
		if ( $this->has_form_errors() ) {
			$this->set_fail();
			$this->set_field_error( 'ap_form_comment', __( 'Unable to submit form', 'anspress-question-answer' ) );
			$this->snackbar( __( 'Cannot submit comment, check error(s) and try again', 'anspress-question-answer' ) );
			$this->send();
		}

		// Check if not restricted post type.
		if ( in_array( $this->post->post_status, [ 'draft', 'pending', 'trash' ], true ) ) {
			$type = 'question' === $this->post->post_type ? __( 'question', 'anspress-question-answer' ) : __( 'answer', 'anspress-question-answer' );

			$this->set_fail();
			$this->snackbar( sprintf(
				// Translators: %s contain post type name.
				__( 'Commenting on draft, pending or deleted %s is not allowed.', 'anspress-question-answer' ),
				$type
			) );
			$this->send();
		}

		if ( empty( $this->post_id ) || ! ap_user_can_comment( $this->post_id ) ) {
			parent::verify_permission();
		}
	}

	/**
	 * Validate form fields.
	 *
	 * @return void
	 */
	private function validate_fields() {
		$data = $this->comment_data;

		if ( $this->is_editing() && $data->content === $this->comment->comment_content ) {
			$this->set_field_error( 'comment[content]', __( 'No changes detected in comment content', 'anspress-question-answer' ) );
		}

		// Content field.
		if ( empty( $data->content ) ) {
			$this->set_field_error( 'comment[content]', __( 'Comment content is empty', 'anspress-question-answer' ) );
		}

		// Check additional fields if not logged in.
		if ( ! is_user_logged_in() ) {
			// Check name.
			if ( empty( $data->name ) ) {
				$this->set_field_error( 'comment[name]', __( 'Name field is empty', 'anspress-question-answer' ) );
			}

			// Email field.
			if ( empty( $data->email ) ) {
				$this->set_field_error( 'comment[email]', __( 'Email field is empty', 'anspress-question-answer' ) );
			}

			if ( ! is_email( $data->email ) ) {
				$this->set_field_error( 'comment[email]',  __( 'Email is invalid', 'anspress-question-answer' ) );
			}
		}

		/**
		 * Action triggered while comment form is validated.
		 *
		 * @param \AnsPress\Ajax\Comment_Submit $ajax Ajax class.
		 * @since 4.2.0
		 */
		do_action_ref_array( 'ap_comment_submit_validate', [ &$this ] );
	}

	/**
	 * Create new comment.
	 *
	 * @return void
	 */
	private function new_comment() {
		$comment_data = array(
			'user_id'         => get_current_user_id(),
			'comment_content' => $comment_data->content,
		);

		if ( ! is_user_logged_in() ) {
			$comment_data['comment_author']       = $this->comment_data->name;
			$comment_data['comment_author_email'] = $this->comment_data->email;
		}

		$comment_id = $question->add_comment( $comment_data );

		// Check if error.
		if ( is_wp_error( $comment_id ) ) {
			$this->set_fail();
			$this->snackbar( sprintf(
				// Translators: %s contain error message.
				__( 'Failed to post comment. There was an error "%s".', 'anspress-question-answer' ),
				$comment_id->get_error_message()
			) );

			$this->send();
		}

		$this->comment = get_comment( $comment_id );
	}

	/**
	 * Update existing comment.
	 *
	 * @return void
	 */
	private function update_comment() {
		$updated = wp_update_comment( array(
			'comment_ID'      => $this->comment_id,
			'comment_content' => $this->comment_data->content,
		) );

		if ( ! $updated ) {
			$this->set_fail();
			$this->snackbar( __( 'Unable to update comment.', 'anspress-question-answer' ) );
		}

		$this->comment = get_comment( $this->comment_id );
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		if ( $this->is_editing() ) {
			$this->update_comment();
		} else {
			$this->new_comment();
		}

		$question   = ap_get_question( $this->post_id );

		$this->set_success();
		$this->add_res( 'post_id', $this->post_id );
		$this->add_res( 'comment_id', $this->comment->comment_ID );

		ob_start();
		ap_get_template_part( 'comments/comments', [ 'question' => $question ] );

		// When comment is not approved.
		if ( '1' !== $this->comment->comment_approved && ! ap_user_can_approve_comment() ) {
			\AnsPress\alert(
				__( 'Comment awaiting moderation', 'anspress-question-answer' ),
				__( 'Your comment is awaiting moderation. Comment will be visible once approved.', 'anspress-question-answer' ), 'warning mt-10'
			);
		}
		$html = ob_get_clean();
		$this->add_res( 'html', $html );
	}

	/**
	 * Handle ajax for non logged in users.
	 *
	 * @return void
	 */
	public function nopriv() {
		$this->logged_in();
	}
}
