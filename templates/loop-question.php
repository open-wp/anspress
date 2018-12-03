<?php
/**
 * Template used for generating single answer item.
 *
 * @author     Rahul Aryan <support@anspress.io>
 * @link       https://anspress.io/anspress
 * @package    AnsPress
 * @subpackage Templates
 *
 * @global AnsPress\Question $question Question object.
 *
 * @since      4.2.0
 */

namespace AnsPress;

?>
<div id="question-<?php $question->the_id(); ?>" class="<?php $question->the_css_classes( 'ap-questions-item ap-loop-question' ); ?>" itemtype="https://schema.org/Question" itemscope>
	<div class="ap-questions-inner">
		<div class="ap__avatar">
			<a href="<?php ap_profile_link(); ?>">
				<?php $question->the_author_avatar( ap_opt( 'avatar_size_list' ) ); ?>
			</a>
		</div>

		<div class="ap__counts">
			<!-- Votes count -->
			<?php if ( ! ap_opt( 'disable_voting_on_question' ) ) : ?>
				<span class="ap__count ap__count-votes">
					<span itemprop="upvoteCount"><?php $question->the_vote_net_counts(); ?></span>
					<?php _e( 'Votes', 'anspress-question-answer' ); ?>
				</span>
			<?php endif; ?>

			<!-- Answer Count -->
			<a class="ap__count ap__count-answers" href="<?php echo ap_answers_link(); ?>">
				<span itemprop="answerCount"><?php $question->the_answer_counts(); ?></span>
				<?php _e( 'Ans', 'anspress-question-answer' ); ?>
			</a>
		</div>

		<div class="ap__summery">
			<span class="ap__title" itemprop="name">

				<?php if ( ! $question->is_published() ) : // Show post status if not published. ?>
					<span class="ap__post-status <?php $question->the_status(); ?>"><?php $question->the_status_label(); ?></span>
				<?php endif; ?>

				<a class="ap-questions-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark"><?php $question->the_title(); ?></a>

			</span>
			<div class="ap__info">
				<?php
					/**
					 * Custom information of question can be shown here.
					 *
					 * @param \AnsPress\Question $question Question object.
					 *
					 * @since 4.2.0
					 */
					do_action( 'ap_question_loop_info_before', $question );
				?>

				<?php if ( $question->is_featured() ) : // Show featured label. ?>
					<span class="ap__info-item check"><?php esc_attr_e( 'Featured', 'anspress-question-answer' ); ?></span>
				<?php endif; ?>

				<?php if ( $question->is_solved() ) : // Show solved label. ?>
					<span class='ap__info-item solved'><i class="apicon-check"></i><?php esc_attr_e( 'Solved', 'anspress-question-answer' ); ?></span>
				<?php endif; ?>

				<?php // Show view counts. ?>
				<span class='ap__info-item views'><i class="apicon-eye"></i><?php printf( _n( '%s view', '%s views', $question->get_view_counts(), 'anspress-question-answer' ), ap_short_num( $question->get_view_counts() ) ); ?></span>

				<?php // Show recent activity. ?>
				<span class='ap__info-item views'><i class="apicon-pulse"></i><?php $question->the_last_activity(); ?></span>

				<?php
					/**
					 * Custom information of question can be shown here.
					 *
					 * @param \AnsPress\Question $question Question object.
					 *
					 * @since 4.2.0
					 */
					do_action( 'ap_question_loop_info_after', $question );
				?>
			</div>
		</div>
	</div>
</div><!-- list item -->
