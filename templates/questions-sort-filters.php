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

<form class="ap-questions-sorting-form mb-30 clearfix" ap="submitOnChange" apDisableEmptyFields name="questionFilters" method="GET">

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
			<input name="ap_search" type="text" class="ap-questions-searchi ap-search-input ap-form-input" placeholder="<?php esc_attr_e( 'Search questions...', 'anspress-question-answer' ); ?>" value="<?php echo ap_get_search_terms(); ?>" />
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
		$qfilter         = get_questions_filter();
		$current_qfilter = get_current_questions_filter();
		$active_filters  = get_current_questions_filters();
	?>

	<div class="ap-questions-sorting-col2 ap-display-flex mt-10">
		<a href="#" class="ap-link-show-filters ap-text-md" data-toggleclassof="#ap-questions-filters" data-classtotoggle="ap-display-none"><?php esc_attr_e( 'Toggle filters', 'anspress-question-answer' ); ?></a>

		<div class="ap-questions-sorting-active">
			<?php if ( ! empty( $active_filters ) ) : ?>

				<?php foreach ( $active_filters as $f ) : ?>
					<a href="#" class="ap-active-filter ap-text-md" ap="removeQFilter" data-name="<?php echo esc_attr( $f['name'] ); ?>"><i class="apicon-x"></i><?php echo esc_attr( $f['label'] ); ?></a>
				<?php endforeach; ?>

			<?php endif; ?>

			<?php
				/**
				 * Action triggered in questions list sorting and filter.
				 *
				 * @since 4.2.0
				 */
				do_action( 'ap_sorting_filters_active' );
			?>
		</div>
	</div>

	<div id="ap-questions-filters" class="<?php echo empty( $active_filters ) ? 'ap-display-none' : ''; ?>">
		<div class="ap-questions-sorting-col3 ap-display-flex mt-10">
			<select id="questions-filter" name="qfilter">
			<?php foreach ( $qfilter as $k => $nav ) : ?>

				<option  value="<?php echo esc_attr( $k ); ?>" <?php selected( $current_qfilter, $k ); ?>>
					<?php echo esc_attr( $nav['label'] ); ?>

					<?php if ( ! empty( $nav['count'] ) ) : ?>
						(<?php echo esc_attr( $nav['count'] ); ?></span>)
					<?php endif; ?>
				</option>

			<?php endforeach; ?>
			</select>

			<?php
				/**
				 * Action after 3rd column of questions sort-filter.
				 *
				 * @since 4.2.0
				 */
				do_action( 'ap_questions_sort_filters_col3' );
			?>
		</div>
	</div>
</form>
