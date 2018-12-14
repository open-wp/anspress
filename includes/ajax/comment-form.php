<?php
/**
 * Class used for ajax callback `comment_form`.
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
 * The `comment_form` ajax callback.
 *
 * @since 4.1.8
 */
class Comment_Form extends \AnsPress\Abstracts\Ajax {
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
	 * Current comment object.
	 *
	 * @var object
	 */
	private $comment;

	/**
	 * The class constructor.
	 *
	 * Set requests and nonce key.
	 */
	protected function __construct() {
		$this->post_id    = (int) ap_sanitize_unslash( 'post_id', 'r' );
		$this->comment_id = (int) ap_sanitize_unslash( 'comment_id', 'r' );
		$this->post       = get_post( $this->post_id );

		if ( empty( $this->comment_id ) ) {
			$this->nonce_key = 'new_comment_' . $this->post_id;
		} else {
			$this->comment   = get_comment( $this->comment_id );
			$this->nonce_key = 'edit_comment_' . $this->comment_id;
		}

		// Call parent.
		parent::__construct();
	}

	/**
	 * Check if currently editing a comment.
	 *
	 * @return boolean
	 */
	private function is_editing() {
		return is_object( $this->comment ) && (int) $this->comment->comment_ID === $this->comment_id;
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		if ( ( $this->is_editing() && ! ap_user_can_edit_comment( $this->comment_id ) ) || ! $this->post || ! ap_user_can_comment( $this->post_id ) ) {
			parent::verify_permission();
		}
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		ob_start();
		ap_comment_form( $this->post_id, $this->comment );
		$html = ob_get_clean();

		$this->set_success();

		$this->add_res( 'post_id', $this->post_id );
		if ( $this->is_editing() ) {
			$this->add_res( 'comment_id', $this->comment_id );
		}
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
