<?php
/**
 * AnsPress class auto loader.
 *
 * @link         https://anspress.io/anspress
 * @since        1.0.0
 * @author       Rahul Aryan <support@anspress.io>
 * @package      AnsPressPro
 */

namespace AnsPress;

/**
 * Callback function for auto loading class on demand.
 *
 * @param string $class Name of class.
 * @return boolean True if files is included.
 * @since 4.1.8
 */
function autoloader( $class ) {
	if ( false === strpos( $class, 'AnsPress\\' ) ) {
		return;
	}

	// Replace AnsPress\Pro\ and change to lowercase to fix WPCS warning.
	$file          = strtolower( str_replace( 'AnsPress\\', '', $class ) );
	$filename      = str_replace( '_', '-', str_replace( '\\', '/', $file ) ) . '.php';
	$filename_path = ANSPRESS_DIR . $filename;

	if ( file_exists( ANSPRESS_DIR . 'includes/class/class-' . $filename ) ) {
		$filename_path = ANSPRESS_DIR . 'includes/class/class-' . $filename;
	} elseif ( ! file_exists( $filename ) ) {
		$filename_path = ANSPRESS_DIR . 'includes/' . $filename;
	}

	$filename_path = wp_normalize_path( $filename_path );

	// Check if file exists before including.
	if ( file_exists( $filename_path ) ) {
		require_once $filename_path;

		// Check class exists.
		if ( class_exists( $class ) ) {
			return true;
		}
	}

	return false;
}

spl_autoload_register( __NAMESPACE__ . '\\autoloader' );
