<?php
/**
 * AnsPress answer loop related functions and classes
 *
 * @package      AnsPress
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-3.0+
 * @link         https://anspress.io
 * @copyright    2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Echo post status of a answer.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 */
function ap_answer_status( $_post = null ) {
	ap_question_status( $_post );
}
