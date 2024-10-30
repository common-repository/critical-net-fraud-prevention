<?php
/**
 * Uninstall Critical.net plugin.
 *
 * @package critical-net-fraud-prevention
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

$option_names = array(
	'cnfp_licence_key',
	'ctc_licence_key',
	'cnfp_api_url',
	'cnfp_version',
);

foreach ( $option_names as $option_name ) {
	delete_option( $option_name );
}
