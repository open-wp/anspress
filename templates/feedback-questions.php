<?php
/**
 * Questions feedback template.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;

$search_str = ap_isset_post_value( 'question_s' );

?>
<div class="ap-feedback ap-feedback-questions">

	<?php
	// Dont have permission.
	if ( ! ap_user_can_read_questions() ) :
	?>
		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-stop ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'You are not allowed to read questions!', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg"><?php esc_attr_e( 'You don\'t have enough permission to read the questions of this site.', 'anspress-question-answer' ); ?></p>
			</div>
		</div>

	<?php elseif ( ap_get_search_terms() ) : ?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-search ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'No questions found matching your query.', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg">
					<?php esc_attr_e( 'Try removing filters or try with differnt search term ', 'anspress-question-answer' ); ?>
				</p>
			</div>
		</div>

	<?php elseif ( ap_current_page( 'category' ) ) : ?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-category ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'No questions in this category.', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg">
					<?php esc_attr_e( 'Try browsing other categories.', 'anspress-question-answer' ); ?>
				</p>
			</div>
		</div>

	<?php elseif ( ap_current_page( 'tag' ) ) : ?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-tag ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'No questions in this tag.', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg">
					<?php esc_attr_e( 'Try browsing other tags.', 'anspress-question-answer' ); ?>
				</p>
			</div>
		</div>

	<?php else : ?>

		<div class="ap-display-flex align-item-center">
			<i class="ap-feedback-icon apicon-question ap-text-muted"></i>
			<div>
				<strong class="ap-feedback-title"><?php esc_attr_e( 'No published questions yet!', 'anspress-question-answer' ); ?></strong>
				<p class="mb-0 ap-feedback-msg">
					<?php
						printf(
							esc_attr__( 'Check back later or be the first to %s.', 'anspress-question-answer' ),
							'<a href="' . esc_url( ap_get_link_to( 'ask' ) ) . '">' . esc_attr__( 'post a question', 'anspress-question-answer' ) . '</a>'
						);
					?>
				</p>
			</div>
		</div>

	<?php endif; ?>

</div>
