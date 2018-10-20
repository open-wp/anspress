<?php
/**
 * Display question archive
 *
 * Template for rendering base of AnsPress.
 *
 * @link https://anspress.io
 * @since 4.2.0
 *
 * @package AnsPress
 * @package Templates
 */

namespace AnsPress\Template;
?>

<?php dynamic_sidebar( 'ap-top' ); ?>

<div class="ap-row">
	<div id="ap-lists" class="<?php echo is_active_sidebar( 'ap-sidebar' ) ? 'ap-col-9' : 'ap-col-12'; ?>">
		<?php
			// Questions sort and filter.
			ap_get_template_part( 'questions-sort-filters' );
		?>

		<?php if ( ap_get_questions() ) : ?>

			<div class="ap-questions">
				<?php
				/* Start the Loop */
				while ( ap_have_questions() ) :
					ap_the_question();
					ap_get_template_part( 'loop-question' );

				endwhile;
				?>
			</div>
			<?php ap_get_template_part( 'pagination-questions' ); ?>

		<?php else : ?>

			<?php ap_get_template_part( 'feedback-questions' ); ?>
			<?php ap_get_template_part( 'login-signup' ); ?>

		<?php endif; ?>

	</div>

	<?php if ( is_active_sidebar( 'ap-sidebar' ) && is_anspress() ) { ?>
		<div class="ap-question-right ap-col-3">
			<?php dynamic_sidebar( 'ap-sidebar' ); ?>
		</div>
	<?php } ?>

</div>
