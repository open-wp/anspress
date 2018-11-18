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
?>

<?php if ( $activities->have() ) : ?>

	<?php
	// Loop for getting activities.
	while ( $activities->have() ) :
		$activities->the_object();
		// Shows date and time for timeline.
		$activities->the_when( 'ap__when' );

		?>

		<?php if ( $activities->have_group_items() ) : ?>

			<div class="ap__item ap-display-flex justify-space-betw ap__action-<?php $activities->the_action_type(); ?>">
				<div class="ap__icon">
					<i class="apicon-pulse"></i>
				</div>
				<div>
					<div class="ap__left">
						<div class="ap__head">
							<span class="ap__verb"><?php printf( esc_attr__( 'Performed %s activity', 'anspress-question-answer' ), '<b>' . number_format_i18n( $activities->count_group() ) . '</b>' ); ?></span>
							<time class="ap__date"><?php echo ap_human_time( $activities->get_the_date(), false ); ?></time>
						</div>

						<?php $activities->the_ref_content(); ?>
					</div>

					<div class="ap__subactivities">
						<?php $activities->group_start(); ?>

						<?php
						while ( $activities->have_group() ) :
							$activities->the_object();
							?>
							<div class="ap__subactivity ap-display-flex ap__action-<?php $activities->the_action_type(); ?>">
								<div class="ap__subactivity-icon">
									<i class="<?php $activities->the_icon(); ?>"></i>
								</div>

								<div class="ap__subactivity-right">
									<div>
										<span class="ap__subactivity-verb"><?php $activities->the_verb(); ?></span> <time class="ap__subactivity-date"><?php echo ap_human_time( $activities->get_the_date(), false ); ?></time>
									</div>

									<?php $activities->the_ref_content(); ?>
								</div>

							</div>
						<?php endwhile; ?>

						<?php $activities->group_end(); ?>
					</div>
				</div>
			</div>

		<?php else : ?>

			<div class="ap__item ap-display-flex justify-space-betw ap__action-<?php $activities->the_action_type(); ?>">
				<div class="ap__icon">
					<i class="<?php $activities->the_icon(); ?>"></i>
				</div>

				<div class="ap__left">
					<div class="ap__head">
						<span class="ap__verb"><?php $activities->the_verb(); ?></span>
						<time class="ap__date"><?php echo ap_human_time( $activities->get_the_date(), false ); ?></time>
					</div>

					<?php $activities->the_ref_content(); ?>
				</div>
			</div>

		<?php endif; ?>

	<?php endwhile; ?>

	<?php
	// Wether to show load more button or not.
	if ( $activities->have_pages() ) :

		$paged = max( 1, get_query_var( 'paged' ) );

		$args = wp_json_encode( [
			'action'  => 'ap_load_more_activities_profile',
			'__nonce' => wp_create_nonce( 'load_more_activities_' . ap_get_displayed_user_id() ),
			'paged'   => $activities->paged + 1,
			'user_id' => ap_get_displayed_user_id(),
		] );
	?>
		<a href="#" class="ap-btn" ap="loadMoreActivities" apquery="<?php echo esc_js( $args ); ?>"><?php esc_attr_e( 'Load more', 'anspress-question-answer' ); ?></a>

	<?php endif; ?>

<?php else : ?>

	<div class="ap-display-flex align-item-center">
		<i class="ap-feedback-icon apicon-pulse ap-text-muted"></i>
		<div>
			<strong class="ap-feedback-title"><?php esc_attr_e( 'No activities yet!', 'anspress-question-answer' ); ?></strong>
		</div>
	</div>

<?php endif; ?>
