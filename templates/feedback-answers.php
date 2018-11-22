<?php
/**
 * Answers feedback template.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress;

$paged = anspress()->answers_query->paged;
?>
<div class="ap-feedback ap-feedback-answers mt-20">
	<?php if ( $paged > 1 ) : ?>
		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-answer ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'This question does not have enough items available to paginate', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php
					printf( esc_attr__( 'You are page %d of answers. Click here to go to %s', 'anspress-question-answer' ), $paged, '<a href="' . esc_url( get_question_permalink() ) . '">' . __( 'first page' ) . '</a>' );
				?></p>
			</div>
		</div>
	<?php else : ?>
		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-answer ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'No answers yet.', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'No one has answered this question yet.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>
	<?php endif; ?>
</div>
