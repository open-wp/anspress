<?php
/**
 * User profile template.
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @link       https://anspress.io
 * @since      4.2.0
 * @package    AnsPress
 * @subpackage Templates
 */

use AnsPress\Addons\Profile;

// Prevent direct access to file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id     = ap_get_displayed_user_id();
$current_tab = ap_sanitize_unslash( 'tab', 'r', 'questions' );
?>

<div id="ap-profile" class="ap-profile">

	<div class="ap-profile-info">
		<div class="ap-display-flex">
			<div class="ap-profile-avatar">
				<?php echo get_avatar( $user_id, 120 ); ?>
			</div>
			<div class="ap-profile-vcard">

				<div class="ap-display-flex">
					<div>
						<div class="ap-profile-vcard-name">
							<?php echo ap_user_display_name( $user_id ); ?>
						</div>

						<div class="ap-profile-vcard-metas">
							<?php
							/**
							 * Action triggered in profile vcard meta.
							 *
							 * @since 4.2.0
							 */
							do_action( 'ap_profile_vcard_meta_before' );
							?>
							<span><b>&bull;</b> <a href="#">rahularyan.com</a></span>
							<span><b>&bull;</b> Member since Jan 2017</span>
							<?php
							/**
							 * Action triggered in profile vcard meta.
							 *
							 * @since 4.2.0
							 */
							do_action( 'ap_profile_vcard_meta' );
							?>
						</div>

						<?php
						/**
						 * Action triggered in profile vcard.
						 *
						 * @since 4.2.0
						 */
						do_action( 'ap_profile_vcard' );
						?>

					</div>
					<div class="ap__ask-btn">
						<a href="#" class="ap-btn"><?php esc_attr_e( 'Ask a question', 'anspress-question-answer' ); ?></a>
					</div>
				</div>

				<div class="ap-profile-counts ap-display-flex justify-space-betw">

					<div class="ap__questions">
						<span class="ap__dt">Questions</span>
						<span class="ap__dd">Posted 12, 10 Solved</span>
					</div>

					<div class="ap__answers">
						<span class="ap__dt">Answers</span>
						<span class="ap__dd">Posted 22, 5 best</span>
					</div>

					<div class="ap__comments">
						<span class="ap__dt">Comments</span>
						<span class="ap__dd">Posted 22, 5 best</span>
					</div>

					<div class="ap__comments">
						<span class="ap__dt">Votes Received</span>
						<span class="ap__dd">122 Up, 10 down</span>
					</div>
				</div>

			</div>
		</div>

		<nav class="ap-profile-nav" aria-labelledby="<?php esc_attr_e( 'User navigation', 'anspress-question-answer' ); ?>">
			<?php ap_get_template_part( 'profile/nav' ); ?>
		</nav>
	</div>

	<div class="ap-flex-row<?php echo is_active_sidebar( 'anspress-profile' ) ? ' ap-sidebar-active' : ''; ?>">

		<div class="ap-col-main">
			<?php Profile\profile_page_content(); ?>
		</div>

		<?php if ( is_active_sidebar( 'anspress-profile' ) ) : ?>
			<div class="ap-col-sidebar ap-sidebar-profile">
				<?php dynamic_sidebar( 'anspress-profile' ); ?>
			</div>
		<?php endif; ?>
	</div>

</div>
