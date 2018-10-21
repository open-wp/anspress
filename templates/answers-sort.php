<?php
/**
 * Display answers tab in single question page.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;

$sorting = get_answers_sorting();
?>
<form class="ap-answers-sorting-form" name="answersFilters" method="GET" ap="submitOnChange">
	<div id="answers-order" class="ap-btn-group ap-btn-group-sort ap-text-md">

		<?php if ( ! empty( $sorting ) ) : ?>
			<?php foreach ( (array) $sorting as $key => $args ) : ?>
				<label class="ap-btn-group__item<?php echo get_current_answer_sorting() === $key ? ' ap-btn-group__active' : ''; ?>">
					<?php echo esc_html( $args['label'] ); ?>
					<?php if ( ! empty( $args['count'] ) ) : ?>
						<span class="ap-btn-group-count"><?php echo esc_attr( $args['count'] ); ?></span>
					<?php endif; ?>
					<input type="radio" name="asort" value="<?php echo esc_attr( $key ); ?>" <?php checked( get_current_answer_sorting(), $key ); ?> />
				</label>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</form>
