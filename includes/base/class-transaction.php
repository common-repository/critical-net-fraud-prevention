<?php
/**
 * Process new transaction.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes\Base;

use CNFP_Includes\Helpers\ApiWrapper;

/**
 * Class for processing new transactions.
 */
class Transaction {

	/**
	 * Register the class.
	 */
	public function register() {
		// Standard WooCommerce Orders.
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'new_transaction' ), 1, 3 );

		// WooCommerce Subscriptions create the order first, then copy the data from the parent subscription order. When this order is created the woocommerce_new_order hook is still called.
		// The first order created doesn't have the information we need, so we need to call the API after the new order has been populated.
		add_filter( 'wcs_new_order_created', array( $this, 'new_subscription_transaction' ), 1, 3 );
	}

	/**
	 * Define the new_transaction callback for normal WooCommerce orders.
	 *
	 * @param int                           $order_id The WooCommerce order ID.
	 * @param array                         $posted_data The posted data from the WooCommerce checkout form.
	 * @param bool|WC_Order|WC_Order_Refund $order The WooCommerce order object or false if there was an error.
	 */
	public function new_transaction( $order_id, $posted_data, $order ) {
		// Call the API.
		try {
			$order_data = $order->get_data();

			if ( ! empty( $order_data['billing']['first_name'] ) && ! empty( $order_data['billing']['last_name'] ) ) {
				$this->call_new_transaction( $order );
			}
		} catch ( Exception $e ) {
			// Silently ignore errors - the end user doesn't need to see an error.
			$e->getMessage();
		}
	}

	/**
	 * Define the new_subscription_transaction callback for WooCommerce Subscription orders.
	 *
	 * @param bool|WC_Order|WC_Order_Refund $new_order The new WooCommerce order created when subscription renews, or false if there is an error.
	 * @param WC_Subscription|int           $subscription WooCommerce Subscription that the new order is based from.
	 * @param string                        $type Type of the new order. Default values are 'renewal_order'|'resubscribe_order'.
	 * @return bool|WC_Order|WC_Order_Refund $new_order The new WooCommerce order created when subscription renews, or false if there is an error.
	 */
	public function new_subscription_transaction( $new_order, $subscription, $type ) {
		// Call the API.
		$this->call_new_transaction( $new_order );

		return $new_order;
	}

	/**
	 * Call the new-transaction API and process the WooCommerce order.
	 * If the Critical.net API rejects the order, cancel it and add a note.
	 *
	 * @param bool|WC_Order|WC_Order_Refund $order The WooCommerce order object or false if there was an error.
	 */
	public function call_new_transaction( $order ) {
		// get order details data...
		try {
			$endpoint   = 'new-transaction';
			$order_data = $order->get_data();

			$tokens = $order->get_payment_tokens();

			if ( empty( $tokens ) ) {
				$tokens = \WC_Payment_Tokens::get_customer_tokens( $order_data['customer_id'], $order_data['payment_method'] );
			}

			if ( ! empty( $tokens ) ) {
				$last4 = array_values( $tokens )[0]->get_last4();
			}

			$data = array(
				'model_id'             => $order_data['id'],
				'user_name'            => $order_data['billing']['email'],
				'user_number'          => $order_data['customer_id'],
				'reg_date'             => gmdate( 'Y-m-d H:i:s' ),
				'reg_ip_address'       => $order_data['customer_ip_address'],
				'customer_information' => array(
					'first_name'  => $order_data['billing']['first_name'],
					'last_name'   => $order_data['billing']['last_name'],
					'email'       => $order_data['billing']['email'],
					'address1'    => $order_data['billing']['address_1'],
					'address2'    => $order_data['billing']['address_2'],
					'city'        => $order_data['billing']['city'],
					'province'    => $order_data['billing']['state'],
					'postal_code' => $order_data['billing']['postcode'],
					'country'     => $order_data['billing']['country'],
					'phone1'      => $order_data['billing']['phone'],
				),
				'billing_information'  => array(
					'first_name'  => $order_data['billing']['first_name'],
					'last_name'   => $order_data['billing']['last_name'],
					'email'       => $order_data['billing']['email'],
					'address1'    => $order_data['billing']['address_1'],
					'address2'    => $order_data['billing']['address_2'],
					'city'        => $order_data['billing']['city'],
					'province'    => $order_data['billing']['state'],
					'postal_code' => $order_data['billing']['postcode'],
					'country'     => $order_data['billing']['country'],
					'phone1'      => $order_data['billing']['phone'],
				),
				'shipping_information' => array(
					'first_name'  => $order_data['shipping']['first_name'],
					'last_name'   => $order_data['shipping']['last_name'],
					'address1'    => $order_data['shipping']['address_1'],
					'address2'    => $order_data['shipping']['address_2'],
					'city'        => $order_data['shipping']['city'],
					'province'    => $order_data['shipping']['state'],
					'postal_code' => $order_data['shipping']['postcode'],
					'country'     => $order_data['shipping']['country'],
					'phone1'      => $order_data['shipping']['phone'],
				),
				'amount'               => $order_data['total'],
				'currency'             => $order_data['currency'],
			);

			if ( isset( $last4 ) ) {
				$data['payment_method'] = array( 'last_digits' => $last4 );
			}

			if ( get_option( 'cnfp_licence_key' ) !== '' ) {
				$response = ApiWrapper::post( $endpoint, $data );

				if ( isset( $response ) ) {
					$api_response = json_decode( $response, true );

					if ( isset( $api_response['code'] ) && 200 === $api_response['code'] && isset( $api_response['data'] ) ) {
						// cancel order depending on response.
						$resp_data = json_decode( $api_response['data'], true );

						if ( $resp_data['status'] && isset( $resp_data['data'] ) ) {
							if ( isset( $resp_data['data']['score'] ) ) {
								// add score.
								add_post_meta( $order_data['id'], '_critical_net_risk_score', $resp_data['data']['score'], true );
							}

							if ( isset( $resp_data['data']['trans_id'] ) ) {
								// add transaction ID.
								add_post_meta( $order_data['id'], '_critical_net_transaction_id', $resp_data['data']['trans_id'], true );
							}

							if ( isset( $resp_data['data']['rec'] ) && 'Rejected' === $resp_data['data']['rec'] ) {
								// Failed fraud check.
								$order->update_status( 'cancelled', __( 'Cancelled from Critical.net.', 'woocommerce' ) );

								wc_add_notice( sprintf( 'This order [%d] failed our fraud detection process, please contact us for more details.', $order->get_id() ), 'error' );

								wc_empty_cart();

								if ( ! wp_doing_ajax() ) {
									wp_safe_redirect( wc_get_cart_url() );
									exit;
								} else {
									wp_send_json(
										array(
											'result'   => 'success',
											'redirect' => wc_get_cart_url(),
										)
									);
									exit;
								}
							}
						} else {
							$message = isset( $resp_data['message'] ) ? $resp_data['message'] : '';
							$order->add_order_note( "Something went wrong. Error: $message. Please contact Critical.net support." );
						}
					} else {
						$message = isset( $api_response['data'] ) ? $api_response['data'] : 'Error connecting to Critical.net';
						$order->add_order_note( $message );
					}
				} else {
					$order->add_order_note( 'Error connecting to Critical.net' );
				}
			}
		} catch ( Exception $e ) {
			// Silently ignore errors - the end user doesn't need to see an error.
			$e->getMessage();
		}
	}
}
