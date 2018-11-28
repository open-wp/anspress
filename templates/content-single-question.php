<?php
/**
 * This file is responsible for displaying single question page.
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @subpackage Templates
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @since      4.2.0
 */

namespace AnsPress;
$question = new Question();
var_dump($question->get_title());
//$question->set_title('');
//$question->set_content('Sed id lacus enim, sit amet imperdiet orci.');
// $question->set_best_answer_id(23456);
// $question->set_view_counts(3002);
// $question->set_answer_counts(41);
// $question->set_vote_down_counts(20);
// $question->set_vote_up_counts(5);
// $question->set_status( 'private' );
var_dump($question->save());
$question = new Question(4658);
var_dump($question->get_title());
?>
<div id="ap-single" class="ap-q clearfix">

	<div class="ap-question-lr ap-row" itemtype="https://schema.org/Question" itemscope="">
		<div class="ap-q-left <?php echo ( is_active_sidebar( 'ap-qsidebar' ) ) ? 'ap-col-8' : 'ap-col-12'; ?>">
			<?php
			ap_get_template_part( 'loop-single-question' );

			ap_get_template_part( 'loop-answers' );

			// Get answer form.
			ap_get_template_part( 'answer-form' );
			?>
		</div>

		<?php if ( is_active_sidebar( 'ap-qsidebar' ) ) : ?>
			<div class="ap-question-right ap-col-4">
				<div class="ap-question-info">
					<?php dynamic_sidebar( 'ap-qsidebar' ); ?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>
