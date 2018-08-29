<?php
/**
 * Questions feedback template.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;

?>
<div class="ap-feedback ap-feedback-questions">

	<?php
	// Dont have permission.
	if ( ap_user_can_read_questions() ) :
	?>
		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-stop ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'You are not allowed to read questions!', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'You don\'t have enough permission to read the questions of this site.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php else : ?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-question ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'No questions yet!', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg">
					<?php
						printf(
							esc_attr__( 'We have no questions to show. Be the first to %s.', 'anspress-question-answer' ),
							'<a href="' . esc_url( ap_get_link_to( 'ask' ) ) . '">' . esc_attr__( 'ask a question', 'anspress-question-answer' ) . '</a>'
						);
					?>
				</p>
			</div>
		</div>

	<?php endif; ?>

</div>
