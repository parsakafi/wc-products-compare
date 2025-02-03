<?php
/**
 * Plugin Name:             WC Products Compare
 * Description:             WooCommerce Products Compare
 * Author:                  Parsa Kafi
 * Author URI:              http://parsa.ws
 * Version:                 1.0
 * Text Domain:             wc-products-compare
 * Domain Path:             /languages
 * Requires Plugins:        woocommerce
 * Requires at least:       6.7
 * Requires PHP:            8.2
 */

namespace WCProductsCompare;

defined( 'ABSPATH' ) || die();

class WCProductsCompare {
	public function __construct() {
		$this->define();
		$this->include();
		$this->instance();
	}

	/**
	 * Define constant
	 *
	 * @return void
	 */
	private function define(): void {
		define( 'WCProductsCompare_PLUGIN_KEY', 'wc_products_compare' );
		define( 'WCProductsCompare_PLUGIN_FILE_PATH', __FILE__ );
		define( 'WCProductsCompare_PLUGIN_PATH', __DIR__ );
		define( 'WCProductsCompare_MAX_COMPARE_ITEM', 2 );

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$pluginData = get_plugin_data( WCProductsCompare_PLUGIN_FILE_PATH );
		$version    = $pluginData['Version'];

		define( 'WCProductsCompare_PLUGIN_VERSION', $version );
	}

	/**
	 * Instant classes
	 *
	 * @return void
	 */
	private function instance(): void {
		new Settings();
		new General();
		new Compare();
	}

	/**
	 * Include required files
	 *
	 * @return void
	 */
	private function include(): void {
		require_once __DIR__ . '/inc/Helper.php';
		require_once __DIR__ . '/inc/General.php';
		require_once __DIR__ . '/inc/Settings.php';
		require_once __DIR__ . '/inc/Storage.php';
		require_once __DIR__ . '/inc/Compare.php';
	}
}

new WCProductsCompare();