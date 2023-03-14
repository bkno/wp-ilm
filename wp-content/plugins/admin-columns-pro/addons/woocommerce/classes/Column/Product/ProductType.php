<?php

namespace ACA\WC\Column\Product;

use AC;
use ACA\WC\Editing;
use ACA\WC\Sorting;
use ACP;

/**
 * @since 1.1
 */
class ProductType extends AC\Column
	implements ACP\Sorting\Sortable, ACP\Filtering\Filterable, ACP\Editing\Editable, ACP\ConditionalFormat\Formattable {

	use ACP\ConditionalFormat\ConditionalFormatTrait;

	public function __construct() {
		$this->set_type( 'column-wc-product_type' )
		     ->set_label( __( 'Type', 'codepress-admin-columns' ) )
		     ->set_group( 'woocommerce' );
	}

	private function get_simple_product_types() {
		return (array) apply_filters( 'acp/wc/editing/simple_product_types', [ 'simple', 'subscription' ] );
	}

	/**
	 * @param string $product_type
	 *
	 * @return bool
	 */
	private function is_simple_product_type( $product_type ) {
		return in_array( $product_type, $this->get_simple_product_types() );
	}

	public function get_value( $post_id ) {
		$product = wc_get_product( $post_id );
		$product_type = $product->get_type();

		$value = $this->get_product_type_label( $product_type );

		if ( $this->is_simple_product_type( $product_type ) ) {
			$additional = [];

			if ( $product->is_downloadable() ) {
				$additional[] = __( 'Downloadable', 'woocommerce' );
			}

			if ( $product->is_virtual() ) {
				$additional[] = __( 'Virtual', 'woocommerce' );
			}

			if ( $additional ) {
				$value .= sprintf( ' (%s)', implode( ' &amp; ', $additional ) );
			}
		}

		return $value;
	}

	private function get_product_type_label( $product_type ) {
		$types = wc_get_product_types();

		if ( ! isset( $types[ $product_type ] ) ) {
			return false;
		}

		return $types[ $product_type ];
	}

	public function get_raw_value( $post_id ) {
		return wc_get_product( $post_id )->get_type();
	}

	public function sorting() {
		return new Sorting\Product\ProductType();
	}

	public function filtering() {
		return new ACP\Filtering\Model\Delegated( $this, 'dropdown_product_type' );
	}

	public function editing() {
		return new Editing\Product\Type( $this->get_simple_product_types() );
	}

}