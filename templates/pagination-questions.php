<?php
/**
 * Pagination for questions (in question archive).
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Template;
?>

<?php
/**
 * Before rendering questions pagination.
 *
 * @since 4.2.0
 */
do_action( 'ap_before_questions_pagination' );
?>

<div class="ap-pagination ap-display-flex align-item-center">
	<div class="ap-pagination-links">
		<?php question_pagination_links(); ?>
	</div>

	<div class="ap-pagination-count ap-text-muted ap-text-small">
		<?php question_pagination_count(); ?>
	</div>
</div>

<?php
/**
 * After rendering questions pagination.
 *
 * @since 4.2.0
 */
do_action( 'ap_after_questions_pagination' );
