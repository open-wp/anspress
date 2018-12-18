<?php
/**
 * This file is responsible for displaying single question content inside loop.
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @subpackage Templates
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @since      4.2.0
 */

defined( 'ABSPATH' ) || exit;
?>
<?php
	/**
	 * Action hook triggered before question meta in single question.
	 *
	 * @since 4.1.2
	 */
	do_action( 'ap_before_question_meta' );
?>

<div class="ap-question-meta clearfix">

	<?php if ( $question->is_featured() ) : // Show featured label. ?>
		<span class="ap-display-meta-item check"><?php esc_attr_e( 'Featured', 'anspress-question-answer' ); ?></span>
	<?php endif; ?>

	<?php if ( $question->is_solved() ) : // Show solved label. ?>
		<span class='ap-display-meta-item solved'><i class="apicon-check"></i><?php esc_attr_e( 'Solved', 'anspress-question-answer' ); ?></span>
	<?php endif; ?>

	<?php // Show view counts. ?>
	<span class='ap-display-meta-item views'><i class="apicon-eye"></i><?php printf( _n( '%s view', '%s views', $question->get_view_counts(), 'anspress-question-answer' ), ap_short_num( $question->get_view_counts() ) ); ?></span>

	<?php // Show recent activity. ?>
	<span class='ap-display-meta-item last-activity'><i class="apicon-pulse"></i><?php $question->the_last_active_human_diff(); ?></span>

	<?php //question_metas(); // xss ok. ?>
</div>

<?php
	/**
	 * Action hook triggered after single question meta.
	 *
	 * @since 4.1.5
	 */
	do_action( 'ap_after_question_meta' );
?>
<div ap="question" apid="<?php $question->the_id(); ?>">
	<?php
	/**
	 * Action triggered before question title.
	 *
	 * @since   2.0
	 */
	do_action( 'ap_before_question_title' );
	?>

	<div id="question" role="main" class="ap-content ap-display-flex">
		<a class="ap-content-col ap-avatar-col" href="<?php ap_profile_link(); ?>">
			<?php $question->the_author_avatar( ap_opt( 'avatar_size_qquestion' ) ); ?>
		</a>

		<div apcontentbody class="ap-content-col ap-cell">
			<div class="ap-cell-inner">
				<div class="ap-q-metas">
					<span class="ap-author" itemprop="author" itemscope itemtype="http://schema.org/Person">
						<?php echo ap_user_display_name( [ 'html' => true ] ); ?>
					</span>

					<a href="<?php the_permalink(); ?>" class="ap-posted">
						<?php
						$posted = $question->is_future() ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

						$time = ap_get_time( get_question_id(), 'U' );

						if ( $question->is_future() ) {
							$time = ap_human_time( $time );
						}
						?>
						<time itemprop="datePublished" datetime="<?php echo esc_attr( $question->get_date_created()->date( 'c' ) ); ?>"><?php $question->the_date_created(); ?></time>
					</a>
				</div>

				<!-- Start ap-content-inner -->
				<div class="ap-q-inner">
					<?php
					/**
					 * Action triggered before question content.
					 *
					 * @since   2.0.0
					 */
					do_action( 'ap_before_question_content' );
					?>

					<div class="question-content ap-q-content" itemprop="text">
						<?php $question->the_content(); ?>
					</div>

					<?php
						/**
						 * Action triggered after question content.
						 *
						 * @since   2.0.0
						 */
						do_action( 'ap_after_question_content' );
					?>
				</div>

				<!-- <div class="ap-post-footer ap-display-flex align-item-center"> -->
					<?php do_action( 'ap_question_footer', $question ); ?>
				<!-- </div> -->
			</div>

			<?php
				ap_get_template_part( 'comments/comments', [ 'question' => $question ] ); // Load comments template.
				ap_new_comment_btn( $question->get_id() );
			?>
		</div>

		<!-- Votes button -->
		<div class="ap-content-col ap-single-vote">
			<?php $question->the_votes_button(); ?>
		</div>
	</div>
</div>

<?php
	/**
	 * Action triggered before answers.
	 *
	 * @since   4.1.8
	 */
	do_action( 'ap_before_answers' );
?>
