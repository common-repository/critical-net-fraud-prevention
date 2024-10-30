<?php
/**
 * Activate plugin.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes\Base;

/**
 * Class used to activate the plugin.
 */
class Activate {

	/**
	 * Activate the plugin.
	 */
	public static function activate() {
		flush_rewrite_rules();
	}

}
