<?php

namespace WCProductsCompare;

class Storage {
	private const cookieName = WCProductsCompare_PLUGIN_KEY;

	/**
	 * Get all product ids
	 *
	 * @return array
	 */
	public static function getAll(): array {
		$value      = $_COOKIE[ self::cookieName ] ?? '';
		$productIDs = json_decode( $value, true );
		$productIDs = is_array( $productIDs ) ? $productIDs : [];

		return array_map( 'intval', $productIDs );
	}

	/**
	 * Get product ids count
	 *
	 * @return int
	 */
	public static function count(): int {
		return count( self::getAll() );
	}

	/**
	 * Check exists product id
	 *
	 * @param  int  $productID  Product id
	 *
	 * @return bool Product id exists status
	 */
	public static function exists( $productID ): bool {
		$productIDs = self::getAll();

		return in_array( $productID, $productIDs, true );
	}

	/**
	 * Update (add/remove) item in storage
	 *
	 * @param  int  $productID  Product id
	 * @param  int  $max  Max items
	 *
	 * @return array Return status and count of items
	 */
	public static function update( $productID, $max = 2 ): array {
		$productIDs = self::getAll();
		$count      = count( $productIDs );
		$status     = 'added';

		if ( ( $key = array_search( $productID, $productIDs, true ) ) !== false ) {
			unset( $productIDs[ $key ] );
			$productIDs = array_values( $productIDs );
			$status     = 'removed';
			$count --;
		} else {
			if ( $count >= $max ) {
				return [ 'status' => 'max_exceeded', 'count' => $count ];
			}

			$productIDs[] = $productID;
			$count ++;
		}

		$productIDs = json_encode( $productIDs );
		$expire     = current_time( 'timestamp' ) + HOUR_IN_SECONDS;
		Helper::setCookie( self::cookieName, $productIDs, $expire );

		return [ 'status' => $status, 'count' => $count ];
	}
}