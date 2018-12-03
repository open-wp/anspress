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
$q = new Question( get_the_ID() );

$q->set_last_activity( 'edit_q' );
//$q->set_last_active( current_time( 'timestamp', true ) );
$q->set_view_counts( 4263 );
$q->save();
var_dump($q->get_view_counts());
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
