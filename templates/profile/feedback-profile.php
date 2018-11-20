<?php
/**
 * Profile feedback template.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;

?>
<div class="ap-feedback ap-feedback-answer">

	<div class="ap-display-flex align-item-center">
		<i class="ap-feedback-icon apicon-alert ap-text-muted"></i>
		<div>
			<strong class="ap-feedback-title"><?php esc_attr_e( 'No profile found!', 'anspress-question-answer' ); ?></strong>
			<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'Sorry! we can\'t find the profile of this user.', 'anspress-question-answer' ); ?></p>
		</div>
	</div>

</div>
