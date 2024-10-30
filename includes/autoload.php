<?php
/**
 * Autoloader for WordPress coding standard classes.
 * Modified from https://gist.github.com/sheabunge/50a9d9f8234820a989ab.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes;

/**
 * Enable autoloading of plugin classes in namespace.
 *
 * @param string $class_name The class to autoload.
 */
function autoload( $class_name ) {

	// Only autoload classes from this namespace.
	if ( false === strpos( $class_name, __NAMESPACE__ ) ) {
		return;
	}

	// Remove namespace from class name.
	$class_file = str_replace( __NAMESPACE__ . '\\', '', $class_name );

	// Convert class name format to file name format.
	$class_file = strtolower( $class_file );
	$class_file = str_replace( '_', '-', $class_file );

	// Convert sub-namespaces into directories.
	$class_path = explode( '\\', $class_file );
	$class_file = array_pop( $class_path );
	$class_path = implode( '/', $class_path );

	// Load the class.
	require_once __DIR__ . ( ! empty( $class_path ) ? ( '/' . $class_path ) : '' ) . '/class-' . $class_file . '.php';
}

spl_autoload_register( __NAMESPACE__ . '\autoload' );
