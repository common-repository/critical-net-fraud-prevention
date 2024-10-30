<?php
/**
 * Wrapper for calling an API.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes\Helpers;

/**
 * Class to comunicate with Critical.net APIs.
 * Has singleton pattern so we can use only one instance through out execution life cycle.
 * Never make an instance of this class outside.
 * All public methods should be designed to be called statically.
 */
class ApiWrapper {
	/**
	 * Singleton instance.
	 *
	 * @var ApiWrapper
	 */
	protected static $inst = false;

	/**
	 * Base URL for the API.
	 *
	 * @var string
	 */
	protected static $api_base_url = '';

	/**
	 * Constructor, sets the base URL for the API.
	 */
	private function __construct() {
		self::$api_base_url = get_option( 'cnfp_api_url', 'https://api.critical.net' );
	}

	/**
	 * Get the Base URL for the API.
	 *
	 * @return string The Base URL for the API.
	 */
	public function get_base_api_url() {
		return self::$api_base_url;
	}

	/**
	 * Gets the singleton instance.
	 *
	 * @return ApiWrapper The singleton instance.
	 */
	public static function inst() {
		if ( false === self::$inst ) {
			self::$inst = new ApiWrapper();
		}
		return self::$inst;
	}

	/**
	 * Perform a POST API call.
	 *
	 * @param string  $endpoint The API endpoint.
	 * @param array   $params Array of parameters to pass to the API call. Defaults to empty array.
	 * @param boolean $pass_origin_header Should we add the Origin header when calling the API? Defaults to true.
	 * @return string|false JSON-encoded API response, or false if there was an issue encoding the API response.
	 */
	public static function post( $endpoint, $params = array(), $pass_origin_header = true ) {
		return self::call( $endpoint, 'POST', $params, $pass_origin_header );
	}

	/**
	 * Perform a GET API call.
	 *
	 * @param string  $endpoint The API endpoint.
	 * @param array   $params Array of parameters to pass to the API call. Defaults to empty array.
	 * @param boolean $pass_origin_header Should we add the Origin header when calling the API? Defaults to true.
	 * @return string|false JSON-encoded API response, or false if there was an issue encoding the API response.
	 */
	public static function get( $endpoint, $params = array(), $pass_origin_header = true ) {
		return self::call( $endpoint, 'GET', $params, $pass_origin_header );
	}

	/**
	 * Call the API.
	 *
	 * @param string  $endpoint The API endpoint.
	 * @param string  $method The HTTP method to use when calling the API.
	 * @param array   $params Array of parameters to pass to the API call. Defaults to empty array.
	 * @param boolean $pass_origin_header Should we add the Origin header when calling the API? Defaults to true.
	 * @return string|false JSON-encoded API response, or false if there was an issue encoding the API response.
	 */
	protected static function call( $endpoint, $method, $params, $pass_origin_header ) {
		$base_url = self::inst()->get_base_api_url();
		$url      = $base_url . '/' . $endpoint;

		$headers = array(
			'Accept'        => 'application/json',
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . get_option( 'cnfp_licence_key' ),
			'Referer'       => get_site_url(),
		);

		if ( $pass_origin_header ) {
			$headers['Origin'] = get_site_url();
		}

		$args = array(
			'headers' => $headers,
			'timeout' => 20,
		);

		if ( ! empty( $params ) ) {
			$args['body'] = wp_json_encode( $params );
		}

		if ( 'GET' === $method ) {
			$response = wp_remote_get( $url, $args );
		} elseif ( 'POST' === $method ) {
			$response = wp_remote_post( $url, $args );
		} else {
			return wp_json_encode(
				array(
					'code' => 500,
					'data' => "Something went wrong. Error: Unrecognized method $method. Please contact Critical.net support.",
				)
			);
		}

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$result        = wp_json_encode(
				array(
					'code' => 400,
					'data' => "Something went wrong. Error: $error_message. Please contact Critical.net support.",
				)
			);
		} else {
			$result = wp_json_encode(
				array(
					'code' => 200,
					'data' => wp_remote_retrieve_body( $response ),
				)
			);
		}

		return $result;
	}
}
