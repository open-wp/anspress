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

namespace AnsPress\Template;

?>
<div id="question-<?php the_ID(); ?>" class="<?php post_classes( 'ap-questions-item' ); ?>"" itemtype="https://schema.org/Question" itemscope="">
	<div class="ap-questions-inner">
		<div class="ap-avatar ap-pull-left">
			<a href="<?php ap_profile_link(); ?>">
				<?php ap_author_avatar( ap_opt( 'avatar_size_list' ) ); ?>
			</a>
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

		<div class="ap-questions-summery">
			<span class="ap-questions-title" itemprop="name">
				<?php ap_question_status(); ?>
				<a class="ap-questions-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
			</span>
			<div class="ap-display-question-meta">
				<?php echo ap_question_metas(); ?>
			</div>
		</div>
	</div>
</div><!-- list item -->
