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

<form class="questions-sorting-form" name="questionsSorting" method="GET">
	<div id="questions-tab" class="ap-tab ap-tab-questions">

		<?php if ( ! empty( $tab_links ) ) : ?>
			<?php foreach ( (array) $tab_links as $k => $nav ) : ?>

				<div class="ap-tab__item ap-tab-<?php echo esc_attr( $k ); ?><?php echo get_questions_active_tab() === $k ? ' ap-tab__active' : ''; ?>">
					<label class="ap-tab__anchor">
						<?php echo esc_attr( $nav['title'] ); ?>

						<?php if ( ! empty( $nav['count'] ) ) : ?>
							<span class="ap-tab-count"><?php echo esc_attr( $nav['count'] ); ?></span>
						<?php endif; ?>
						<input type="radio" name="qtab" value="<?php echo esc_attr( $k ); ?>" <?php checked( get_questions_active_tab(), $k ); ?> />
					</label>

				</div>

			<?php endforeach; ?>
		<?php endif; ?>

		<!-- Sorting field -->
		<div class="ap-tab-item ap-tab-sort right">
			<select class="ap-form-control" name="qsorting" id="question-sorting">

				<?php foreach( get_questions_sorting() as $key => $args ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( get_current_questions_sorting(), $key ); ?>><?php echo esc_html( $args['label'] ); ?></option>
				<?php endforeach; ?>

			</select>
		</div>
		<!-- End sorting field -->

	</div>
</form>

<script type="text/javascript">
	document.querySelector('#questions-tab').onchange = function(){
		document.questionsSorting.submit();
	};
</script>