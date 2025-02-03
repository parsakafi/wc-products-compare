<?php

namespace WCProductsCompare;

class General {
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'loadPluginTextDomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	/**
	 * Enqueue style and script
	 *
	 * @return void
	 */
	public function enqueueScripts(): void {
		$pluginVersion = WCProductsCompare_PLUGIN_VERSION . ( defined( 'DEVELOPMENT_MODE' ) && DEVELOPMENT_MODE ? time() : '' );

		wp_enqueue_style( WCProductsCompare_PLUGIN_KEY . '-style',
			plugins_url( '/assets/style.min.css', WCProductsCompare_PLUGIN_FILE_PATH ),
			false, $pluginVersion );

		wp_enqueue_script( WCProductsCompare_PLUGIN_KEY . '-script',
			plugins_url( '/assets/script.js', WCProductsCompare_PLUGIN_FILE_PATH ),
			[ 'jquery' ], $pluginVersion, [ 'in_footer' => true ] );

		wp_localize_script( WCProductsCompare_PLUGIN_KEY . '-script', 'WCProductsCompare', array(
			'ajaxurl'            => admin_url( 'admin-ajax.php' ),
			'ajaxnonce'          => wp_create_nonce( WCProductsCompare_PLUGIN_KEY . current_time( 'd' ) ),
			'maxExceededMessage' => __( 'It is not possible to add more than %number% product to the comparison.',
				'wc-products-compare' ),
		) );
	}

	/**
	 * Load plugin text domain
	 *
	 * @return void
	 */
	public function loadPluginTextDomain(): void {
		$domain = 'wc-products-compare';
		if ( is_textdomain_loaded( $domain ) ) {
			return;
		}
		$moFile     = sprintf( '%s-%s.mo', $domain, get_locale() );
		$domainPath = path_join( WP_LANG_DIR, 'plugins' );
		$loaded     = load_textdomain( $domain, path_join( $domainPath, $moFile ) );

		if ( ! $loaded ) { //else, check the plugin language folder.
			$domainPath = path_join( WP_PLUGIN_DIR, "$domain/languages" );
			load_textdomain( $domain, path_join( $domainPath, $moFile ) );
		}
	}
}