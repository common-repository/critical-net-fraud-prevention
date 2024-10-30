<?php
/**
 * Add settings admin menu link.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes\Base;

/**
 * Class for adding settings admin menu link.
 */
class SettingsLink {

	/**
	 * Register the class.
	 */
	public function register() {
		add_filter( 'plugin_action_links_' . CRITICAL_PLUGIN_BASENAME, array( $this, 'settings_link' ) );
	}

	/**
	 * Add the settings admin menu link.
	 *
	 * @param array $links Array of action links to be added to.
	 * @return array The array of action links.
	 */
	public function settings_link( $links ) {
		// add custom settings link.
		$settings_link = '<a href="admin.php?page=cnfp_plugin">Settings<a>';
		array_push( $links, $settings_link );
		return $links;
	}

}
