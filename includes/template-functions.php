<?php
/**
 * Template Functions
 *
 * @package AnsPress
 * @subpackage Functions
 * @since 4.2.0
 */

namespace AnsPress;

// Bail if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add checks for AnsPress conditions to parse_query.
 *
 * @param WP_Query $posts_query Wp query.
 * @return mixed
 * @since 4.2.0
 */
function parse_query( $posts_query ) {
	// Bail if $posts_query is not the main loop.
	if ( ! $posts_query->is_main_query() ) {
		return;
	}

	// Bail if filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) ) {
		return;
	}

	// Bail if in admin
	if ( is_admin() ) {
		return;
	}

	$object  = get_queried_object();
	$ap_user = $posts_query->get( 'ap_user_name' );

	if ( isset( $posts_query->query_vars['ap_search'] ) ) {

		// Check if there are search query args set
		$search_terms = ap_get_search_terms();
		if ( ! empty( $search_terms ) ) {
			$posts_query->ap_search_terms = $search_terms;
		}

		// Correct is_home variable
		$posts_query->is_home = false;

		// We are in a search query
		$posts_query->ap_is_search = true;
		$posts_query->is_search    = true;
	} elseif ( isset( $posts_query->query_vars['question_category'] ) ) {
		$posts_query->is_home        = false;
		$posts_query->ap_is_category = true;

	} elseif ( isset( $posts_query->query_vars['question_tag'] ) ) {
		$posts_query->is_home   = false;
		$posts_query->ap_is_tag = true;
		$posts_query->is_tax    = true;
	} elseif ( ! empty( $ap_user ) ) {
		$the_user = false;
		$the_user = get_user_by( 'slug', $ap_user );

		if ( empty( $the_user->ID ) ) {
			$posts_query->set_404();
			return;
		}

		$posts_query->ap_is_profile = true;
		$posts_query->is_404        = false;
		$posts_query->is_home       = false;

		if ( get_current_user_id() === $the_user->ID ) {
			$posts_query->ap_is_user_home = true;
		}

		// Set bbp_user_id for future reference.
		$posts_query->set( 'ap_user_id', $the_user->ID );

		// Set author_name as current user's nicename to get correct posts.
		$posts_query->set( 'author_name', $the_user->user_nicename );

		// Set the displayed user global to this user.
		anspress()->displayed_user = $the_user;

	} elseif ( $object instanceof WP_Post && 'page' === $object->post_type && ap_main_pages_id( 'profile' ) == $object->ID && empty( $ap_user ) ) {

		// Show 404 if user not set.
		$posts_query->set_404();
		return;

	}
}