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
<div id="post-<?php answer_id(); ?>" class="<?php post_classes( 'ap-profile-answer' ); ?>" apid="<?php answer_id(); ?>" ap="answer">

	<div class="ap-profile-answer-con">
		<div class="ap-answer-title" itemprop="name">
			<?php ap_question_status(); ?>
			<a itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark"><?php answer_title(); ?></a>
		</div>

		<div class="ap-profile-answer-post_content">
			<?php echo esc_html( wp_trim_words( get_the_content(), 25, '...' ) ); ?>
		</div>

		<div class="ap-profile-answer-meta">
			<span>
				<i class="apicon-clock"></i><?php esc_attr_e( 'Posted', 'anspress-question-answer' ); ?>
				<a href="<?php the_permalink(); ?>" class="ap-posted">
					<time itemprop="datePublished" datetime="<?php echo ap_get_time( get_the_ID(), 'c' ); ?>">
						<?php echo ap_human_time( ap_get_time( get_the_ID(), 'U' ) ); ?>
					</time>
				</a>
			</span>

			<?php
				$activity = ap_recent_activity( null, false );

				if ( ! empty( $activity ) ) {
					echo '<span><i class="apicon-pulse"></i>' . $activity . '</span>';
				}
			?>
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
	</div>

</div>
