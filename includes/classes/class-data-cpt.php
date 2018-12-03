<?php
/**
 * Common methods between question and answer cpt.
 *
 * @package AnsPress
 * @subpackage Classes
 * @since 4.2.0
 */

namespace AnsPress;
defined( 'ABSPATH' ) || exit;

/**
 * CPT data class.
 *
 * @since 4.2.0
 */
class Data_Cpt extends Abstracts\Data {
	/**
	 * All default meta keys.
	 *
	 * @var array
	 */
	protected $meta_props = [
		'_ap_version'               => 'version',
		'_ap_last_active'           => 'last_active',
		'_ap_last_activity'         => 'last_active',
		'_ap_last_activity_user_id' => 'last_activity_user_id',
	];

	/**
	 * Set version of AnsPress.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_version( $value ) {
		$this->set_prop( 'version', $value );
	}

	/**
	 * Set question last active date.
	 *
	 * @param string $date Date.
	 * @return void
	 */
	public function set_last_active( $date = null ) {
		$this->set_date_prop( 'last_active', $date );
	}

	/**
	 * Set last activity type.
	 *
	 * @param string $value Activity type.
	 * @return void
	 */
	public function set_last_activity( $value ) {
		$this->set_prop( 'last_activity', sanitize_title( $value ) );
	}

	/**
	 * Set last activity user id.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_last_activity_user_id( $value ) {
		$this->set_prop( 'last_activity_user_id', absint( $value ) );
	}

	/**
	 * Set author id.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_author_id( $value ) {
		$this->set_prop( 'author_id', absint( $value ) );
	}

	/**
	 * Set date_created.
	 *
	 * @param  string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
	 * @throws \Exception Exception may be thrown if value is invalid.
	 */
	public function set_date_created( $date = null ) {
		$this->set_date_prop( 'date_created', $date );
	}

	/**
	 * Set date_modified.
	 *
	 * @param  string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
	 * @throws \Exception Exception may be thrown if value is invalid.
	 */
	public function set_date_modified( $date = null ) {
		$this->set_date_prop( 'date_modified', $date );
	}

	/**
	 * Set question up vote counts.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_vote_up_counts( $value ) {
		$this->set_prop( 'vote_up_counts', absint( $value ) );
	}

	/**
	 * Set question down vote counts.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_vote_down_counts( $value ) {
		$this->set_prop( 'vote_down_counts', absint( $value ) );
	}

	/**
	 * Set question net vote counts.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_vote_net_counts( $value ) {
		$this->set_prop( 'vote_net_counts', absint( $value ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get CPT title.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_title( $context = 'view' ) {
		return $this->get_prop( 'title', $context );
	}

	/**
	 * Get CPT content.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_content( $context = 'view' ) {
		return $this->get_prop( 'content', $context );
	}

	/**
	 * Get CPT up vote counts.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_vote_up_counts( $context = 'view' ) {
		return $this->get_prop( 'vote_up_counts', $context );
	}

	/**
	 * Get CPT down vote counts.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_vote_down_counts( $context = 'view' ) {
		return $this->get_prop( 'vote_down_counts', $context );
	}

	/**
	 * Get CPT net vote counts.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_vote_net_counts( $context = 'view' ) {
		return $this->get_prop( 'vote_net_counts', $context );
	}

	/**
	 * Get date_created.
	 *
	 * @param  string $context View or edit context.
	 * @return \AnsPress\DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_created( $context = 'view' ) {
		return $this->get_prop( 'date_created', $context );
	}

	/**
	 * Get date_modified.
	 *
	 * @param  string $context View or edit context.
	 * @return \AnsPress\DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_modified( $context = 'view' ) {
		return $this->get_prop( 'date_modified', $context );
	}

	/**
	 * Return the question statuses without wc- internal prefix.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Output post classes.
	 *
	 * @param string $class Custom CSS classes to append.
	 * @return string
	 * @since 4.2.0
	 */
	public function get_css_classes( $class = '' ) {
		$list = get_post_classes( $class, $this->get_id() );

		if ( is_array( $list ) ) {
			return esc_attr( implode( ' ', $list ) );
		}
	}

	/**
	 * Return label of post status.
	 *
	 * @return string
	 */
	public function get_status_label() {
		$status_obj = get_post_status_object( $this->get_status() );
		return esc_attr( $status_obj->label );
	}

	/**
	 * Check if post is published.
	 *
	 * @return boolean
	 */
	public function is_published() {
		return 'publish' === $this->get_status();
	}

	/**
	 * Get last active date.
	 *
	 * @param string $context Context.
	 * @return boolean
	 */
	public function get_last_active( $context = 'view' ) {
		return $this->get_prop( 'last_active', $context );
	}

	/**
	 * Get CPT version.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_version( $context = 'view' ) {
		return $this->get_prop( 'version', $context );
	}

	/**
	 * Get last activity on post.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_last_activity( $context = 'view' ) {
		return $this->get_prop( 'last_activity', $context );
	}

	/**
	 * Get author id.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return int
	 */
	public function get_author_id( $context = 'view' ) {
		return $this->get_prop( 'author_id', $context );
	}

	/**
	 * Get the user associated with the question. False for guests.
	 *
	 * @return WP_User|false
	 */
	public function get_author() {
		return $this->get_author_id() ? get_user_by( 'id', $this->get_author_id() ) : false;
	}


	/**
	 * Get last activity user id.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_last_activity_user_id( $context = 'view' ) {
		return $this->get_prop( 'last_activity_user_id', $context );
	}

	/**
	 * Get last active time in human readable format.
	 *
	 * @return string
	 */
	public function get_last_active_human_diff() {
		$active_date = $this->get_last_active() ? $this->get_last_active() : $this->get_date_modified();
		return human_time_diff( $active_date->getTimestamp() );
	}

	/**
	 * Get formatted last activity.
	 *
	 * @return string
	 */
	public function get_last_activity_formatted() {
		$activity_type = ap_activity_object()->get_action( $this->get_last_activity() );

		if ( empty( $activity_type ) ) {
			$activity_type = array(
				'verb' => __( 'performed an activity', 'anspress-question-answer' ),
			);
		}

		return sprintf(
			// Translators: %1$s author link, %2$s activity, %3$s time.
			__( '%1$s %2$s %3$s ago', 'anspress-question-answer' ),
			'<a href="' . ap_user_link( $this->get_author_id() ) . '">' . ap_user_display_name( $this->get_author() ) . '</a>',
			$activity_type['verb'],
			$this->get_last_active_human_diff()
		);
	}

	/**
	 * Output method `get_last_activity_formatted`.
	 *
	 * @return void
	 */
	public function the_last_activity() {
		echo $this->get_last_activity_formatted(); // WPCS: XSS safe.
	}

	/**
	 * Get the avatar of the author.
	 *
	 * @param integer $size Avatar size.
	 * @return string
	 */
	public function get_author_avatar( $size = 40 ) {
		return get_avatar( $this->get_author_id(), $size );
	}
}
