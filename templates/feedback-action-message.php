<?php
/**
 * @global array $post_action
 */

defined( 'ABSPATH' ) || exit;

if ( '3' == $post_action['type'] ) {
	$type = 'error';
} elseif ( '2' == $post_action['type'] ) {
	$type = 'warning';
} else {
	$type = 'success';
}

if ( empty( $post_action['msg'] ) ) {
	return;
}

?>
<div class="ap-action-message">
	<?php AnsPress\alert( '', $post_action['msg'], $type ); ?>
</div>
