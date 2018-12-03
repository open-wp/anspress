<?php
/**
 * Template used for generating questions loop.
 *
 * @author     Rahul Aryan <support@anspress.io>
 * @link       https://anspress.io/anspress
 * @package    AnsPress
 * @subpackage Templates
 * @since      4.2.0
 */

namespace AnsPress;

?>
<div class="ap-questions">
	<?php
	/* Start the Loop */
	while ( ap_have_questions() ) : ap_the_question();
		global $post;
		$question = ap_get_question( $post );

		ap_get_template_part( 'loop-question', [ 'question' => $question ] );
	endwhile;
	?>
</div>
