<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Handles cart recovery events, such as tracking and updating cart data
 *
 * @since 1.0.0
 * @version 1.0.1
 * @package MeuMouse.com
 */
class Cart_Events {

    /**
     * Constructor function
     *
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        // Listen to cart changes
        add_action( 'woocommerce_add_to_cart', array( $this, 'update_cart_post' ), 10, 6 );
        add_action( 'woocommerce_cart_item_removed', array( $this, 'update_cart_post_on_change' ), 10, 2 );
        add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_cart_post_on_change' ), 10, 2 );
        add_action( 'woocommerce_cart_emptied', array( $this, 'handle_cart_emptied' ) );

        // update last modified cart time
        add_action( 'woocommerce_cart_updated', array( $this, 'update_last_modified_cart_time' ) );
    }


    /**
     * Updates the cart post when a product is added
     *
     * @since 1.0.0
     * @version 1.0.1
     * @param string  $cart_id          The cart item key
     * @param integer $product_id       The ID of the product added to the cart
     * @param integer $request_quantity The quantity of the item added to the cart
     * @param integer $variation_id     The variation ID (if applicable)
     * @param array   $variation        The variation data
     * @param array   $cart_item_data   Additional cart item data
     *
     * @return void
     */
    public function update_cart_post( $cart_id, $product_id, $request_quantity, $variation_id, $variation, $cart_item_data ) {
        // Check if we're in recovery mode
        if ( WC()->session->get('fcrc_cart_recovery_mode') ) {
            WC()->session->__unset('fcrc_cart_recovery_mode'); // Clear recovery mode flag
            
            return;
        }

        if ( function_exists('WC') && WC()->session instanceof WC_Session ) {
            $cart_id = WC()->session->get('fcrc_cart_id') ?: ( $_COOKIE['fcrc_cart_id'] ?? null );
        } else {
            $cart_id = $_COOKIE['fcrc_cart_id'] ?? null;
        }

        if ( ! $cart_id ) {
            // Create new cart post
            $cart_id = wp_insert_post( array(
                'post_type' => 'fc-recovery-carts',
                'post_status' => 'shopping',
                'post_title' => sprintf( __( 'Novo carrinho - %s', 'fc-recovery-carts' ), current_time('mysql') ),
                'meta_input' => array(
                    '_fcrc_cart_items' => array(),
                    '_fcrc_cart_total' => 0,
                    '_fcrc_cart_updated_time' => time(),
                    '_fcrc_abandoned_time' => '',
                ),
            ));

            // Store in session
            WC()->session->set( 'fcrc_cart_id', $cart_id );

            // Store in cookie
            setcookie( 'fcrc_cart_id', $cart_id, time() + ( 7 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN ); // Expira em 7 dias

            if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                error_log( "New cart created: " . $cart_id );
            }
        }

        self::sync_cart_with_post();
    }


    /**
     * Updates the cart post when an item is removed or its quantity is updated.
     *
     * @since 1.0.0
     * @param string $cart_item_key | The cart item key
     * @param array  $cart | The cart object
     *
     * @return void
     */
    public function update_cart_post_on_change( $cart_item_key, $cart ) {
        self::sync_cart_with_post();
    }
    

    /**
     * Handles the cart being emptied and resets the cart post.
     *
     * @since 1.0.0
     * @version 1.0.1
     * @return void
     */
    public function handle_cart_emptied() {
        if ( function_exists('WC') && WC()->session instanceof WC_Session ) {
            $cart_id = WC()->session->get('fcrc_cart_id') ?: ( $_COOKIE['fcrc_cart_id'] ?? null );
        } else {
            $cart_id = $_COOKIE['fcrc_cart_id'] ?? null;
        }

        if ( ! $recovery_cart_id ) {
            return;
        }

        // Update cart post status and clear items
        update_post_meta( $recovery_cart_id, '_fcrc_cart_items', array() );
        update_post_meta( $recovery_cart_id, '_fcrc_cart_total', 0 );

        // Change cart status to "abandoned"
        wp_update_post( array(
            'ID' => $recovery_cart_id,
            'post_status' => 'abandoned',
        ));
    }


    /**
     * Synchronizes WooCommerce cart data with the recovery cart post
     *
     * @since 1.0.0
     * @version 1.0.1
     * @param string $cart_id | The cart ID
     * @return void
     */
    public static function sync_cart_with_post( $cart_id = null ) {

        if ( ! empty( $cart_id ) ) {
            $recovery_cart_id = $cart_id;
        } else {
            if ( function_exists('WC') && WC()->session instanceof WC_Session ) {
                $cart_id = WC()->session->get('fcrc_cart_id') ?: ( $_COOKIE['fcrc_cart_id'] ?? null );
            } else {
                $cart_id = $_COOKIE['fcrc_cart_id'] ?? null;
            }
        }

        if ( ! $recovery_cart_id ) {
            return;
        }

        // Get WooCommerce cart contents
        $cart_items_data = WC()->cart->get_cart();
        $cart_items = array();
        $cart_total = 0;

        foreach ( $cart_items_data as $cart_item_key => $cart_item ) {
            $product = wc_get_product( $cart_item['product_id'] );

            if ( ! $product ) {
                continue;
            }

            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            $price = floatval( $product->get_price() );
            $total_price = $quantity * $price;
            $cart_total += $total_price;

            $cart_items[$product_id] = array(
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total_price,
                'name' => $product->get_name(),
                'image' => get_the_post_thumbnail_url( $product_id, 'thumbnail' ),
            );
        }

        // Update cart post metadata
        update_post_meta( $recovery_cart_id, '_fcrc_cart_items', $cart_items );
        update_post_meta( $recovery_cart_id, '_fcrc_cart_total', $cart_total );
        update_post_meta( $recovery_cart_id, '_fcrc_cart_updated_time', time() );

        // Set status to "shopping"
        wp_update_post( array(
            'ID' => $recovery_cart_id,
            'post_status' => 'shopping',
        ));
    }


    /**
     * Updates the cart's last modified time when it's updated
     *
     * @since 1.0.0
     * @version 1.0.1
     * @return void
     */
    public function update_last_modified_cart_time() {
        if ( function_exists('WC') && WC()->session instanceof WC_Session ) {
            $cart_id = WC()->session->get('fcrc_cart_id') ?: ( $_COOKIE['fcrc_cart_id'] ?? null );
        } else {
            $cart_id = $_COOKIE['fcrc_cart_id'] ?? null;
        }

        if ( ! $cart_id ) {
            return;
        }

        update_post_meta( $cart_id, '_fcrc_cart_updated_time', time() );
    }
}