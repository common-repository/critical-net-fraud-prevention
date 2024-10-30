<?php
/**
 * Process new customer registrations.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes\Base;

use CNFP_Includes\Helpers\ApiWrapper;

/**
 * Class used to process new customer registrations.
 */
class Registration {

	/**
	 * Register the class.
	 */
	public function register() {
		add_action( 'user_register', array( $this, 'new_registration' ), 1, 2 );
	}

	/**
	 * Send new customer registrations to Critical.net API.
	 *
	 * @param int   $user_id The WordPress user ID.
	 * @param array $user The raw array of data passed to wp_insert_user().
	 */
	public function new_registration( $user_id, $user ) {
		// Get user data.
		try {
			$endpoint  = 'customer-registration';
			$user_data = get_userdata( $user_id )->data;

			$data = array(
				'model_id'             => $user_data->ID,
				'user_name'            => $user_data->user_login,
				'reg_date'             => gmdate( 'Y-m-d H:i:s' ),
				'reg_ip_address'       => ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) ?
					sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) :
					( ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ?
						sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) :
						( ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) ?
							sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) :
							null ) ),
				'customer_information' => array(
					'first_name' => $user_data->user_login,
					'last_name'  => $user_data->user_login,
					'email'      => $user_data->user_email,
				),
			);

			if ( get_option( 'cnfp_licence_key' ) !== '' ) {
				ApiWrapper::post( $endpoint, $data );
			}
		} catch ( Exception $e ) {
			// Silently ignore errors - the end user doesn't need to see an error.
			$e->getMessage();
		}
	}
}
