<?php
/**
 * Class used for ajax callback `ap_load_more_activities_profile`.
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
 * The `comment_modal` ajax callback.
 *
 * @since 4.1.8
 */
class More_Activities_Profile extends \AnsPress\Abstracts\Ajax {
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
		$comment_id = ap_sanitize_unslash( 'paged', 'r' );

		$this->req( 'paged', (int) ap_sanitize_unslash( 'paged', 'r' ) );
		$this->req( 'user_id', (int) ap_sanitize_unslash( 'user_id', 'r' ) );
		$this->nonce_key = 'load_more_activities_' . $this->req( 'user_id' );


		// Call parent.
		parent::__construct();
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		//parent::verify_permission();
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		ob_start();

		$args = [
			'number'        => 20,
			'user_id'       => ap_get_displayed_user_id(),
			'exclude_roles' => [],
			'paged'         => $this->req( 'paged' ),
		];

		$activities = new \AnsPress\Activity( $args );
		ap_get_template_part( 'profile/overview-activities', [ 'activities' => $activities ] );

		$html = ob_get_clean();

		$this->set_success();

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
