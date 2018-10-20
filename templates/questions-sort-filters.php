<?php
/**
 * Display questions tab in question archive.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;
?>

<form class="ap-questions-sorting-form mb-30 clearfix" name="questionFilters" method="GET">

	<div class="ap-questions-sorting-col1 ap-display-flex">
		<!-- Sorting -->
		<div id="ap-sort-tab" class="ap-btn-group ap-btn-group-sort ap-text-md">
			<?php foreach( get_questions_sorting() as $key => $args ) : ?>
				<label class="ap-btn-group__item<?php echo get_current_questions_sorting() === $key ? ' ap-btn-group__active' : ''; ?>">
					<?php echo esc_html( $args['label'] ); ?>
					<input type="radio" name="qsort" value="<?php echo esc_attr( $key ); ?>" <?php checked( get_current_questions_sorting(), $key ); ?> />
				</label>
			<?php endforeach; ?>
		</div>
		<!-- End sorting -->

		<div class="ap-questions-search">
			<input name="question_s" type="text" class="ap-questions-searchi ap-search-input ap-form-input" placeholder="<?php esc_attr_e( 'Search questions...', 'anspress-question-answer' ); ?>" value="<?php echo ap_sanitize_unslash( 'question_s', 'r' ); ?>" />
			<button class="ap-questions-searchb" type="submit"><i class="apicon-search"></i></button>
		</div>

		<?php
			/**
			 * Action after first column of questions sort-filter.
			 *
			 * @since 4.2.0
			 */
			do_action( 'ap_questions_sort_filters_col1' );
		?>


	</div>

	<?php
		$current_filter = get_current_questions_filter();
		$filters        = get_questions_filter();
	?>

	<div class="ap-questions-sorting-col2 ap-display-flex mt-10">
		<a href="#" class="ap-link-show-filters ap-text-md" data-toggleclassof="#ap-questions-filters" data-classtotoggle="ap-display-none"><?php esc_attr_e( 'Show filters', 'anspress-question-answer' ); ?></a>

		<div class="ap-questions-sorting-active">
			<?php if ( 'all' !== $current_filter ) : ?>
				<a href="#" class="ap-active-filter ap-text-md" data-removefilter="qfilter"><i class="apicon-x"></i><?php echo esc_attr( $filters[ $current_filter ]['label'] ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div id="ap-questions-filters" class="ap-display-none">
		<div class="ap-questions-sorting-col3 ap-display-flex mt-10">
			<select id="questions-filter" name="qfilter">
			<?php foreach ( $filters as $k => $nav ) : ?>

				<option  value="<?php echo esc_attr( $k ); ?>" <?php selected( $current_filter, $k ); ?>>
					<?php echo esc_attr( $nav['label'] ); ?>

					<?php if ( ! empty( $nav['count'] ) ) : ?>
						(<?php echo esc_attr( $nav['count'] ); ?></span>)
					<?php endif; ?>
				</option>

			<?php endforeach; ?>
			</select>
		</div>
	</div>
</form>
