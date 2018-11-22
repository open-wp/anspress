<?php
/**
 * Feedback template for ask page.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress;

?>
<div class="ap-feedback ap-feedback-ask">

	<div class="ap-display-flex align-item-center">
		<i class="ap-feedback-icon apicon-lock ap-text-muted"></i>
		<div>
			<strong class="ap-feedback-title"><?php esc_attr_e( 'No Permission!', 'anspress-question-answer' ); ?></strong>
			<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'Sorry! You don\'t have enough permission to post a question.', 'anspress-question-answer' ); ?></p>
		</div>
	</div>
</div>
