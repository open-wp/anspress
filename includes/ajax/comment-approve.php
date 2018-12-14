<?php
/**
 * Class used for ajax callback `ap_comment_approve`.
 * This class is auto loaded by AnsPress loader on demand.
 *
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 * @subpackage Ajax
 * @since 4.2.0
 */

namespace AnsPress\Ajax;

// Die if called directly.
defined( 'ABSPATH' ) || exit;

/**
 * The `ap_comment_approve` ajax callback.
 *
 * @since 4.2.0
 */
class Comment_Approve extends \AnsPress\Abstracts\Ajax {
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
	 * Current comment id.
	 *
	 * @var integer Comment id.
	 */
	private $comment_id;

	/**
	 * Current post id.
	 *
	 * @var integer Post id.
	 */
	private $post_id;

	/**
	 * The class constructor.
	 *
	 * Set requests and nonce key.
	 */
	protected function __construct() {
		$this->comment_id = (int) ap_sanitize_unslash( 'comment_id', 'r' );
		$this->post_id    = (int) ap_sanitize_unslash( 'post_id', 'r' );
		$this->comment    = get_comment( $this->comment_id );

		$this->nonce_key = 'approve_comment_' . $this->comment_id;

		// Call parent.
		parent::__construct();
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		$post = get_post( $this->post_id );

		if ( ! $this->comment || ! ap_is_cpt( $post ) || ! ap_user_can_approve_comment() ) {
			parent::verify_permission();
		}
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		$success  = wp_set_comment_status( $this->comment_id, 'approve' );

		$this->set_success();
		$this->snackbar( __( 'Comment approved successfully', 'anspress-question-answer' ) );
		$this->add_res( 'post_id', $this->post_id );
		$this->add_res( 'comment_id', $this->comment_id );

		$question = ap_get_question( $this->post_id );

		// Make sure to show unapproved tab if there are more unapproved comments.
		if ( $question->get_unapproved_comment_count() > 0 ) {
			$_REQUEST['comments_order'] = 'unapproved';
			$_GET    ['comments_order'] = 'unapproved';
		}

		ob_start();
		ap_get_template_part( 'comments/comments', [ 'question' => $question ] );
		$html = ob_get_clean();
		$this->add_res( 'html', $html );
	}
}
