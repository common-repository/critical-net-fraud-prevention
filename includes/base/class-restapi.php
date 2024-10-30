<?php
/**
 * REST API.
 *
 * @package critical-net-fraud-prevention
 * @deprecated No longer used.
 */

namespace CNFP_Includes\Base;

/**
 * Class for the REST API.
 */
class RestApi {

	/**
	 * Register the class.
	 */
	public function register() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'cnfp/v1',
					'cancel-order',
					array(
						'methods'  => 'POST',
						'callback' => array( $this, 'cancel_order' ),
					)
				);
			}
		);
	}

	/**
	 * REST API endpoint for cancelling orders. This no longer does anything and is only provided for backwards compatibility.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return array REST return array.
	 * @deprecated No longer used.
	 */
	public function cancel_order( $request ) {
		// This is here for backwards compatibility.
		return array(
			'status'  => true,
			'message' => 'Order now cancelled from plugin.',
			'data'    => array(),
			'errors'  => array(),
		);
	}
}
