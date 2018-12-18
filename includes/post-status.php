<?php
/**
 * Post status related codes
 *
 * @link     https://anspress.io
 * @since    2.0.1
 * @license  GPL3+
 * @package  AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Post status message.
 *
 * @param mixed $post_id Post.
 * @return string
 * @since 4.0.0
 * @todo make sure to show closed and private status together.
 */
function ap_get_post_status_message( $post_id = false ) {
	$post      = ap_get_post( $post_id );
	$post_type = 'question' === $post->post_type ? __( 'Question', 'anspress-question-answer' ) : __( 'Answer', 'anspress-question-answer' );

	$ret = '';
	$msg = '';
	if ( is_private_post( $post_id ) ) {
		$ret = '<i class="apicon-lock"></i><span>' . sprintf( __( 'This %s is marked as a private, only admin and post author can see.', 'anspress-question-answer' ), $post_type ) . '</span>';
	} elseif ( is_post_waiting_moderation( $post_id ) ) {
		$ret = '<i class="apicon-alert"></i><span>' . sprintf( __( 'This %s is waiting for the approval by the moderator.', 'anspress-question-answer' ), $post_type ) . '</span>';
	} elseif ( is_post_closed( $post_id ) ) {
		$ret = '<i class="apicon-x"></i><span>' . __( 'Question is closed for new answers.', 'anspress-question-answer' ) . '</span>';
	} elseif ( 'trash' === $post->post_status ) {
		$ret = '<i class="apicon-trashcan"></i><span>' . sprintf( __( 'This %s has been trashed, you can delete it permanently from wp-admin.', 'anspress-question-answer' ), $post_type ) . '</span>';
	} elseif ( 'future' === $post->post_status ) {
		$ret = '<i class="apicon-clock"></i><span>' . sprintf( __( 'This %s is not published yet and is not accessible to anyone until it get published.', 'anspress-question-answer' ), $post_type ) . '</span>';
	}

	if ( ! empty( $ret ) ) {
		$msg = '<div class="ap-notice status-' . $post->post_status . ( is_post_closed( $post_id ) ? ' closed' : '' ) . '">' . $ret . '</div>';
	}

	return apply_filters( 'ap_get_post_status_message', $msg, $post_id );
}

/**
 * Return description of a post status.
 *
 * @param  boolean|integer $post_id Post ID.
 * @todo Fix this for closed post.
 */
function ap_post_status_badge( $post_id = false ) {
	$ret = '<postmessage>';
	$msg = ap_get_post_status_message( $post_id );

	if ( ! empty( $msg ) ) {
		$ret .= $msg;
	}

	$ret .= '</postmessage>';

	return $ret;
}
