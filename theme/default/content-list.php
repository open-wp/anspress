<?php
global $post;
$clearfix_class = array('question-summary clearfix');
?>

	<article id="question-<?php the_ID(); ?>" <?php post_class($clearfix_class); ?> itemtype="http://schema.org" itemscope="">
		<?php if ( is_sticky() && is_home() && ! is_paged() ) : ?>
		<div class="featured-post">
			<?php _e( 'Featured post', 'ap' ); ?>
		</div>
		<?php endif; ?>
			<div class="wrap-right">				
				<a class="ap-vote-count ap-tip" href="#" title="<?php _e('Total votes', 'ap'); ?>">
					<span><?php echo ap_net_vote(); ?></span> 
					<?php  _e('Votes', 'ap'); ?>
				</a>
				<a class="ap-vote-count ap-tip" href="#" title="<?php _e('Total views', 'ap'); ?>">
					<span><?php echo ap_get_qa_views(); ?></span> 
					<?php  _e('Views', 'ap'); ?>
				</a>
				<a class="ap-answer-count ap-tip" href="<?php echo ap_answers_link(); ?>" title="<?php _e('Total answers', 'ap'); ?>">
					<span><?php echo ap_count_ans_meta(); ?></span>
					<?php _e('Ans', 'ap');?>
				</a>
			</div>
			<div class="ap-list-inner">
				<div class="ap-avatar">
					<a href="<?php echo ap_user_link(); ?>">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 45 ); ?>
					</a>
				</div>								
				<div class="summery wrap-left">
					<h3 class="question-title" itemprop="title">
						<a class="question-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
						<?php echo ap_get_question_label(null, true); ?>
					</h3>					
					<ul class="list-taxo ap-inline-list clearfix">
						<?php if($post->selected): ?>
							<li class="ap-tip ap-ansslable" title="<?php _e('Answer is accepted', 'ap'); ?>">
								<i class="ap-icon-answer"></i>
							</li>
						<?php endif; ?>
						<li class="list-meta ap-tip" title="<?php _e('Last activity', 'ap'); ?>">	
							<i class="ap-icon-clock ap-meta-icon"></i>
							<?php echo ap_get_latest_history_html(get_the_ID())	?>
						</li>
						<li class="ap-tip" title="<?php _e('Question category', 'ap'); ?>"><?php ap_question_categories_html(false, false); ?></li>
						<li class="ap-tip" title="<?php _e('Question tagged', 'ap'); ?>"><?php ap_question_tags_html(false, false); ?></li>
					</ul>
					
				</div>				
			</div>	

	</article><!-- list item -->
