<?php
/**
 * Class used for ajax callback `ap_toggle_best_answer`.
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
 * The `ap_toggle_best_answer` ajax callback.
 *
 * @since 4.1.8
 */
class Upload_Avatar extends \AnsPress\Abstracts\Ajax {
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
		$this->nonce_key = 'upload_avatar_' . get_current_user_id();

		// Call parent.
		parent::__construct();
	}

	/**
	 * Verify user permission.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		if ( empty( $_FILES ) || empty( $_FILES['avatar'] ) || ! ap_user_can_upload() ) {
			parent::verify_permission();
		}
	}

	/**
	 * Delete previously uploaded avatar of user.
	 *
	 * @return void
	 * @since 4.2.0
	 */
	public function delete_previous_avatar() {
		$attach_id = (int) get_user_meta( get_current_user_id(), 'ap_avatar_attach_id', true );

		if ( ! empty( $attach_id ) ) {
			wp_delete_attachment( $attach_id, true );
			update_user_meta( get_current_user_id(), 'ap_avatar_attach_id', '' );
		}
	}

	/**
	 * Handle ajax for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {
		$upload_dir = wp_upload_dir();
		$tmp        = $_FILES['avatar']['tmp_name'];

		// Add filter to resize avatar size.
		add_filter( 'wp_handle_upload', [ $this, 'wp_handle_upload' ] );

		$attach_id = ap_upload_user_file( $_FILES['avatar'], false, array(
			'jpg|jpeg' => 'image/jpeg',
			'gif'      => 'image/gif',
			'png'      => 'image/png',
		) );

		// Remove filter.
		remove_filter( 'wp_handle_upload', [ $this, 'wp_handle_upload' ] );

		if ( is_wp_error( $attach_id ) ) {
			$this->set_fail();
			$this->snackbar( $attach_id->get_error_messages() );
			$this->send();
		}

		// Delete existing avatar.
		$this->delete_previous_avatar();

		$img_src = wp_get_attachment_image_src( $attach_id, 'full' );

		// Remove domain name from url before saving as meta field.
		$img_base = str_replace( $upload_dir['baseurl'] . '/', '', $img_src[0] );

		update_user_meta( get_current_user_id(), 'ap_avatar', $img_base );
		update_user_meta( get_current_user_id(), 'ap_avatar_attach_id', $attach_id );

		$this->set_success();
		$this->add_res( 'avatar', $img_src );
		$this->add_res( 'action', 'avatar_uploaded' );
		$this->snackbar( __( 'Successfully uploaded avatar.', 'anspress-question-answer' ) );
	}

	/**
	 * Modify the size of uploaded avatar to 180x180.
	 *
	 * @param array $data Image data.
	 * @return array
	 */
	public function wp_handle_upload( $data ) {
		if ( true !== anspress()->wp_handle_upload ) {
			return $data;
		}

		if ( ! isset( $data['file'] ) || ! isset( $data['type'] ) ) {
			return $data;
		}

		if ( in_array( $data['type'], [ 'image/jpg', 'image/jpeg', 'image/png', 'image/gif' ] ) ) {
			// Check for a valid image editor.
			$editor = wp_get_image_editor( $data['file'] );

			if ( ! is_wp_error( $editor ) ) {
				// Set the new image quality
				$result = $editor->resize( 180, 180, true );

				// Re-save the original image file
				if ( ! is_wp_error( $result ) ) {
					$editor->save( $data['file'] );
				}
			}
		}

		return $data;
	}
}
