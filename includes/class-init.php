<?php
/**
 * Initiate Critical.net plugin.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes;

use CNFP_Includes\Base\WCOrders;

/**
 * Class for initiating the Critical.net plugin.
 */
final class Init {

	/**
	 * Store all the classes inside an array.
	 *
	 * @return array Full list of classes
	 */
	public static function get_services() {
		return array(
			Pages\Admin::class,
			Base\Enqueue::class,
			Base\SettingsLink::class,
			Base\Transaction::class,
			Base\Registration::class,
			Base\RestApi::class,
			Base\WCOrders::class,
		);
	}

	/**
	 * Loop through the classes, initialize them, and call the register() method if it exists.
	 */
	public static function register_services() {
		foreach ( self::get_services() as $class ) {
			$service = new $class();
			if ( method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}
	}

	/**
	 * Checks if the plugin has been upgraded and perform the relevant actions if so.
	 */
	public static function version_check() {
		// If it's not set, we'll set it to the version before we added this check.
		$installed_version = get_option( 'cnfp_version', '1.5.0' );

		if ( version_compare( $installed_version, CRITICAL_PLUGIN_VERSION, '<' ) ) {
			// Plugin has been updated, run the relevant updates.
			if ( version_compare( $installed_version, '1.8.0', '<' ) ) {
				update_option( 'cnfp_api_url', 'https://api.critical.net' );
			}

			if ( version_compare( $installed_version, '1.10.0', '<' ) ) {
				update_option( 'cnfp_front_url', 'https://bo.critical.net' );

				// Populate previous risk scores and transaction IDs.
				WCOrders::populate_meta_data();
			}

			update_option( 'cnfp_version', CRITICAL_PLUGIN_VERSION );
		}
	}
}
