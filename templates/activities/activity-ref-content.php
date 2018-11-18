<?php
/**
 * Activity reference content template.
 *
 * @link       https://anspress.io
 * @since      4.1.2
 * @license    GPL3+
 * @package    AnsPress
 * @subpackage Templates
 *
 * @global object $activities Activity query.
 */

if ( ! $this->has_action() ) {
	return;
}

$type = $this->object->action['ref_type'];

$post_id = $this->object->q_id;

if ( 'answer' === $type && ! empty( $this->object->a_id ) ) {
	$post_id = $this->object->a_id;
} elseif ( 'post' === $type && ! empty( $this->object->a_id ) ) {
	$post_id = $this->object->a_id;
}

if ( 'comment' === $type && ap_user_can_read_comment( $this->object->c_id ) ) {

	echo '<div class="ap__ref-content">';
	echo get_comment_excerpt( $this->object->c_id ) . '<a href="' . ap_get_short_link( [ 'ap_c' => $this->object->c_id ] ) . '">' . __( 'View comment', 'anspress-question-answer' ) . '</a>';
	echo '</div>';

} elseif ( ! empty( $post_id ) && ! $this->in_group ) {

	echo '<div class="ap__ref-content">';
	echo '<div class="ap__ref-content-post">';
	echo '<a href="' . get_permalink( $post_id ) . '" class="ap__title">' . get_the_title( $post_id ) . '</a>';
	echo '<div class="ap__content">' . wp_trim_words( get_post_field( 'post_content', $post_id ), 25, '...' ) . '</div>';
	echo '</div>';
	echo '</div>';
}

