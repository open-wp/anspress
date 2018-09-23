<?php
/**
 * Display questions tab in question archive.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;

$tab_links = get_questions_tab_links();
?>
<div id="questions-tab" class="ap-tab ap-tab-questions">

	<?php if ( ! empty( $tab_links ) ) : ?>
		<?php foreach ( (array) $tab_links as $k => $nav ) : ?>

			<div class="ap-tab-item ap-tab-<?php echo esc_attr( $k ); ?><?php echo get_questions_active_tab() === $k ? ' active' : ''; ?>">

				<a href="<?php echo esc_url( $nav['link'] ); ?>">
					<?php echo esc_attr( $nav['title'] ); ?>

					<?php if ( ! empty( $nav['count'] ) ) : ?>
						<span class="ap-tab-count"><?php echo esc_attr( $nav['count'] ); ?></span>
					<?php endif; ?>
				</a>

			</div>

		<?php endforeach; ?>
	<?php endif; ?>

	<div class="ap-tab-item ap-tab-sort">
		<a href="<?php echo esc_url( $nav['link'] ); ?>">
			<?php esc_attr_e( 'Sort', 'anspress-question-answer' ); ?>
		</a>
	</div>

</div>
