<?php
/**
 * Tag page
 * Display list of question of a tag
 *
 * @package AnsPress
 * @subpackage Templates
 */
namespace AnsPress;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$question_tag = get_queried_object();
$order_by     = get_current_questions_sorting();
$filter_by    = get_current_questions_filter();

$args = array(
	'ap_order_by'  => $order_by,
	'ap_filter_by' => $filter_by,
	'tax_query' => array(
		array(
			'taxonomy' => 'question_tag',
			'field'    => 'id',
			'terms'    => [ $question_tag->term_id ],
		),
	),
	'pagination_base' => get_term_link( $question_tag, 'question_tag' ),
);

$args = apply_filters( 'ap_tag_question_query_args', $args );

?>

<?php dynamic_sidebar( 'ap-top' ); ?>

<div id="ap-category">

	<?php if ( ! empty( $question_tag->description ) ) : ?>
		<div class="ap-taxo-detail clearfix">
			<p class="ap-taxo-description"><?php echo wp_kses_post( $question_tag->description ); ?></p>
		</div>
	<?php endif; ?>

	<div class="ap-flex-row<?php echo is_active_sidebar( 'anspress-tag' ) ? ' ap-sidebar-active' : ''; ?>">
		<div class="ap-col-main">
			<?php
				// Questions sort and filter.
				ap_get_template_part( 'questions-sort-filters' );
			?>

			<?php if ( ap_get_questions( $args ) ) : ?>

				<?php ap_get_template_part( 'loop-questions' ); ?>
				<?php ap_get_template_part( 'pagination-questions' ); ?>

			<?php else : ?>

				<?php ap_get_template_part( 'feedback-questions' ); ?>
				<?php ap_get_template_part( 'login-signup' ); ?>

			<?php endif; ?>
		</div>

		<?php if ( is_active_sidebar( 'anspress-tag' ) ) : ?>
			<div class="ap-col-sidebar ap-sidebar-tag">
				<?php dynamic_sidebar( 'anspress-tag' ); ?>
			</div>
		<?php endif; ?>

	</div>
</div>
