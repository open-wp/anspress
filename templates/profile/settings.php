<?php
/**
 * Profile settings template.
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @link       https://anspress.io
 * @since      4.2.0
 * @package    AnsPress
 * @subpackage Templates
 */

namespace AnsPress\Template;

// Prevent direct access to file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook triggered before rending settings forms. This
 * action is used to process submitted form.
 *
 * @since 4.2.0
 */
do_action( 'ap_before_profile_settings' );

$user      = get_user_by( 'id', get_current_user_id() );
$user_meta = get_user_meta( get_current_user_id() );
$user_meta = array_map( function( $a ) {
	return $a[0];
}, $user_meta );

?>
<div class="ap-profile-settings">
	<h2 class="ap-subheading"><?php esc_attr_e( 'Public profile', 'anspress-question-answer' ); ?></h2>

	<div class="ap-display-flex">
		<form class="ap-profile-edit" method="POST" id="ap_edit_profile">
			<!-- Userlogin -->
			<div class="ap-form-group">
				<label for="ap-userlogin" class="ap-form-label"><?php _e( 'Username', 'anspress-question-answer' ); ?></label>
				<div class="ap-form-field">
					<input name="ap-userlogin" id="ap-userlogin" class="ap-form-control" type="text" disabled="disabled" value="<?php echo esc_attr( $user->user_login ); ?>" />
					<div class="ap-form-desc"><?php esc_attr_e( 'User name cannot be changed.', 'anspress-question-answer' ); ?></div>
				</div>
			</div>
			<!-- /Userlogin -->

			<!-- Email -->
			<div class="ap-form-group">
				<label for="email" class="ap-form-label"><?php _e( 'Email', 'anspress-question-answer' ); ?></label>
				<div class="ap-form-field">
					<input name="email" id="email" class="ap-form-control" type="text" disabled="disabled" value="<?php echo esc_attr( $user->user_email ); ?>" />
					<div class="ap-form-desc"><?php esc_attr_e( 'Email cannot be changed.', 'anspress-question-answer' ); ?></div>
				</div>
			</div>
			<!-- /Email -->

			<!-- First name -->
			<div class="ap-form-group">
				<label for="first_name" class="ap-form-label"><?php _e( 'First Name', 'anspress-question-answer' ); ?></label>
				<div class="ap-form-field">
					<input name="public_profile[first_name]" id="first_name" class="ap-form-control" type="text" value="<?php echo esc_attr( $user->user_firstname ); ?>" />
				</div>
			</div>
			<!-- /First name -->

			<!-- Last name -->
			<div class="ap-form-group">
				<label for="last_name" class="ap-form-label"><?php _e( 'Last Name', 'anspress-question-answer' ); ?></label>
				<div class="ap-form-field">
					<input name="public_profile[last_name]" id="last_name" class="ap-form-control" type="text" value="<?php echo esc_attr( $user->user_lastname ); ?>" />
				</div>
			</div>
			<!-- /Last name -->

			<!-- Nick name -->
			<div class="ap-form-group">
				<label for="nickname" class="ap-form-label"><?php _e( 'Nickname', 'anspress-question-answer' ); ?></label>
				<div class="ap-form-field">
					<input name="public_profile[nickname]" id="nickname" class="ap-form-control" type="text" value="<?php echo esc_attr( $user->nickname ); ?>" />
				</div>
			</div>
			<!-- /Nick name -->

			<!-- Url name -->
			<div class="ap-form-group">
				<label for="user_url" class="ap-form-label"><?php _e( 'Website', 'anspress-question-answer' ); ?></label>
				<div class="ap-form-field">
					<input name="public_profile[user_url]" id="user_url" class="ap-form-control" type="text" placeholder="<?php esc_attr_e( 'https://my-domain.com', 'anspress-question-answer' ); ?>" value="<?php echo esc_url( $user->user_url ); ?>" />
				</div>
			</div>
			<!-- /Url name -->

			<!-- Description name -->
			<div class="ap-form-group">
				<label for="ap-bio" class="ap-form-label"><?php _e( 'Bio', 'anspress-question-answer' ); ?></label>
				<div class="ap-form-field">
					<textarea name="public_profile[bio]" id="ap-bio" class="ap-form-control" placeholder="<?php esc_attr_e( 'A very short description about you.', 'anspress-question-answer' ); ?>" rows="5"><?php echo esc_attr( $user_meta['description'] ); ?></textarea>
				</div>
			</div>
			<!-- /Description name -->

			<div class="ap-form-group">
				<div class="col-sm-offset-2 ap-form-field">
					<button type="submit" class="ap-btn"><?php _e( 'Update Profile', 'anspress-question-answer' ); ?></button>
				</div>
			</div>
			<input type="hidden" name="__update_nonce" value="<?php echo wp_create_nonce( 'update_profile' ); ?>" />
		</form>

		<div class="ap-profile-edit-avatar">
			<?php echo get_avatar( get_current_user_id(), 180 ); ?>
			<form method="POST" class="ap-form-avatar">
				<input type="file" name="avatar" class="ap-field-avatar" />
				<span class="ap-btn"><?php _e( 'Upload new picture', 'anspress-question-answer' ); ?></span>
			</form>
		</div>

	</div>

	<!-- Change password form -->
	<h2 class="ap-subheading mt-20"><?php esc_attr_e( 'Password', 'anspress-question-answer' ); ?></h2>

	<form id="ap-profile-password-form" method="POST">

		<div class="ap-form-group">
			<label for="ap-old-password" class="ap-form-label"><?php _e( 'Old Password', 'anspress-question-answer' ); ?></label>
			<div class="ap-form-field">
				<input name="ap-old-password" id="ap-old-password" class="ap-form-control" type="password" />
			</div>
		</div>

		<div class="ap-form-group">
			<label for="ap-password1" class="ap-form-label"><?php _e( 'New Password', 'anspress-question-answer' ); ?></label>
			<div class="ap-form-field">
				<input name="ap-password1" id="ap-password1" class="ap-form-control" type="password" />
				<div class="ap-password-strength"></div>
			</div>
		</div>

		<div class="ap-form-group">
			<label for="ap-password2" class="ap-form-label"><?php _e( 'Confirm new password', 'anspress-question-answer' ); ?></label>
			<div class="ap-form-field">
				<input name="ap-password2" id="ap-password2" class="ap-form-control" type="password" />
			</div>
		</div>

		<div class="ap-form-group">
			<div class="col-sm-offset-2 ap-form-field">
				<button type="submit" class="ap-btn"><?php _e( 'Update Password', 'anspress-question-answer' ); ?></button>
			</div>
		</div>

		<input type="hidden" name="__nonce_password" value="<?php echo wp_create_nonce( 'password_nonce' ); ?>" >
	</form>
	<!-- End change password form -->


</div>
