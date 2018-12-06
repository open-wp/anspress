<?php
/**
 * Comments template.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

defined( 'ABSPATH' ) || exit;

// Check if user can read.
if ( ! ap_user_can_read_post( $question->get_id() ) ) {
	return;
}

$active_order = ap_sanitize_unslash( 'comments_order', 'r' );
$active_order = empty( $active_order ) ? 'oldest' : $active_order;

$comments_links = array(
	'oldest'     => __( 'Oldest first', 'anspress-question-answer' ),
	'newest'     => __( 'Newest first', 'anspress-question-answer' ),
	'unapproved' => __( 'Unapproved', 'anspress-question-answer' ),
);

?>

<?php if ( $question->get_comments() ) : ?>

<apcomments id="comments-<?php $question->the_id(); ?>" class="ap-question-comments ap-comments have-comments">
	<div class="ap-comments-header ap-display-flex justify-space-betw">
		<div class="ap-comments-total">
			<?php
				// Translators: Comments count.
				printf( _n( '%s Comment', '%s Comments', $question->get_comment_count(), 'anspress-question-answer' ), '<span itemprop="commentCount">' . esc_html( number_format_i18n( $question->get_comment_count() ) ) . '</span>' );
			?>
		</div>

		<div class="ap-comments-orders">
			<?php foreach ( $comments_links as $link_slug => $link_name ) : ?>

				<?php if ( 'unapproved' === $link_slug && ! ap_user_can_edit_comments() ) : ?>
					<?php continue; ?>
				<?php endif; ?>

				<a ap="apCommentOrder" href="<?php echo esc_url( add_query_arg( [ 'comment_order' => $link_slug ], get_permalink() ) ); ?>"<?php echo $active_order === $link_slug ? ' class="ap-comments-active"' : ''; ?> data-post_id="<?php $question->the_id(); ?>" data-order="<?php echo esc_attr( $link_slug ); ?>">
					<?php echo esc_html( $link_name ); ?>
					<?php if ( 'unapproved' === $link_slug ) : ?>
						<span class="ap-comments-count"><?php $question->the_unapproved_comment_count(); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>

	<?php foreach ( $question->get_comments() as $c ) : ?>
		<?php
			global $comment;
			$comment = $c;

			ap_get_template_part(
				'comments/comment', [
					'question' => $question,
					'comment'  => $comment,
				]
			);

			// Clear global comment.
			$comment = null;
		?>
	<?php endforeach; ?>

	<?php ap_new_comment_btn( $question->get_id() ); ?>

</apcomments>

<?php endif; ?>
