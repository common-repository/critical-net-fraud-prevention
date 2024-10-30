<?php
/**
 * Enqueue scripts and styles.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes\Base;

/**
 * Class used to enqueue scripts and styles.
 */
class Enqueue {

	/**
	 * Register the class.
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue the scripts and styles.
	 */
	public function enqueue() {
		// Add css/js.
		wp_enqueue_style( 'bootstrap', CRITICAL_PLUGIN_URL . 'assets/bootstrap.min.css', array(), CRITICAL_PLUGIN_VERSION );
		wp_enqueue_style( 'critical_net', CRITICAL_PLUGIN_URL . 'assets/critical.css', array(), CRITICAL_PLUGIN_VERSION );
	}
}
