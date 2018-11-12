<?php
/**
 * User profile helper functions.
 *
 * @author     Rahul Aryan <support@anspress.io>
 * @copyright  2014 AnsPress.io & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.io
 * @since      4.2.0
 * @package    AnsPress
 * @subpackage User Profile Addon
 */

namespace AnsPress\Addons\Profile;

// Die if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profile pages.
 *
 * @return array List of page.
 * @since 4.2.0
 */
function pages() {
	$pages = array(
		'overview' => array(
			'title' => __( 'Overview', 'anspress-question-answer' ),
		),
		'questions' => array(
			'title' => __( 'Questions', 'anspress-question-answer' ),
		),
		'answers' => array(
			'title' => __( 'Answers', 'anspress-question-answer' ),
		),
		'reputations' => array(
			'title' => __( 'Reputations', 'anspress-question-answer' ),
		),
		'activities' => array(
			'title' => __( 'Activities', 'anspress-question-answer' ),
		),
	);

	/**
	 * Filters profile pages.
	 *
	 * @param array $pages Profile pages.
	 * @since 4.2.0
	 */
	return apply_filters( 'ap_profile_page', $pages );
}

/**
 * Return current profile page slug or check current user page.
 *
 * @param string $check Page slug to check if it is current page.
 * @return boolean|string
 *
 * @since 4.2.0
 */
function current_page( $check = false ) {
	$current = '';

	if ( ap_current_page( 'profile' ) ) {

		$query_var  = get_query_var( 'profile_page', 'overview' );
		$page_slugs = array_keys( pages() );

		if ( in_array( $query_var, $page_slugs, true ) ) {
			$current = $query_var;
		}
	}

	/**
	 * Filter current user page.
	 *
	 * @return array $current Current user page.
	 * @since 4.2.0
	 */
	$current = apply_filters( 'ap_profile_current_page', $current );

	if ( false !== $check ) {
		return ( $check === $current ? true : false );
	}

	return $current;
}

/**
 * Profile navigation links.
 *
 * @param integer|false $user_id User id. Currently display user id will be used by default.
 * @return array
 * @since 4.2.0
 */
function nav_links( $user_id = false ) {
	$user_id     = ( false !== $user_id ? $user_id : ap_get_displayed_user_id() );
	$current_tab = get_query_var( 'user_page', 'questions' );
	$user_link   = ap_user_link( $user_id );

	$nav = [];

	foreach ( pages() as $slug => $args ) {
		$active_class = current_page( $slug ) ? ' ap-nav-active' : '';

		$nav[ $slug ] = array(
			'id'    => "ap-nav-item-$slug",
			'class' => "ap-nav-item $active_class",
			'title' => $args['title'],
			'slug'  => $slug,
		);

		if ( 'questions' === $slug ) {
			$nav[ $slug ]['count'] = '1.4k';
		}

		$page_slug = $slug;
		if ( 'overview' === $slug ) {
			$page_slug = '';
		}

		if ( get_option( 'permalink_structure' ) ) {
			$nav[ $slug ]['link'] = user_trailingslashit( trailingslashit( $user_link ) . $page_slug );
		} else {
			$nav[ $slug ]['link'] = add_query_arg( [ 'profile_page' => $page_slug ], home_url() );
		}
	}

	return apply_filters( 'ap_profile_nav_links', $nav, $user_id );
}

/**
 * Load profile page content.
 *
 * @since 4.2.0
 */
function profile_page_content() {
	$user_page     = get_query_var( 'profile_page', 'overview' );
	$template_file = ap_get_theme_location( 'profile/' . $user_page . '.php' );

	echo '<div class="ap-profile-content">';

	if ( file_exists( $template_file ) ) {
		ap_get_template_part( 'profile/' . trim( $user_page ) );
	} else {
		printf( esc_attr__( 'No template file exists for profile page "%s".', 'anspress-question-answer' ), $user_page );
	}

	echo '</div>';
}
