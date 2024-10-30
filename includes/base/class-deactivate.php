<?php
/**
 * Deactivate Critical.net plugin.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes\Base;

/**
 * Class to deactivate the plugin.
 */
class Deactivate {

	/**
	 * Deactivate the plugin.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

}
