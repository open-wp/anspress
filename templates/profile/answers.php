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

namespace AnsPress\Template;

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

	<?php ap_get_template_part( 'feedback-questions' ); ?>
	<?php ap_get_template_part( 'login-signup' ); ?>

<?php endif; ?>

