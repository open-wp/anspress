<?php
/**
 * Categories page.
 *
 * Display categories page
 *
 * @link        http://anspress.io
 * @since       4.0
 * @package     AnsPress
 * @subpackage  Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $question_categories, $ap_max_num_pages, $ap_per_page;

$paged            = max( 1, get_query_var( 'paged' ) );
$per_page         = ap_opt( 'categories_per_page' );
$total_terms      = wp_count_terms(
	'question_category', [
		'hide_empty' => false,
		'parent'     => 0,
	]
);
$offset           = $per_page * ( $paged - 1 );
$ap_max_num_pages = ceil( $total_terms / $per_page );

$order = ap_opt( 'categories_page_order' ) == 'ASC' ? 'ASC' : 'DESC';

$cat_args = array(
	'parent'     => 0,
	'number'     => $per_page,
	'offset'     => $offset,
	'hide_empty' => false,
	'orderby'    => ap_opt( 'categories_page_orderby' ),
	'order'      => $order,
);

/**
 * Filter applied before getting categories.
 *
 * @param array $cat_args `get_terms` arguments.
 * @since 1.0
 */
$cat_args = apply_filters( 'ap_categories_shortcode_args', $cat_args );

$question_categories = get_terms( 'question_category', $cat_args );
?>

<?php dynamic_sidebar( 'ap-top' ); ?>

<div id="ap-categories" class="ap-categories">

	<div class="ap-flex-row<?php echo is_active_sidebar( 'anspress-categories' ) ? ' ap-sidebar-active' : ''; ?>">

		<div class="ap-col-main">
			<div class="ap-categories-items">

				<?php foreach ( (array) $question_categories as $key => $category ) : ?>

						<div class="ap-categories-item">
							<div class="ap-cat-img-c">

								<?php ap_category_icon( $category->term_id ); ?>

								<span class="ap-term-count">
									<?php
										printf(
											_n( '%d Question', '%d Questions', $category->count, 'anspress-question-answer' ),
											(int) $category->count
										);
									?>
								</span>

								<a class="ap-categories-feat" style="height:<?php echo ap_opt( 'categories_image_height' ); ?>px" href="<?php echo get_category_link( $category ); ?>">
									<?php echo ap_get_category_image( $category->term_id, ap_opt( 'categories_image_height' ) ); ?>
								</a>
							</div>

							<div class="ap-term-title">
								<h3>
									<a class="term-title" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
								</h3>

								<?php $sub_cat_count = count( get_term_children( $category->term_id, 'question_category' ) ); ?>

								<?php if ( $sub_cat_count > 0 ) : ?>
									<span class="ap-sub-category">
										<?php
											printf(
												// Translators: %d contains count of sub category.
												_n( '%d Sub category', '%d Sub categories', (int) $sub_cat_count, 'anspress-question-answer' ),
												(int) $sub_cat_count
											);
										?>
									</span>
								<?php endif; // End if(). ?>

							</div>

							<?php if ( $category->description != '' ) : ?>
								<div class="ap-taxo-description">
									<?php echo ap_truncate_chars( $category->description, 200 ); ?>
								</div>
							<?php endif; ?>

						</div>
				<?php endforeach; // End foreach(). ?>

			</div>
			<?php ap_pagination(); ?>
		</div>

		<?php if ( is_active_sidebar( 'anspress-categories' ) ) : ?>
			<div class="ap-col-sidebar ap-sidebar-categories">
				<?php dynamic_sidebar( 'anspress-categories' ); ?>
			</div>
		<?php endif; ?>

	</div>

</div>
