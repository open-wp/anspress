<?php
/**
 * Display answers tab in single question page.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;

$tab_links = get_answers_tab_links();
?>
<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">

	<?php if ( ! empty( $tab_links ) ) : ?>
		<?php foreach ( (array) $tab_links as $k => $nav ) : ?>

			<li class="ap-tab-item ap-tab-<?php echo esc_attr( $k ); ?><?php echo get_answers_active_tab() === $k ? ' active' : ''; ?>">

				<a href="<?php echo esc_url( $nav['link'] . '#answers-order' ); ?>">
					<?php echo esc_attr( $nav['title'] ); ?>

					<?php if ( ! empty( $nav['count'] ) ) : ?>
						<span class="ap-tab-count"><?php echo esc_attr( $nav['count'] ); ?></span>
					<?php endif; ?>
				</a>

			</li>

		<?php endforeach; ?>
	<?php endif; ?>

</ul>
