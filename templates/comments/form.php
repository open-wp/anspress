<?php
/**
 * Comment form template.
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @subpackage Templates
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 * @since      4.1.0
 */

defined('ABSPATH') || exit;

?>
<div class="ap-animate-slide-y ap-animate-closed">
	<!-- Content textarea -->
	<div id="ap-field-comment-content" class="ap-form-group ap-field-comment-content ap-field-type-textarea">
		<div class="ap-field-group-w">
			<textarea name="comment[content]" id="comment-content" class="ap-form-control " placeholder="<?php esc_attr_e( 'Write your comment here...', 'anspress-question-answer' ); ?>" rows="4"></textarea>
		</div>
	</div>
	<!-- /Content textarea -->

	<?php if ( ! is_user_logged_in() ) : ?>

		<!-- Author name -->
		<div id="ap-field-comment-name" class="ap-form-group ap-field-comment-name ap-field-type-input">
			<label class="ap-form-label" for="comment-name"><?php esc_attr_e( 'Your Name', 'anspress-question-answer' ); ?></label>
			<div class="ap-field-group-w">
				<input type="text" value="" name="comment[name]" id="comment-name" class="ap-form-control" placeholder="<?php esc_attr_e( 'Enter your name to display', 'anspress-question-answer' ); ?>">
			</div>
		</div>
		<!-- /Author name -->

		<div id="ap-field-comment-email" class="ap-form-group ap-field-comment-email ap-field-type-input">
			<label class="ap-form-label" for="comment-email"><?php esc_attr_e( 'Your Email', 'anspress-question-answer' ); ?></label>
			<div class="ap-field-group-w">
				<input type="email" value="" name="comment[email]" id="comment-email" class="ap-form-control" placeholder="<?php esc_attr_e( 'Enter your email to get follow up notifications', 'anspress-question-answer' ); ?>">
			</div>
		</div>
		
	<?php endif; ?>

	<input type="hidden" name="ap_form_name" value="form_comment">

	<button type="submit" class="ap-btn ap-btn-submit ap-btn-small"><?php esc_attr_e( 'Submit Comment', 'anspress-question-answer' ); ?></button>
</div>
