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

<form class="questions-sorting-form" name="questionFilters" method="GET">
	<input type="hidden" name="question_filters" value="1" />

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
			<select class="ap-form-control" name="order_by" id="question-order_by">

				<?php var_dump(get_question_filters( 'order_by' ));foreach( get_questions_sorting() as $key => $args ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( get_question_filters( 'order_by' ), $key ); ?>><?php echo esc_html( $args['label'] ); ?></option>
				<?php endforeach; ?>

			</select>
		</div>
		<!-- End sorting field -->

	</div>

	<div class="ap-questions__search mb-20">
		<div class="ap-display-flex align-item-center">
			<input name="question_s" type="text" class="ap-questions__searchi ap-search-input ap-form-input mr-10" placeholder="<?php esc_attr_e( 'Search questions...', 'anspress-question-answer' ); ?>" value="<?php echo ap_sanitize_unslash( 'question_s', 'r' ); ?>" />
			<button class="ap-btn ap-search-btn ap-questions__searchb" type="submit"><?php esc_attr_e( 'Search', 'anspress-question-answer' ); ?></button>
		</div>
	</div>
</form>

<script type="text/javascript">
	document.questionFilters.onchange = function(){
		document.questionFilters.submit();
	};
</script>