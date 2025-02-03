<?php

namespace WCProductsCompare;

class Helper {
	/**
	 * Get product add to card button
	 *
	 * @param $product
	 *
	 * @return string
	 */
	public static function getAddToCardButton( $product ): string {
		ob_start();
		$GLOBALS['product'] = $product;
		woocommerce_template_loop_add_to_cart();
		wc_setup_product_data( $GLOBALS['post'] );

		return ob_get_clean();
	}

	/**
	 * Set cookie
	 *
	 * @param  string  $name  Cookie name
	 * @param  string|numeric  $value  Cookie value
	 * @param  int  $expire  Expire time in seconds
	 *
	 * @return void
	 */
	public static function setCookie( $name, $value, $expire = 0 ): void {
		$options = array(
			'expires'  => $expire,
			'secure'   => is_ssl(),
			'path'     => COOKIEPATH ?: '/',
			'domain'   => COOKIE_DOMAIN,
			'httponly' => true,
		);

		/**
		 * Controls whether the cookie should only be accessible via the HTTP protocol, or if it should also be
		 * accessible to Javascript.
		 *
		 * @see   https://www.php.net/manual/en/function.setcookie.php
		 * @since 3.3.0
		 *
		 * @param  bool  $httponly  If the cookie should only be accessible via the HTTP protocol.
		 * @param  string  $name  Cookie name.
		 * @param  string  $value  Cookie value.
		 * @param  int  $expire  When the cookie should expire.
		 * @param  bool  $secure  If the cookie should only be served over HTTPS.
		 */
		setcookie( $name, $value, $options );
	}

	/**
	 * Get Woocommerce attributes
	 *
	 * @return array Woocommerce attributes
	 */
	public static function getWcAttributes(): array {
		$attributes   = wc_get_attribute_taxonomies();
		$wcAttributes = [];

		foreach ( $attributes as $attribute ) {
			$wcAttributes[ md5( $attribute->attribute_name ) ] = [
				'label' => $attribute->attribute_label,
				'name'  => $attribute->attribute_name
			];
		}

		return $wcAttributes;
	}

	/**
	 * Get WP page items
	 *
	 * @return array WP pages
	 */
	public static function getPages(): array {
		$args      = [
			'exclude'      => implode( ',', apply_filters( 'wp_list_pages_excludes', [] ) ),
			'hierarchical' => false
		];
		$pages     = get_pages( $args );
		$sitePages = [];

		foreach ( $pages as $page ) {
			$sitePages[ $page->ID ] = $page->post_title;
		}

		return $sitePages;
	}

	/**
	 *  Get WP image sizes
	 * https://developer.wordpress.org/reference/functions/get_intermediate_image_sizes/
	 *
	 * @return array WP image sizes
	 */
	public static function getImageSizes(): array {
		$sizes      = get_intermediate_image_sizes();
		$imageSizes = [];

		foreach ( $sizes as $value ) {
			$imageSizes[ $value ] = ucwords( str_replace( '_', ' ', $value ) );
		}

		return $imageSizes;
	}
}