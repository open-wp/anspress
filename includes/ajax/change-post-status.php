<?php
/**
 * Class used for ajax callback `ap_change_post_status`.
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
 * The `ap_change_post_status` ajax callback.
 *
 * @since 4.2.0
 * @todo Change post status to private not working.
 */
class Change_Post_Status extends \AnsPress\Classes\Ajax {
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
		$this->req( 'status', ap_sanitize_unslash( 'status', 'r' ) );

		$this->nonce_key = 'change-status-' . $this->req( 'status' ) . '-' . $this->req( 'post_id' );

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

		if ( empty( $post_id ) || ! ap_user_can_change_status( $post_id ) || ! in_array( $status, [ 'publish', 'moderate', 'trash' ], true ) ) {
			parent::verify_permission();
		}
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		$status      = $this->req( 'status' );
		$post        = ap_get_post( $this->req( 'post_id' ) );
		$update_data = array();

		$update_data['post_status'] = $status;
		$update_data['ID']          = $post->ID;

		wp_update_post( $update_data );

		// Unselect as best answer if moderate.
		if ( 'answer' === $post->post_type && 'moderate' === $status && ap_have_answer_selected( $post->post_parent ) ) {
			ap_unselect_answer( $post->ID );
		}

		do_action( 'ap_post_status_updated', $post->ID );

		$activity_type = 'moderate' === $post->post_status ? 'approved_' . $post->post_type : 'changed_status';
		ap_update_post_activity_meta( $post_id, $activity_type, get_current_user_id() );

		$this->set_success();
		$this->add_res( 'action', [ 'active' => true ] );
		$this->add_res( 'postmessage', ap_get_post_status_message( $post->ID ) );
		$this->add_res( 'newStatus', $status );

		$this->snackbar( __( 'Post status updated successfully.', 'anspress-question-answer' ) );

		$this->send();
	}
}
