<?php
/**
 * Process frontend post actions.
 *
 * @link       https://anspress.io/anspress
 * @since      4.1.5
 * @author     Rahul Aryan <support@anspress.io>
 * @package    AnsPress
 * @subpackage Classes
 */

namespace AnsPress;
defined( 'ABSPATH' ) || exit;

class Post_Actions {
	/**
	 * Instance.
	 *
	 * @var Instance
	 */
	protected static $instance = null;

	protected $action = '';

	/**
	 * Get current instance.
	 *
	 * @return AnsPress\Post_Actions
	 */
	public static function get_instance() {
		// Create an object.
		if ( null === self::$instance ) {
			self::$instance = new self();

			anspress()->add_action( 'admin_post_anspress_post_action', self::$instance, 'admin_post' );
		}
		return self::$instance; // Return the object.
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {

	}

	public function admin_post() {
		$action = (string) ap_sanitize_unslash( 'ap_action', 'r' );
		$action = preg_replace( '/[^a-zA-Z0-9_]/', '', (string) $action );
		$this->action = 'post_action_' . $action;

		if ( ! empty( $action ) && method_exists( $this, $this->action ) ) {
			$method = $this->action;
			return $this->$method();
		}

		$for_admin = '';

		// Show action name if admin.
		if ( current_user_can( 'manage_options' ) ) {
			$for_admin = sprintf(
				// Translators: Placeholder contain name of method.
				__( 'No method named <code>%s</code> found in class AnsPress\Post_Actions.', 'anspress-question-answer' ),
				$this->action
			);
		}

		wp_die( __( 'Not a valid action!', 'anspress-question-answer' ) . ' ' . $for_admin );
	}

	/**
	 * General die message.
	 *
	 * @return void
	 */
	private function wrong( $msg = '' ) {
		if ( empty( $msg ) ) {
			$msg = __( 'Something is wrong! &#128528;', 'anspress-question-answer' );
		}

		wp_die( esc_html( $msg ) );
	}

	private function send( $url, $message, $type = 1 ) {
		// Store message in session.
		anspress()->session->set( 'admin_post_action', array(
			'msg'  => $message,
			'type' => $type,
			'type' => (string) ap_sanitize_unslash( 'ap_action', 'r' ),
		) );

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Process close question.
	 *
	 * @return void
	 * @todo Add hook for close toggle.
	 */
	private function post_action_close_question() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'p' );
		// Die if nonce not matched.
		ap_verify_nonce( 'close-question-' . $post_id ) || $this->wrong();

		$permalink = get_permalink( $post_id );
		// Check permission and nonce.
		if ( ! is_user_logged_in() || ! ap_user_can_close_question( $post_id ) ) {
			$this->send( $permalink, __( 'You don\'t have enough permission. &#128273;', 'anspress-question-answer' ), 3 );
		}

		$question = ap_get_question( $post_id );
		$toggle   = $question->toggle_closed();

		// Log in activity table.
		if ( $toggle ) {
			ap_activity_add( array(
				'q_id'   => $question->get_id(),
				'action' => 'closed_q',
			) );
			$this->send( $permalink, __( 'Question is now closed for new answers and comments.', 'anspress-question-answer' ) );
		}

		// Redirect to question.
		$this->send( $permalink, __( 'Question is now open for new answers and comments.', 'anspress-question-answer' ) );
	}

