<?php
/**
 * Critical.net - Fraud Detector and Chargeback Prevention Solution.
 *
 * @package critical-net-fraud-prevention
 * @version 1.16.0
 *
 * Plugin Name: Critical.net - Fraud Detector and Chargeback Prevention Solution
 * Plugin URI: http://wordpress.org/plugins/critical-net-fraud-prevention
 * Description: We offer fraud detection, prevention solutions and data automation strategies. Critical.net protects your woocomerce store from any suspicious or fraudulent transactions. Critical.net keeps your online business safe for you and your customers.
 * Author: Critical.net Team <plugins@critical.net>
 * Author URI: https://www.critical.net
 * Version: 1.16.0
 * Requires at least: 5.8.1
 * Requires PHP: 7.0
 */

defined( 'ABSPATH' ) || exit;

if ( file_exists( dirname( __FILE__ ) . '/includes/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/includes/autoload.php';
}

session_start();

define( 'CRITICAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CRITICAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CRITICAL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'CRITICAL_PLUGIN_VERSION', '1.16.0' );

/**
 * Code that runs during plugin activation.
 */
function cnfp_activate_plugin() {
	CNFP_Includes\Base\Activate::activate();
}

/**
 * Code that runs during plugin deactivation.
 */
function cnfp_deactivate_plugin() {
	CNFP_Includes\Base\Deactivate::deactivate();
}

// Activation.
register_activation_hook( __FILE__, 'cnfp_activate_plugin' );

// Deactivation.
register_deactivation_hook( __FILE__, 'cnfp_deactivate_plugin' );

if ( class_exists( 'CNFP_Includes\\Init' ) ) {
	CNFP_Includes\Init::register_services();
}

/**
 * Version check.
 */
function cnfp_version_check() {
	if ( class_exists( 'CNFP_Includes\\Init' ) ) {
		CNFP_Includes\Init::version_check();
	}
}

add_action( 'plugins_loaded', 'cnfp_version_check' );
