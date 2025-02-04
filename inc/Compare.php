<?php

namespace WCProductsCompare;

use Automattic\WooCommerce\Utilities\I18nUtil;

class Compare {
	public function __construct() {
		add_action( 'woocommerce_after_shop_loop_item', [ $this, 'addButton' ], 9999 );
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'addButton' ], 9999 );
		add_action( 'wp_ajax_wcpc_add_remove', [ $this, 'addRemove' ] );
		add_action( 'wp_ajax_nopriv_wcpc_add_remove', [ $this, 'addRemove' ] );
		add_shortcode( 'wc_products_compare', [ $this, 'shortcode' ] );
	}

	/**
	 * Product compare shortcode
	 *
	 * @return false|string
	 */
	public function shortcode(): false|string {
		ob_start();

		$productIDs = Storage::getAll();

		if ( empty( $productIDs ) ) {
			wc_print_notice( __( 'Your product compare list is empty', 'wc-products-compare' ), 'error' );

		} else {
			$products = wc_get_products( array(
				'limit'   => WCProductsCompare_MAX_COMPARE_ITEM,
				'orderby' => 'date',
				'order'   => 'DESC',
				'include' => $productIDs
			) );

			if ( count( $products ) ) {
				$imageSize       = Settings::getOption( 'image_size', 'medium' );
				$addToCardButton = wc_string_to_bool( Settings::getOption( 'display_add_to_card_button', 'no' ) );
				$fields          = self::getProductFields();
				$attributes      = Helper::getWcAttributes();
				?>
                <div class="wcpc-wrapper">
					<?php
					/**
					 * \WC_Product $product
					 */

					$data = [ 'count' => count( $products ) ];

					foreach ( $products as $product ) {
						$productID = $product->get_id();
						$imageID   = (int) $product->get_image_id();

						$data['removeButton'][] = '<button type="button" class="button wcpc-button wcpc-button-remove" data-id="' . $productID . '" data-action="refresh"><svg width="28px" height="28px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 8L8 16M8.00001 8L16 16" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';

						$data['images'][] = $imageID ? wp_get_attachment_image( $imageID, $imageSize, false,
							[ 'class' => 'compare-image' ] ) : '';

						if ( ! $product->is_visible() ) {
							$data['title'][] = esc_html( $product->get_name() );
						} else {
							$data['title'][] = sprintf( '<a href="%s">%s</a>', esc_url( $product->get_permalink() ),
								esc_html( $product->get_name() ) );
						}

						if ( $addToCardButton ) {
							$button = '';
							if ( $product->is_purchasable() && $product->is_in_stock() ) {
								$button = Helper::getAddToCardButton( $product );
							}

							$data['addToCard'][] = $button;
						}

						foreach ( $fields as $key => $field ) {
							if ( wc_string_to_bool( Settings::getOption( 'display_field_' . $key ) ) ) {
								$value = false;

								if ( $key === 'brand' ) {
									$value = do_shortcode( '[product_brand post_id="' . $productID . '" class="test"]' );

								} elseif ( $key === 'dimensions' && $product->has_dimensions() ) {
									$value = preg_replace( '/ /', '', $product->get_dimensions(), 4 );

								} elseif ( $key === 'weight' && $product->has_weight() ) {
									$weight_unit_label = I18nUtil::get_weight_unit_label( get_option( 'woocommerce_weight_unit',
										'g' ) );
									$value             = $product->get_weight() . ' ' . $weight_unit_label;

								} elseif ( $key === 'stock' ) {
									$availability = $product->get_availability();
									$value        = sprintf( '<span class="%s">%s</span>',
										esc_attr( $availability['class'] ),
										$availability['availability'] ? esc_html( $availability['availability'] ) : esc_html__( 'In stock',
											'wc-products-compare' ) );

								} elseif ( $key === 'rating' && wc_review_ratings_enabled() ) {
									$value = wc_get_rating_html( $product->get_average_rating() );

								} elseif ( $key === 'price' && $price_html = $product->get_price_html() ) {
									$value = sprintf( '<span class="price">%s</span>', $price_html );
								}

								if ( $value === false ) {
									continue;
								}
								$data['fields'][ $key ]['label']   = $field;
								$data['fields'][ $key ]['value'][] = $value;
							}
						}

						foreach ( $attributes as $key => $attribute ) {
							if ( wc_string_to_bool( Settings::getOption( 'display_attribute_' . $key ) ) ) {
								$data['fields'][ $key ]['label']   = $attribute['label'];
								$data['fields'][ $key ]['value'][] = $product->get_attribute( $attribute['name'] );
							}
						}
					}

					// Head
					echo '<div class="compare-row compare-head">';
					foreach ( $data['title'] as $i => $title ) {
						echo '<div class="compare-col">';
						echo $data['removeButton'][ $i ];
						echo $data['images'][ $i ];
						echo $title;
						if ( ! empty( $data['addToCard'][ $i ] ) ) {
							echo '<div class="add-to-card-btns">' . $data['addToCard'][ $i ] . '</div>';
						}
						echo '</div>';
					}
					echo '</div>';

					// Fields
					foreach ( $data['fields'] as $key => $field ) {
						$value = array_filter( $field['value'] );
						if ( empty( $value ) ) {
							continue;
						}

						echo '<div class="compare-field-title">';
						echo $field['label'];
						echo '</div>';
						echo '<div class="compare-row compare-row-' . $key . '">';
						foreach ( $field['value'] as $i => $value ) {
							echo '<div class="compare-col">';
							echo empty( $value ) ? '---' : $value;
							echo '</div>';
						}
						echo '</div>';
					}
					?>
                </div>
				<?php

			} else {
				wc_print_notice( __( 'Your product compare list is not valid', 'wc-products-compare' ), 'error' );
			}
		}

		return ob_get_clean();
	}

	/**
	 * Add or remove product id from storage
	 *
	 * @return void
	 */
	public function addRemove(): void {
		$nonce     = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
		$productID = (int) $_POST['product_id'];

		if ( isset( $_POST['nonce'] ) &&
		     wp_verify_nonce( $nonce, WCProductsCompare_PLUGIN_KEY . current_time( 'd' ) ) ) {

			$update = Storage::update( $productID, WCProductsCompare_MAX_COMPARE_ITEM );

			$data = array(
				'status'   => $update['status'],
				'count'    => $update['count'],
				'redirect' => $update['count'] >= WCProductsCompare_MAX_COMPARE_ITEM ? get_permalink( Settings::getOption( 'compare_page' ) ) : ''
			);

			wp_send_json_success( $data );
		}

		wp_send_json_error( [
			'error'   => 'nonce-invalid',
			'message' => __( 'Security code is not valid, page will be refreshed.', 'wc-products-compare' ),
			'refresh' => true
		], 403 );
	}

	/**
	 * Print add to compare button
	 *
	 * @return void
	 */
	public function addButton(): void {
		$productID = get_the_ID();
		$exists    = Storage::exists( $productID );
		echo '<button type="button" class="button wcpc-button ' . ( $exists ? 'wcpc-button-remove' : '' ) . '" data-id="' . $productID . '" data-action="non">' . __( 'Compare',
				'wc-products-compare' ) . '</button>';
	}

	/**
	 * Get product fields
	 *
	 * @return array
	 */
	public static function getProductFields(): array {
		return array(
			'price'      => __( 'Price', 'wc-products-compare' ),
			'stock'      => __( 'Stock', 'wc-products-compare' ),
			'rating'     => __( 'Rating', 'wc-products-compare' ),
			'brand'      => __( 'Brand', 'wc-products-compare' ),
			'dimensions' => __( 'Dimensions', 'wc-products-compare' ),
			'weight'     => __( 'Weight', 'wc-products-compare' ),
		);
	}
}