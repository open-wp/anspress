<?php
/**
 * Feedback template for unpublished answers.
 * Shows a message at the top of unpublished answers tab.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;
?>
<div class="ap-feedback ap-feedback-small ap-feedback-unpublished-answer mt-20">

	<div class="ap-display-flex align-item-center">
		<i class="ap-feedback-icon apicon-pin ap-text-muted"></i>
		<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'All answers listed here are not published.', 'anspress-question-answer' ); ?></p>
	</div>

</div>
