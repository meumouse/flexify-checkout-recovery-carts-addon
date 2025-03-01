<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Manages cart recovery events, such as event triggering and information storage
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Recovery_Handler {

    /**
     * Construct function
     *
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        // get products details on add to cart
        add_action( 'woocommerce_add_to_cart', array( $this, 'handle_add_to_cart' ), 10, 6 );
    }


    /**
     * Get product details on add to cart hook
     * 
     * @since 1.0.0
     * @param string $cart_id | ID of the item in the cart
     * @param integer $product_id | ID of the product added to the cart
     * @param integer $request_quantity | Quantity of the item added to the cart
     * @param integer $variation_id | Variation ID of the product added to the cart
     * @param array $variation | Array of variation data
     * @param array $cart_item_data | Array of other cart item data
     *
     * @return void
     */
    public function handle_add_to_cart( $cart_id, $product_id, $request_quantity, $variation_id, $variation, $cart_item_data ) {
        
    }
}