	/**
	 * Process to toggle featured question.
	 *
	 * @return void
	 * @todo Add hook for featured toggle.
	 */
	private function post_action_toggle_featured() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'p' );
		// Die if nonce not matched.
		ap_verify_nonce( 'toggle-featured-' . $post_id ) || $this->wrong();

		$permalink = get_permalink( $post_id );
		// Check permission and nonce.
		if ( ! is_user_logged_in() || ! ap_user_can_toggle_featured() ) {
			$this->send( $permalink, __( 'You don\'t have enough permission. &#128273;', 'anspress-question-answer' ), 3 );
		}

		$question = ap_get_question( $post_id );
		$toggle   = $question->toggle_featured();

		// Log in activity table.
		if ( $toggle ) {
			ap_activity_add( array(
				'q_id'   => $question->get_id(),
				'action' => 'featured',
			) );

			$this->send( $permalink, __( 'Question is set as featured.', 'anspress-question-answer' ) );
		}
		$this->send( $permalink, __( 'Question is unset as featured.', 'anspress-question-answer' ) );
	}

	/**
	 * Process to toggle featured question.
	 *
	 * @return void
	 * @todo Add hook for featured toggle.
	 */
	private function post_action_toggle_private() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'p' );
		// Die if nonce not matched.
		ap_verify_nonce( 'toggle-private-' . $post_id ) || $this->wrong();

		$permalink = get_permalink( $post_id );

		// Check permission and nonce.
		if ( ! is_user_logged_in() || ! ap_user_can_change_status( $post_id ) ) {
			$this->send( $permalink, __( 'You don\'t have enough permission. &#128273;', 'anspress-question-answer' ), 3 );
		}

		$question = ap_get_question( $post_id );
		$status   = 'private' === $question->get_status() ? 'publish' : 'private';
		$toggle   = $question->update_status( $status );

		// Log in activity table.
		if ( ! $toggle ) {
			$this->send( $permalink, __( 'Failed to change visibility to private!', 'anspress-question-answer' ), 3 );
		}

		$this->send( $permalink, __( 'Successfully changed visibility of the post to private.', 'anspress-question-answer' ) );
	}

	/**
	 * Process to toggle trash status.
	 *
	 * @return void
	 */
	private function post_action_trash_post() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'p' );
		// Die if nonce not matched.
		ap_verify_nonce( 'trash-post-' . $post_id ) || $this->wrong();
		$question = ap_get_question( $post_id );
		$permalink = get_post_type_archive_link( 'question' );

		// Check permission and nonce.
		if ( ! is_user_logged_in() || ! ap_user_can_delete_post( $post_id ) ) {
			$this->send( get_permalink( $question->get_id() ), __( 'You don\'t have enough permission. &#128273;', 'anspress-question-answer' ), 2 );
		}

		if ( 'trash' === $question->get_status() ) {
			$this->send( $permalink, __( 'Post is already in trash', 'anspress-question-answer' ), 3 );
		}

		$question->delete();
		$this->send( $permalink, __( 'Successfully trashed the post.', 'anspress-question-answer' ) );
	}

	/**
	 * Process to unapprove a post.
	 *
	 * @return void
	 */
	private function post_action_toggle_unapprove() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'p' );
		// Die if nonce not matched.
		ap_verify_nonce( 'toggle_unapprove-' . $post_id ) || $this->wrong();
		$question = ap_get_question( $post_id );
		$permalink = get_permalink( $post_id );

		// Check permission and nonce.
		if ( ! is_user_logged_in() || ! ap_user_can_change_status_to_moderate() ) {
			$this->send( $permalink, __( 'You don\'t have enough permission. &#128273;', 'anspress-question-answer' ), 2 );
		}

		if ( 'moderate' === $question->get_status() ) {
			$question->update_status( 'publish' );
			$this->send( $permalink, __( 'Post approved successfully', 'anspress-question-answer' ) );
		} else {
			$question->update_status( 'moderate' );
			$this->send( $permalink, __( 'Successfully unapproved the post.', 'anspress-question-answer' ) );
		}
	}

	/**
	 * Process to delete a post.
	 *
	 * @return void
	 */
	private function post_action_delete_post() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'p' );
		// Die if nonce not matched.
		ap_verify_nonce( 'delete_post-' . $post_id ) || $this->wrong();
		$question = ap_get_question( $post_id );
		$permalink = get_permalink( $post_id );

		// Check permission and nonce.
		if ( ! is_user_logged_in() || ! ap_user_can_permanent_delete( $post_id ) ) {
			$this->send( $permalink, __( 'You don\'t have enough permission. &#128273;', 'anspress-question-answer' ), 2 );
		}

		$question->delete( true );
		$this->send( get_post_type_archive_link( 'question' ), __( 'Post was deleted permanently.', 'anspress-question-answer' ) );
	}

}
