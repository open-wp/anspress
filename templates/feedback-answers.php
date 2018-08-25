<?php
/**
 * Answers feedback template.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;

?>
<div class="ap-feedback ap-feedback-answers mt-20">

	<div class="ap-display-flex align-item-center">
		<i class="ap-feedback-icon apicon-answer ap-text-muted"></i>
		<div>
			<strong class="ap-feedback-title"><?php esc_attr_e( 'No answers yet.', 'anspress-question-answer' ); ?></strong>
			<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'No one has answered this question yet.', 'anspress-question-answer' ); ?></p>
		</div>
	</div>

</div>
