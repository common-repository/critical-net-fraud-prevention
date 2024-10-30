<?php
/**
 * Display Critical.net admin page.
 *
 * @package critical-net-fraud-prevention
 */

namespace CNFP_Includes\Pages;

use CNFP_Includes\Helpers\ApiWrapper;

/**
 * Admin class for displaying Critical.net admin page.
 */
class Admin {

	/**
	 * Register the class.
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		add_action( 'admin_init', array( $this, 'register_settings_fields' ) );
		add_action( 'plugins_loaded', array( $this, 'handle_post' ) );
	}

	/**
	 * Add the admin pages to the admin menu.
	 */
	public function add_admin_pages() {
		$page = array(
			'page_title' => 'CNFP Settings',
			'menu_title' => 'CNFP Settings',
			'capability' => 'manage_options',
			'menu_slug'  => 'cnfp_plugin',
			'callback'   => array( $this, 'admin_index' ),
			'icon_url'   => '',
			'position'   => null,
		);
		add_menu_page( $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'], $page['icon_url'], $page['position'] );
	}

	/**
	 * Callbackfor the admin menu.
	 */
	public function admin_index() {
		// require template.
		require_once CRITICAL_PLUGIN_PATH . 'templates/admin-settings.php';
	}

	/**
	 * Save the license key if it is valid.
	 *
	 * @param string $key The license key to save.
	 */
	public function save_licence_key( $key ) {
		try {
			$endpoint = 'check-licence-key';
			$data     = array(
				'licence_key' => $key,
			);

			$api_response = json_decode( ApiWrapper::post( $endpoint, $data ), true );

			$data = json_decode( $api_response['data'], true );
			if ( 200 === $api_response['code'] ) {
				if ( true === $data['status'] ) {
					update_option( 'cnfp_licence_key', $key );
					$_SESSION['cnfp_licence_key_saved'] = 'success';
					$_SESSION['cnfp_session_message']   = $data['message'];
				} else {
					$_SESSION['cnfp_licence_key_saved'] = 'error';
					$_SESSION['cnfp_session_message']   = $data['message'];
				}
			} else {
				$_SESSION['cnfp_licence_key_saved'] = 'error';
				$_SESSION['cnfp_session_message']   = 'Something went wrong, Please contact critical.net support.';
			}
		} catch ( Exception $e ) {
			// Silently ignore errors - the end user doesn't need to see an error.
			$e->getMessage();
		}
	}

	/**
	 * Register the settings with the Settings API.
	 */
	public function register_settings_fields() {
		$setting = array(
			'option_group' => 'cnfp_option_group',
			'option_name'  => 'cnfp_option_name',
			'callback'     => array( $this, 'cnfp_option_group' ),
		);

		$section = array(
			'id'    => 'cnfp_settings',
			'title' => 'CNFP Settings',
			'page'  => 'cnfp_plugin',
		);

		$field = array(
			'id'         => 'cnfp_licence_key',
			'title'      => 'CNFP Licence Key',
			'callback'   => array( $this, 'cnfp_licence_field' ),
			'page'       => 'cnfp_plugin',
			'section_id' => 'cnfp_settings',
			'args'       => array(
				'label_for' => 'cnfp_licence_key',
				'class'     => 'licence_key',
			),
		);

		// Register setting.
		register_setting( $setting['option_group'], $setting['option_name'], ( isset( $setting['callback'] ) ? $setting['callback'] : array() ) );

		// Add settings section.
		add_settings_section( $section['id'], $section['title'], ( isset( $section['callback'] ) ? $section['callback'] : array() ), $section['page'] );

		// Add settiongs field.
		add_settings_field( $field['id'], $field['title'], ( isset( $field['callback'] ) ? $field['callback'] : array() ), $field['page'], $field['section_id'], ( isset( $section['args'] ) ? $section['args'] : array() ) );
	}

	/**
	 * Critical.net settings option group callback.
	 *
	 * @param array $input The settings section.
	 * return array The unmodified settings section.
	 */
	public function cnfp_option_group( $input ) {
		return $input;
	}

	/**
	 * Critical.net license key settings field callback.
	 */
	public function cnfp_licence_field() {
		echo '<input class="regular-text" type="text" name="cnfp_licence_key" placeholder="Enter Licence key" value="' . esc_attr( get_option( 'cnfp_licence_key' ) ) . '">';
	}

	/**
	 * Handle form post.
	 */
	public function handle_post() {
		if ( isset( $_POST['cnfp_licence_key'], $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'set-license-key' ) ) {
			$this->save_licence_key( sanitize_text_field( wp_unslash( $_POST['cnfp_licence_key'] ) ) );
		}
	}
}
