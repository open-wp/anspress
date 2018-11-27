<?php
namespace AnsPress;

/**
 * WooCommerce Timezone - helper to retrieve the timezone string for a site until.
 * a WP core method exists (see https://core.trac.wordpress.org/ticket/24730).
 *
 * Adapted from https://secure.php.net/manual/en/function.timezone-name-from-abbr.php#89155.
 *
 * @since 2.1
 * @return string PHP timezone string for the site
 */
function timezone_string() {
	// If site timezone string exists, return it.
	$timezone = get_option( 'timezone_string' );
	if ( $timezone ) {
		return $timezone;
	}
	// Get UTC offset, if it isn't set then return UTC.
	$utc_offset = intval( get_option( 'gmt_offset', 0 ) );
	if ( 0 === $utc_offset ) {
		return 'UTC';
	}
	// Adjust UTC offset from hours to seconds.
	$utc_offset *= 3600;
	// Attempt to guess the timezone string from the UTC offset.
	$timezone = timezone_name_from_abbr( '', $utc_offset );
	if ( $timezone ) {
		return $timezone;
	}
	// Last try, guess timezone string manually.
	foreach ( timezone_abbreviations_list() as $abbr ) {
		foreach ( $abbr as $city ) {
			if ( (bool) date( 'I' ) === (bool) $city['dst'] && $city['timezone_id'] && intval( $city['offset'] ) === $utc_offset ) {
				return $city['timezone_id'];
			}
		}
	}
	// Fallback to UTC.
	return 'UTC';
}

/**
 * Get timezone offset in seconds.
 *
 * @since  3.0.0
 * @return float
 */
function timezone_offset() {
	$timezone = get_option( 'timezone_string' );
	if ( $timezone ) {
		$timezone_object = new DateTimeZone( $timezone );
		return $timezone_object->getOffset( new DateTime( 'now' ) );
	} else {
		return floatval( get_option( 'gmt_offset', 0 ) ) * HOUR_IN_SECONDS;
	}
}

/**
 * Convert mysql datetime to PHP timestamp, forcing UTC. Wrapper for strtotime.
 *
 * Copied from WooCommerce.
 *
 * @since  3.0.0
 * @param  string   $time_string    Time string.
 * @param  int|null $from_timestamp Timestamp to convert from.
 * @return int
 * @author WooCommerce
 */
function string_to_timestamp( $time_string, $from_timestamp = null ) {
	$original_timezone = date_default_timezone_get();
	// @codingStandardsIgnoreStart
	date_default_timezone_set( 'UTC' );
	if ( null === $from_timestamp ) {
		$next_timestamp = strtotime( $time_string );
	} else {
		$next_timestamp = strtotime( $time_string, $from_timestamp );
	}
	date_default_timezone_set( $original_timezone );
	// @codingStandardsIgnoreEnd
	return $next_timestamp;
}