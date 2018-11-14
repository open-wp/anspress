<?php
/**
 * Profile overview activities template.
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

$args  = [
	'number'        => 10,
	'user_id'       => ap_get_displayed_user_id(),
	'exclude_roles' => [],
];

$activities = new AnsPress\Activity( $args );

?>
<div class="ap-overview-activities ap-overview-block">
	<h2 class="ap__heading"><?php esc_attr_e( 'Activities', 'anspress-question-answer' ); ?></h2>

	<div class="ap-overview-block-in">
		<?php if ( $activities->have() ) : ?>

			<?php
			// Loop for getting activities.
			while ( $activities->have() ) :
				$activities->the_object();
				// Shows date and time for timeline.
				$activities->the_when( 'ap__when' );

				?>

				<div class="ap__item ap__action-<?php $activities->the_action_type(); ?>">

					<div class="ap__icon">
						<i class="<?php $activities->the_icon(); ?>"></i>
					</div>

					<div class="ap__left">
						<div class="ap__body">
							<div class="ap__head">
								<span class="ap__verb"><?php $activities->the_verb(); ?></span> <time class="ap__date"><?php echo ap_human_time( $activities->get_the_date(), false ); ?></time>
							</div>

							<?php $activities->the_ref_content(); ?>
						</div>
					</div>

				</div>

			<?php endwhile; ?>

			<?php
			// Wether to show load more button or not.
			if ( $activities->have_pages() ) :
			?>
				<div class="ap-activity-more ap-activity-item mt-20">
					<div>
						<?php $activities->more_button(); ?>
					</div>
				</div>
			<?php endif; ?>

		<?php else : ?>

			<div class="ap-display-flex align-item-center">
				<i class="ap-feedback-icon apicon-pulse ap-text-muted"></i>
				<div>
					<strong class="ap-feedback-title"><?php esc_attr_e( 'No activities yet!', 'anspress-question-answer' ); ?></strong>
				</div>
			</div>

		<?php endif; ?>
	</div>

</div>
