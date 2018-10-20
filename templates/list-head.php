<?php
/**
 * Display question list header
 * Shows sorting, search, tags, category filter form. Also shows a ask button.
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 *
 * @todo Delete this template
 */
?>

<div class="ap-list-head clearfix">
	<div class="pull-right">
		<?php ap_ask_btn(); ?>
	</div>

	<?php ap_get_template_part( 'search-form' ); ?>
	<?php ap_list_filters(); ?>
</div>


<?php
/**
 * Display an alert showing count for unpublished questions.
 *
 * @since 4.2.13
 */

$unpublished_count = ap_get_unpublished_post_count();

if ( current_user_can( 'ap_edit_others_question' ) && $unpublished_count > 0 ) {
	$text = sprintf( _n( 'is %d question', 'are %d questions', $unpublished_count, 'anspress-question-answer' ), $unpublished_count );

	echo '<div class="ap-unpublished-alert ap-alert warning"><i class="apicon-pin"></i>';
	printf(
		// Translators: Placeholder contain link to unpublished questions.
		esc_html__( 'There %s unpublished. ', 'anspress-question-answer' ),
		'<a href="' . esc_url( ap_get_link_to( '/' ) ) . '?unpublished=true">' . esc_attr( $text ) . '</a>'
	);
	echo '</div>';
}
?>
