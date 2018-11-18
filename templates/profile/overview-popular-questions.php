<?php
/**
 * Profile overview popular template.
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @link       https://anspress.io
 * @since      4.2.0
 * @package    AnsPress
 * @subpackage Templates
 */

// Prevent direct access to file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Query args.
$question_args = array(
	'ap_order_by' => 'views',
	'author'      => ap_get_displayed_user_id(),
	'showposts'   => 5,
);

?>

<?php if ( ap_get_questions( $question_args ) ) : ?>

	<?php $i = 1; ?>

	<?php while ( ap_have_questions() ) : ap_the_question(); ?>

		<a href="<?php the_permalink(); ?>" class="ap__item ap-display-flex">
			<div class="ap__count">
				<?php echo number_format_i18n( $i ); ?>
			</div>

			<div>
				<div class="ap__title"><?php the_title(); ?></div>

				<div class="ap__meta">
					<span><?php printf( _n( '%d Answer', '%d Answers', ap_get_answers_count(), 'anspress-question-answer' ), ap_get_answers_count() ); ?></span>

					<span><?php printf( _n( '%d Vote', '%d Votes', ap_get_votes_net(), 'anspress-question-answer' ), ap_get_votes_net() ); ?></span>
				</div>
			</div>

		</a>

		<?php $i++; ?>

	<?php endwhile; ?>

<?php else: ?>

	<div class="ap__no-questions"><?php esc_attr_e( 'No questions posted yet.', 'anspress-question-answer' ); ?></div>

<?php endif; ?>
