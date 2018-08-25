<?php
/**
 * Answer feedback template.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;

?>
<div class="ap-feedback ap-feedback-answer">

	<?php
	// Check if private answer.
	if ( is_private() ) :
	?>
		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-lock ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'Answer is private', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'You are not allowed to read this answer. Only limited people can see this answer.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php
	// Check if scheduled answer.
	elseif ( is_scheduled() ) :
	?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-clock ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'Answer not published', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'This answer is scheduled for publication and is not accessible to anyone until it is published.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php elseif ( is_moderate() ) : // Check if moderate answer. ?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-stop ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'Answer awaiting moderation', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'This answer is awaiting moderation and cannot be viewed. Please check back later.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php else : ?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-alert ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'Answer not readable', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'You do not have enough permission to read this answer.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php endif; ?>

</div>
