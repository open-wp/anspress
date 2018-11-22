<?php
/**
 * Profile questions template.
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @link       https://anspress.io
 * @since      4.2.0
 * @package    AnsPress
 * @subpackage Templates
 */

namespace AnsPress;

// Prevent direct access to file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AnsPress\Addons\Profile;
$user_id = ap_get_displayed_user_id();

$order_by  = get_current_questions_sorting();
$filter_by = get_current_questions_filter();

$nav = Profile\nav_links();

// Query args.
$answers_args = array(
	'ap_order_by'     => $order_by,
	'ap_filter_by'    => $filter_by,
	'pagination_base' => $nav['questions']['link'],
	'author'          => $user_id,
	'post_parent'     => 'any',
	'post_status'     => [ 'publish' ],
	'perm'            => 'readable',
	'pagination_base' => $nav['answers']['link'],
);
?>

<?php if ( ap_get_answers( $answers_args ) ) : ?>

	<?php ap_get_template_part( 'answers-sort' ); ?>

	<div class="ap-answers">

		<?php if ( ap_have_answers() ) : ?>

			<?php while ( ap_have_answers() ) : ap_the_answer(); ?>
				<?php ap_get_template_part( 'profile/loop-answer' ); ?>
			<?php endwhile; ?>

		<?php else : ?>

			<?php ap_get_template_part( 'feedback-answers' ); ?>

		<?php endif; ?>

	</div>

	<?php ap_get_template_part( 'pagination-answers' ); ?>

<?php else : ?>

	<div class="ap-feedback ap-feedback-questions">
		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-answer ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'No published answers yet!', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg">
					<?php esc_attr_e( 'This user have not posted any answers yet.', 'anspress-question-answer' ); ?>
				</p>
			</div>
		</div>
	</div>

<?php endif; ?>

