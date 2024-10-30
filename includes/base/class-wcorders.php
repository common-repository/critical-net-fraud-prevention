<?php
/**
 * Handle WooCommerce orders page-related functionality.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes\Base;

use CNFP_Includes\Helpers\ApiWrapper;

defined( 'ABSPATH' ) || exit;

/**
 * Class for WooCoomerce orders page.
 */
class WCOrders {

	/**
	 * Register the class.
	 */
	public function register() {
		// Add Risk Score column to WC Orders page.
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'wc_add_risk_score_column' ), 20 );

		// Populate Risk Score column.
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'wc_add_risk_score_column_content' ), 20, 2 );
	}

	/**
	 * Add the risk score column to the WooCommerce orders page.
	 *
	 * @param string[] $columns The column header labels keyed by column ID.
	 * @return string[] The new column header labels keyed by column ID.
	 */
	public function wc_add_risk_score_column( $columns ) {
		$new_columns = array();

		foreach ( $columns as $idx => $value ) {
			$new_columns[ $idx ] = $value;
			if ( 'order_status' === $idx ) {
				$new_columns['critical_net_risk_score'] = 'Risk Score';
			}
		}

		return $new_columns;
	}

	/**
	 * Add the risk score column content to the given column.
	 *
	 * @param string $column The name of the column to get content for.
	 * @param int    $order_id The WooCommerce order ID.
	 */
	public function wc_add_risk_score_column_content( $column, $order_id ) {
		if ( 'critical_net_risk_score' === $column ) {
			$risk_score = get_post_meta( $order_id, '_critical_net_risk_score', true );

			$risk_score = ( '' === $risk_score ) ? '&ndash;' : $risk_score;

			$transaction_id = get_post_meta( $order_id, '_critical_net_transaction_id', true );

			$front_url = get_option( 'cnfp_front_url', 'https://bo.critical.net' );

			if ( is_numeric( $risk_score ) ) {
				if ( $risk_score < 70 ) {
					$class = 'approved';
					$text  = 'Approved';
				} elseif ( $risk_score < 90 ) {
					$class = 'review';
					$text  = 'Review';
				} else {
					$class = 'rejected';
					$text  = 'Not approved';
				}
			} else {
				$class = 'normal_order';
			}

			$content = "<span class='" . esc_html( $class ) . "'>";

			$content .= ! empty( $transaction_id ) ?
				(
					'<a href="' .
					esc_html( $front_url ) .
					'/#/merchant-transactions/' .
					esc_html( $transaction_id ) .
					'/view" target="_blank">' .
					'<span class="dashicons dashicons-star-filled"></span>' .
					'<span class="text">' .
					esc_html( $risk_score ) .
					"&nbsp;&nbsp;$text" .
					'</span></a>'
				) :
				esc_html( $risk_score );

			$content .= '</span>';

			echo wp_kses_post( $content );
		}
	}

	/**
	 * Populate previous risk scores and transaction IDs.
	 */
	public static function populate_meta_data() {
		if ( '' !== get_option( 'cnfp_licence_key' ) ) {
			$response = ApiWrapper::get( 'merchant-transactions' );

			if ( isset( $response ) ) {
				$api_response = json_decode( $response, true );

				if ( isset( $api_response['code'] ) && 200 === $api_response['code'] && isset( $api_response['data'] ) ) {
					$resp_data = json_decode( $api_response['data'], true );

					if ( $resp_data['status'] && isset( $resp_data['data'] ) ) {
						foreach ( $resp_data['data'] as $transaction ) {
							if ( isset( $transaction['model_id'] ) && isset( $transaction['type'] ) && 'WordPress' === $transaction['type'] ) {
								if ( isset( $transaction['score'] ) ) {
									// Add score.
									add_post_meta( $transaction['model_id'], '_critical_net_risk_score', $transaction['score'], true );
								}

								if ( isset( $transaction['id'] ) ) {
									// Add transaction ID.
									add_post_meta( $transaction['model_id'], '_critical_net_transaction_id', $transaction['id'], true );
								}
							}
						}
					}
				}
			}
		}
	}
}
