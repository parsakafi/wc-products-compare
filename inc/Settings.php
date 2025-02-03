<?php

namespace WCProductsCompare;

defined( 'ABSPATH' ) || die();

class Settings {
	public function __construct() {
		add_filter( 'woocommerce_get_sections_products', [ $this, 'addSettingsTab' ] );
		add_filter( 'woocommerce_get_settings_products', [ $this, 'addSettings' ], 10, 2 );
		add_filter( 'plugin_action_links', [ $this, 'addSettingsLink' ], 0, 2 );
	}

	/**
	 * Add settings link in admin plugin page
	 *
	 * @param  array  $actions  Plugins actions
	 * @param  string  $pluginFile  Plugin file
	 *
	 * @return array
	 */
	public function addSettingsLink( $actions, $pluginFile ): array {
		if ( $pluginFile === 'wc-products-compare/WCProductsCompare.php' ) {
			$actions['settings'] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=' . WCProductsCompare_PLUGIN_KEY ) . '">' . __( 'Settings' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Add setting section tab
	 *
	 * @param  array  $sections  WC Sections
	 *
	 * @return array
	 */
	public function addSettingsTab( array $sections ): array {
		$sections[ WCProductsCompare_PLUGIN_KEY ] = __( 'Products Compare', 'wc-products-compare' );

		return $sections;
	}

	/**
	 * Add setting fields
	 *
	 * @param  array  $settings  Default settings
	 * @param  string  $currentSection  Current section name
	 *
	 * @return array
	 */
	public function addSettings( $settings, $currentSection ): array {
		if ( $currentSection === WCProductsCompare_PLUGIN_KEY ) {
			$pluginSettings = array();
			$imageSizes     = Helper::getImageSizes();
			$pages          = Helper::getPages();
			$attributes     = Helper::getWcAttributes();
			$fields         = Compare::getProductFields();

			$pluginSettings[] = array(
				'name' => __( 'WC Products Compare', 'wc-products-compare' ),
				'type' => 'title',
				'desc' => __( 'The following options are used to configure WC Products Compare',
					'wc-products-compare' ),
				'id'   => WCProductsCompare_PLUGIN_KEY
			);

			$pluginSettings[] = array(
				'name'     => __( 'Compare page', 'wc-products-compare' ),
				'type'     => 'select',
				'desc'     => __( 'Insert shortcode in the compare page',
						'wc-products-compare' ) . ': <code>[wc_products_compare]</code>',
				'desc_tip' => __( 'Select compare page', 'wc-products-compare' ),
				'id'       => WCProductsCompare_PLUGIN_KEY . '_compare_page',
				'options'  => $pages,
				'autoload' => false,
			);

			$pluginSettings[] = array(
				'name'     => __( 'Image Size', 'wc-products-compare' ),
				'type'     => 'select',
				'desc'     => __( 'Select product image size', 'wc-products-compare' ),
				'desc_tip' => true,
				'id'       => WCProductsCompare_PLUGIN_KEY . '_image_size',
				'options'  => $imageSizes,
				'autoload' => false,
			);

			$pluginSettings[] = array(
				'name'     => __( 'Display add to card button', 'wc-products-compare' ),
				'id'       => WCProductsCompare_PLUGIN_KEY . '_display_add_to_card_button',
				'type'     => 'checkbox',
				'desc'     => __( 'Active', 'wc-products-compare' ),
				'autoload' => false,
			);

			$fieldKeys     = array_keys( $fields );
			$fieldFirstKey = current( $fieldKeys );
			$fieldLastKey  = end( $fieldKeys );
			foreach ( $fields as $key => $field ) {
				$pluginSettings[] = array(
					'title'         => __( 'Select product fields', 'wc-products-compare' ),
					//'name'          => sprintf( __( 'Display %s attribute', 'wc-products-compare' ), $attribute ),
					'id'            => WCProductsCompare_PLUGIN_KEY . '_display_field_' . $key,
					'type'          => 'checkbox',
					'default'       => 'no',
					'desc'          => $field,
					'checkboxgroup' => $key === $fieldFirstKey ? 'start' : ( $key === $fieldLastKey ? 'end' : '' ),
					'autoload'      => false,
				);
			}


			if ( empty( $attributes ) ) {
				$attributes = array(
					0 => __( 'Your product attributes is empty, Add attribute in "Products > Attributes" menu',
						'wc-products-compare' ),
				);
			}
			$attributeKeys     = array_keys( $attributes );
			$attributeFirstKey = current( $attributeKeys );
			$attributeLastKey  = end( $attributeKeys );
			foreach ( $attributes as $key => $attribute ) {
				$pluginSettings[] = array(
					'title'         => __( 'Select product attributes', 'wc-products-compare' ),
					'id'            => WCProductsCompare_PLUGIN_KEY . '_display_attribute_' . $key,
					'type'          => 'checkbox',
					'default'       => 'no',
					'desc'          => $attribute['label'],
					'checkboxgroup' => $key === $attributeFirstKey ? 'start' : ( $key === $attributeLastKey ? 'end' : '' ),
					'autoload'      => false,
				);
			}

			$pluginSettings[] = array( 'type' => 'sectionend', 'id' => WCProductsCompare_PLUGIN_KEY );

			return $pluginSettings;
		}

		return $settings;
	}

	/**
	 * Get plugin settings
	 *
	 * @param  string  $key  Option key
	 * @param  mixed  $default  Default option value
	 *
	 * @return false|mixed|null
	 */
	public static function getOption( $key, $default = false ): mixed {
		return get_option( WCProductsCompare_PLUGIN_KEY . '_' . $key, $default );
	}
}