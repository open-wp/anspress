<?php
/**
 * Class used for ajax callback `comment_delete`.
 * This class is auto loaded by AnsPress loader on demand.
 *
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 * @subpackage Ajax
 * @since 4.1.8
 */

namespace AnsPress\Ajax;

// Die if called directly.
defined( 'ABSPATH' ) || exit;

/**
 * The `comment_delete` ajax callback.
 *
 * @since 4.1.8
 */
class Comment_Delete extends \AnsPress\Abstracts\Ajax {
	/**
	 * Instance of this class.
	 */
	static $instance;

	/**
	 * Current comment object.
	 *
	 * @var object Comment object.
	 */
	private $comment;

	/**
	 * The class constructor.
	 *
	 * Set requests and nonce key.
	 */
	protected function __construct() {
		$this->req( 'comment_id', (int) ap_sanitize_unslash( 'comment_id', 'r' ) );
		$this->req( 'post_id', (int) ap_sanitize_unslash( 'post_id', 'r' ) );

		$comment_id    = $this->req( 'comment_id' );
		$comment       = get_comment( $comment_id );
		$this->comment = $comment;

		$this->nonce_key = 'delete_comment_' . $this->req( 'comment_id' );

		// Call parent.
		parent::__construct();
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		if ( ! $this->comment || $this->comment->comment_post_ID != $this->req( 'post_id' ) || ! ap_user_can_delete_comment( $this->comment->Comment_ID ) ) {
			parent::verify_permission();
		}
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		// Check if deleting comment is locked.
		if ( ap_comment_delete_locked( $this->comment->comment_ID ) && ! is_super_admin() ) {
			$this->set_fail();
			$this->snackbar( __( 'Comment is locked, cannot be deleted.', 'anspress-question-answer' ) );

			$this->send();
		}

		$delete = wp_delete_comment( (integer) $this->comment->comment_ID, true );

		$question = ap_get_question( $this->req( 'post_id' ) );
		if ( $delete ) {
			do_action( 'ap_unpublish_comment', $this->comment );
			do_action( 'ap_after_deleting_comment', $this->comment );

			$count = get_comment_count( $this->comment->comment_post_ID );

			$this->set_success();
			$this->snackbar( __( 'Comment deleted successfully', 'anspress-question-answer' ) );
			$this->add_res( 'post_id', $this->comment->comment_post_ID );
			$this->add_res( 'comment_id', $this->comment->comment_ID );

			ob_start();
			ap_get_template_part( 'comments/comments', [ 'question' => $question ] );
			$html = ob_get_clean();
			$this->add_res( 'html', $html );
		}
	}
}
