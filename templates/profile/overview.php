<?php
/**
 * Profile overview template.
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @link       https://anspress.io
 * @since      4.2.0
 * @package    AnsPress
 * @subpackage Templates
 */

// Prevent direct access to file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AnsPress\Addons\Profile;

$bio = get_the_author_meta( 'description', ap_get_displayed_user_id() );
?>

<div class="ap-profile-overview ap-display-flex">
	<div class="ap-overview-right">

		<?php if ( ! empty( $bio ) ) : ?>
			<div class="ap-overview-about ap-overview-block">
				<h2 class="ap__heading"><?php esc_attr_e( 'About', 'anspress-question-answer' ); ?></h2>

				<div class="ap-overview-block-in">
					<div class="ap__bio">
						<?php echo wp_kses_post( $bio ); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="ap-overview-questions ap-overview-qa ap-overview-block">
			<h2 class="ap__heading"><?php esc_attr_e( 'Popular Questions', 'anspress-question-answer' ); ?></h2>

			<div class="ap-overview-block-in">
				<?php ap_get_template_part( 'profile/overview-popular-questions' ); ?>
			</div>
		</div>

		<div class="ap-overview-bestans ap-overview-qa ap-overview-block">
			<h2 class="ap__heading"><?php esc_attr_e( 'Best Answers', 'anspress-question-answer' ); ?></h2>

			<div class="ap-overview-block-in">
				<?php ap_get_template_part( 'profile/overview-best-answers' ); ?>
			</div>
		</div>
	</div>

	<div class="ap-overview-activities">
		<?php
			$args  = [
				'number'        => 20,
				'user_id'       => ap_get_displayed_user_id(),
				'exclude_roles' => [],
			];

			$activities = new AnsPress\Activity( $args );
		?>
		<?php ap_get_template_part( 'profile/overview-activities', [ 'activities' => $activities ] ); ?>
	</div>
	<!-- /.ap-overview-activities -->

</div>
