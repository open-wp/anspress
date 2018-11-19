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

// Counts.
$q_count        = ap_total_posts_count( 'question', false, $user_id );
$solved_q_count = ap_total_posts_count( 'question', 'solved', $user_id );
$a_count        = ap_total_posts_count( 'answer', false, $user_id );
$best_a_count   = ap_total_posts_count( 'answer', 'best_answer', $user_id );
$comments_count = ap_get_user_comments_count( $user_id );
$votes          = ap_count_votes( [ 'vote_user_id' => $user_id, 'vote_type' => 'vote', 'group' => 'vote_value' ] );
$up_votes       = 0;
$down_votes     = 0;

if ( ! empty( $votes ) ) {
	foreach ( $votes as $group ) {
		if ( '1' === $group->vote_value ) {
			$up_votes = (int) $group->count;
		} elseif ( '-1' === $group->vote_value ) {
			$down_votes = (int) $group->count;
		}
	}
}
?>

<div id="ap-profile" class="ap-profile">

	<div class="ap-profile-info">
		<div class="ap-display-flex">

			<div class="ap-profile-avatar">
				<?php echo get_avatar( $user_id, 120 ); ?>
			</div>

			<div class="ap-profile-vcard">

				<div class="ap-display-flex justify-space-betw">
					<div>
						<a href="<?php echo esc_url( ap_user_link( $user_id ) ); ?>" class="ap-profile-vcard-name">
							<?php echo esc_html( ap_user_display_name( $user_id ) ); ?>
						</a>

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

					<a href="#" class="ap-btn ap__ask-btn"><?php esc_attr_e( 'Ask a question', 'anspress-question-answer' ); ?></a>
				</div>

				<div class="ap-profile-counts ap-display-flex justify-space-betw">
					<div class="ap__questions">
						<i class="apicon-question"></i>
						<div>
							<span class="ap__dt"><?php esc_attr_e( 'Questions', 'anspress-qustion-answer' ); ?></span>
							<span class="ap__dd"><?php
							printf(
								__( 'Posted %s, %s Solved', 'anspress-question-answer' ),
								number_format_i18n( $q_count->publish ),
								number_format_i18n( $solved_q_count->publish )
							); ?></span>
						</div>
					</div>

					<div class="ap__answers">
						<i class="apicon-answer"></i>
						<div>
							<span class="ap__dt"><?php esc_attr_e( 'Answers', 'anspress-qustion-answer' ); ?></span>
							<span class="ap__dd"><?php
							printf(
								__( 'Posted %d, %d best', 'anspress-question-answer' ),
								number_format_i18n( $a_count->publish ),
								number_format_i18n( $best_a_count->publish )
							); ?></span>
						</div>
					</div>

					<div class="ap__comments">
						<i class="apicon-comments"></i>
						<div>
							<span class="ap__dt"><?php esc_attr_e( 'Comments', 'anspress-qustion-answer' ); ?></span>
							<span class="ap__dd"><?php printf( _n( '%d Comment', '%d Comments', $comments_count, 'anspress-question-answer' ), number_format_i18n( $comments_count ) ); ?></span>
						</div>
					</div>

					<div class="ap__votes">
						<i class="apicon-thumbs-up-down"></i>
						<div>
							<span class="ap__dt"><?php esc_attr_e( 'Votes Received', 'anspress-qustion-answer' ); ?></span>
							<span class="ap__dd"><?php
							printf(
								__( '%s Up, %s down', 'anspress-question-answer' ),
								number_format_i18n( $up_votes ),
								number_format_i18n( $down_votes )
							); ?></span>
						</div>
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
