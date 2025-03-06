<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Handles order abandonment tracking based on payment method delay
 *
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Order_Abandonment {

    /**
     * Construct function
     *
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        // schedule event for order abandonment check
        add_action( 'woocommerce_checkout_order_processed', array( $this, 'schedule_order_abandonment_check' ), 10, 3 );

        // check if order still unpaid
        add_action( 'fcrc_check_order_payment_status', array( $this, 'check_order_payment_status' ), 10, 1 );

        // save cart id on order meta data
        add_action( 'woocommerce_new_order', array( $this, 'save_cart_id_to_order_meta' ), 10, 1 );

        // Listen for order status changes
        add_action( 'woocommerce_order_status_changed', array( $this, 'mark_cart_as_recovered' ), 10, 3 );
    }


    /**
     * Schedules an event to check if the order is paid within the configured delay
     *
     * @since 1.0.0
     * @param int $order_id | The order ID
     * @param array $posted_data | The posted data from checkout
     * @param object $order | The WooCommerce order object
     * @return void
     */
    public function schedule_order_abandonment_check( $order_id, $posted_data, $order ) {
        if ( ! $order_id ) {
            return;
        }

        $payment_method = $order->get_payment_method();
        $payment_methods = Admin::get_setting('payment_methods');

        // Get delay time based on the payment method
        if ( isset( $payment_methods[$payment_method] ) ) {
            $delay_time = $payment_methods[$payment_method]['delay_time'];
            $delay_unit = $payment_methods[$payment_method]['delay_unit'];
        } else {
            // Default delay if payment method is not found
            $delay_time = 5;
            $delay_unit = 'minutes';
        }

        // Convert delay time to seconds
        $delay_seconds = Helpers::convert_to_seconds( $delay_time, $delay_unit );

        // Schedule a task to check order payment status after the delay
        if ( ! wp_next_scheduled( 'fcrc_check_order_payment_status', array( $order_id) ) ) {
            wp_schedule_single_event( time() + $delay_seconds, 'fcrc_check_order_payment_status', array( $order_id ) );
        }
    }


    /**
     * Checks if an order is still unpaid after the delay and marks it as abandoned
     *
     * @since 1.0.0
     * @param int $order_id | The order ID
     * @return void
     */
    public function check_order_payment_status( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        // If order is already paid, exit early
        if ( $order->is_paid() ) {
            return;
        }

        $cart_id = get_post_meta( $order_id, '_fcrc_cart_id', true );

        if ( ! $cart_id ) {
            return;
        }

        // Mark order as abandoned
        update_post_meta( $cart_id, '_fcrc_abandoned_time', current_time('mysql') );

        wp_update_post( array(
            'ID' => $cart_id,
            'post_status' => 'order_abandoned',
        ));

        /**
         * Fire a hook when an order is considered abandoned
         *
         * @since 1.0.0
         * @param int $order_id | The abandoned order ID
         * @param int $cart_id | The abandoned cart ID
         */
        do_action( 'Flexify_Checkout/Recovery_Carts/Order_Abandoned', $order_id, $cart_id );
    }


    /**
     * Saves the cart ID, products, and billing details to the order meta when a new order is created
     *
     * @since 1.0.0
     * @param int $order_id | The order ID
     * @return void
     */
    public function save_cart_id_to_order_meta( $order_id ) {
        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        // Try to get the cart ID from the session or cookie
        $cart_id = WC()->session->get('fcrc_cart_id') ?: ( $_COOKIE['fcrc_cart_id'] ?? null );

        if ( ! $cart_id ) {
            return;
        }

        // Retrieve stored cart meta
        $stored_meta = get_post_meta( $cart_id );

        // Get billing information from the order
        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name = $order->get_billing_last_name();
        $billing_full_name = sprintf( '%s %s', $billing_first_name, $billing_last_name );
        $billing_phone = $order->get_billing_phone();
        $billing_email = $order->get_billing_email();
        $order_total = $order->get_total();
        $user_id = $order->get_user_id();

        // Prepare updated meta data
        $updated_meta = array(
            '_fcrc_first_name' => $billing_first_name,
            '_fcrc_last_name' => $billing_last_name,
            '_fcrc_full_name' => $billing_full_name,
            '_fcrc_cart_phone' => $billing_phone,
            '_fcrc_cart_email' => $billing_email,
            '_fcrc_cart_total' => $order_total,
            '_fcrc_user_id' => $user_id ?: 0,
        );

        // Compare existing meta and update only if different
        foreach ( $updated_meta as $meta_key => $new_value ) {
            $stored_value = isset( $stored_meta[$meta_key][0] ) ? $stored_meta[$meta_key][0] : null;

            if ( $stored_value !== $new_value ) {
                update_post_meta( $cart_id, $meta_key, $new_value );
            }
        }

        // Retrieve order items
        $order_items = array();
        foreach ( $order->get_items() as $item_id => $item ) {
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            $price = floatval( $item->get_total() ) / $quantity;

            $order_items[$product_id] = array(
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $item->get_total(),
                'name' => $item->get_name(),
                'image' => get_the_post_thumbnail_url( $product_id, 'thumbnail' ),
            );
        }

        // Compare and update cart items only if changed
        $stored_cart_items = get_post_meta( $cart_id, '_fcrc_cart_items', true ) ?: array();
        
        if ( json_encode( $stored_cart_items ) !== json_encode( $order_items ) ) {
            update_post_meta( $cart_id, '_fcrc_cart_items', $order_items );
        }

        // Mark cart as purchased
        update_post_meta( $cart_id, '_fcrc_purchased', true );
        update_post_meta( $order_id, '_fcrc_cart_id', $cart_id );

        if ( FC_RECOVERY_CARTS_DEV_MODE ) {
            error_log( "Cart ID {$cart_id} updated with billing info for order {$order_id}" );
        }

        /**
         * Fire a hook when a cart is recovered
         *
         * @since 1.0.0
         * @param int $cart_id | The cart ID
         * @param int $order_id | The WooCommerce order ID
         */
        do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Recovered', $cart_id, $order_id );
    }


    /**
     * Marks the cart as recovered when the order is paid (processing or completed)
     *
     * @since 1.0.0
     * @param int $order_id | The WooCommerce order ID
     * @param string $old_status | The previous order status
     * @param string $new_status | The new order status
     * @return void
     */
    public function mark_cart_as_recovered( $order_id, $old_status, $new_status ) {
        if ( ! in_array( $new_status, array( 'processing', 'completed' ) ) ) {
            return;
        }

        // Get the cart ID linked to this order
        $cart_id = get_post_meta( $order_id, '_fcrc_cart_id', true );

        if ( ! $cart_id ) {
            return;
        }

        // Update the cart status to recovered
        wp_update_post( array(
            'ID' => $cart_id,
            'post_status' => 'recovered',
        ));

        update_post_meta( $cart_id, '_fcrc_purchased', true );

        if ( FC_RECOVERY_CARTS_DEV_MODE ) {
            error_log( "Cart ID {$cart_id} linked to Order ID {$order_id} marked as recovered." );
        }

        /**
         * Fire a hook when a cart is recovered
         *
         * @since 1.0.0
         * @param int $cart_id | The cart ID
         * @param int $order_id | The WooCommerce order ID
         */
        do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Recovered', $cart_id, $order_id );
    }
}
