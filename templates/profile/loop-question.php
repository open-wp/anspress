<?php
/**
 * Template used for generating single answer item.
 *
 * @author     Rahul Aryan <support@anspress.io>
 * @link       https://anspress.io/anspress
 * @package    AnsPress
 * @subpackage Templates
 * @since      4.2.0
 */

namespace AnsPress;

?>
<div id="question-<?php the_ID(); ?>" class="<?php post_classes( 'ap-profile-question' ); ?>"" itemtype="https://schema.org/Question" itemscope="">

	<div class="ap-profile-question-con">
		<div class="ap-question-title" itemprop="name">
			<?php ap_question_status(); ?>
			<a itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
		</div>

		<div class="ap-display-question-meta">
			<?php echo ap_question_metas(); ?>
		</div>
	</div>

	<div class="ap-list-counts">
		<!-- Votes count -->
		<?php if ( ! ap_opt( 'disable_voting_on_question' ) ) : ?>
			<span class="ap-questions-count ap-questions-vcount">
				<span itemprop="upvoteCount"><?php ap_votes_net(); ?></span>
				<?php _e( 'Votes', 'anspress-question-answer' ); ?>
			</span>
		<?php endif; ?>

		<!-- Answer Count -->
		<a class="ap-questions-count ap-questions-acount" href="<?php echo ap_answers_link(); ?>">
			<span itemprop="answerCount"><?php ap_answers_count(); ?></span>
			<?php _e( 'Ans', 'anspress-question-answer' ); ?>
		</a>
	</div>
</div><!-- list item -->
