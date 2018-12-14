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

$comments_links = array(
	'oldest'     => __( 'Oldest first', 'anspress-question-answer' ),
	'newest'     => __( 'Newest first', 'anspress-question-answer' ),
);

if ( $question->get_unapproved_comment_count() > 0 ) {
	$comments_links['unapproved'] = __( 'Unapproved', 'anspress-question-answer' );
}

// Show unapproved comments if not approved comments.
if ( empty( $active_order ) && $question->get_unapproved_comment_count() > 0 && 0 === $question->get_comment_count() && ap_user_can_approve_comment() ) {
	$active_order = 'unapproved';
} else {
	$active_order = ! isset( $comments_links[ $active_order ] ) ? 'oldest' : $active_order;
}

$comments = $question->get_comments( [ 'display_order' => $active_order ] );
?>

<?php if ( $comments || ( $question->get_unapproved_comment_count() > 0 && ap_user_can_approve_comment() ) ) : ?>
	<apcomments id="comments-<?php $question->the_id(); ?>" class="ap-question-comments ap-comments">
		<div class="ap-comments-header ap-display-flex justify-space-betw">
			<div class="ap-comments-total">
				<?php
					$counts = 'unapproved' !== $active_order ? $question->get_comment_count() : $question->get_unapproved_comment_count();

					// Translators: Comments count.
					printf( _n( '%s Comment', '%s Comments', $counts, 'anspress-question-answer' ), '<span itemprop="commentCount" ap="commentCount-' . $question->get_id() . '">' . esc_html( number_format_i18n( $counts ) ) . '</span>' );
				?>
			</div>

			<div class="ap-comments-orders">
				<?php foreach ( $comments_links as $link_slug => $link_name ) : ?>

					<?php if ( 'unapproved' === $link_slug && ! ap_user_can_approve_comment() ) : ?>
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

		<?php if ( $comments ) : ?>
			<?php foreach ( $comments as $c ) : ?>
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
		<?php else : ?>
			<div class="ap-feedback ap-feedback-comments ap-feedback-small">
				<div class="ap-display-flex align-item-center">
					<i class="ap-feedback-icon apicon-lock ap-text-muted"></i>
					<div>
						<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'No comments to show.', 'anspress-question-answer' ); ?></p>
					</div>
				</div>
			</div>
			<p><?php esc_attr_e( '', 'anspress-question-answer' ); ?></p>
		<?php endif; ?>

	</apcomments>

<?php endif; ?>
