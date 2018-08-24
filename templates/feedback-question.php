<?php
/**
 * Single question feedback template.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;

?>
<div class="ap-feedback">

	<?php
	// Check if private question.
	if ( is_private() ) :
	?>
		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-lock ap-text-muted mr-20"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'Question is private', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'You are not allowed to read this question. Only limited people can see this question.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php
	// Check if scheduled question.
	elseif ( is_scheduled() ) :
	?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-clock ap-text-muted mr-20"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'Question not published', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'This question is scheduled for publication and is not accessible to anyone until it is published.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php elseif ( is_moderate() ) : // Check if moderate question. ?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-stop ap-text-muted mr-20"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'Question awaiting moderation', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'This question is awaiting moderation and cannot be viewed. Please check back later.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php else : ?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-alert ap-text-muted mr-20"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'Question not readable', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'You do not have enough permission to read this question.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php endif; ?>

</div>
