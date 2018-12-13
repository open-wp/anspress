<?php
/**
 * Class used for ajax callback `ap_comment_order`.
 * This class is auto loaded by AnsPress loader on demand.
 *
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 * @subpackage Ajax
 * @since 4.2.0
 */

namespace AnsPress\Ajax;

// Die if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The `ap_comment_order` ajax callback.
 *
 * @since 4.2.0
 * @todo Change post status to private not working.
 */
class Comment_Order extends \AnsPress\Abstracts\Ajax {
	/**
	 * Instance of this class.
	 */
	static $instance;

	/**
	 * The class constructor.
	 *
	 * Set requests and nonce key.
	 */
	protected function __construct() {
		$this->req( 'post_id', (int) ap_sanitize_unslash( 'post_id', 'r' ) );
		$this->req( 'order', ap_sanitize_unslash( 'order', 'r' ) );

		$this->nonce_key = '';

		// Call parent.
		parent::__construct();
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		$post_id = $this->req( 'post_id' );
		$status = $this->req( 'status' );

		if ( ! ap_user_can_read_post( $post_id ) ) {
			parent::verify_permission();
		}
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		$question    = ap_get_question( $this->req( 'post_id' ) );

		ob_start();
		ap_get_template_part( 'comments/comments', [ 'question' => $question ] );
		$html = ob_get_clean();

		$this->set_success();
		$this->add_res( 'html', $html );

		$this->send();
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
