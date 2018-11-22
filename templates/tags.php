<?php
/**
 * Tags page layout
 *
 * @link http://anspress.io
 * @since 1.0
 *
 * @package AnsPress
 */

namespace AnsPress;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

global $question_tags, $ap_max_num_pages, $ap_per_page, $tags_rows_found;

$paged    = max( 1, get_query_var( 'paged' ) );
$per_page = (int) ap_opt( 'tags_per_page' );
$per_page = 40; // 0 === $per_page ? 1 : $per_page;
$offset   = $per_page * ( $paged - 1 );

$tag_args = array(
	'taxonomy'      => 'question_tag',
	'ap_tags_query' => true,
	'parent'        => 0,
	'number'        => $per_page,
	'offset'        => $offset,
	'hide_empty'    => false,
	'order'         => 'DESC',
);

$ap_sort = ap_isset_post_value( 'tags_order', 'count' );

if ( 'new' === $ap_sort ) {
	$tag_args['orderby'] = 'id';
	$tag_args['order']   = 'DESC';
} elseif ( 'name' === $ap_sort ) {
	$tag_args['orderby'] = 'name';
	$tag_args['order']   = 'ASC';
} else {
	$tag_args['orderby'] = 'count';
}

/**
 * Filter applied before getting tags.
 *
 * @var array
 */
$tag_args = apply_filters( 'ap_tags_shortcode_args', $tag_args );

$query = new \WP_Term_Query( $tag_args );

// Count terms.
$tag_args['fields'] = 'count';
$found_query        = new \WP_Term_Query( $tag_args );
$tags_rows_found    = $found_query->get_terms();
$ap_max_num_pages   = ceil( $tags_rows_found / $per_page );
$question_tags      = $query->get_terms();
?>

<?php dynamic_sidebar( 'ap-top' ); ?>

<div id="ap-tags" class="ap-tags">
	<div class="ap-flex-row<?php echo is_active_sidebar( 'anspress-tags' ) ? ' ap-sidebar-active' : ''; ?>">

		<div class="ap-col-main">
			<div class="ap-tags-items">

				<?php foreach ( $question_tags as $key => $tag ) : ?>

				<div class="ap-tags-item">
					<a class="ap-tags-name" href="<?php echo esc_url( get_tag_link( $tag ) ); ?>">
						<?php echo esc_html( $tag->name ); ?>
					</a>

					<div class="ap-tags-count">
						<?php
							printf(
								_n( '%d Question', '%d Questions', $tag->count, 'anspress-question-answer' ),
								(int) $tag->count
							);
						?>
					</div>

					<div class="ap-tags-desc mt-5 fs-sm">
						<?php echo esc_html( wp_trim_words( $tag->description, 20, '...' ) ); ?>
					</div>
				</div>

				<?php endforeach; ?>

			</div><!-- close #ap-tags -->

			<?php ap_pagination(); ?>
		</div>

		<?php if ( is_active_sidebar( 'anspress-tags' ) ) : ?>
			<div class="ap-col-sidebar ap-sidebar-tags">
				<?php dynamic_sidebar( 'anspress-tags' ); ?>
			</div>
		<?php endif; ?>

	</div>
</div><!-- close .row -->
