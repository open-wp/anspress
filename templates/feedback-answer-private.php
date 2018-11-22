<?php
/**
 * Private answer feedback template.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress;
?>
<div class="ap-feedback ap-feedback-answer-private">
	<div class="ap-display-flex align-item-center direction-column">
		<i class="apicon-lock ap-feedback-icon"></i>
		<div>
			<?php esc_attr_e( 'Private answer. Only asker can view the contents.', 'anspress-question-answer' ); ?>
		</div>';
	</div>
</div>
