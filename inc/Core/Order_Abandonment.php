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

        $cart_id = WC()->session->get('fcrc_cart_id');

        if ( ! $cart_id ) {
            return;
        }

        // Mark order as abandoned
        update_post_meta( $cart_id, '_fcrc_abandoned_time', current_time('mysql') );

        wp_update_post( array(
            'ID' => $cart_id,
            'post_status' => 'abandoned',
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
     * Saves the cart ID to the order meta when a new order is created
     *
     * @since 1.0.0
     * @param int $order_id | The order ID
     * @return void
     */
    public function save_cart_id_to_order_meta( $order_id ) {
        if ( ! $order_id ) {
            return;
        }

        // try to get the cart_id from the session or cookie
        $cart_id = WC()->session->get('fcrc_cart_id') ?: ( $_COOKIE['fcrc_cart_id'] ?? null );

        if ( $cart_id ) {
            update_post_meta( $order_id, '_fcrc_cart_id', $cart_id );

            if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                error_log( "Cart ID {$cart_id} saved to order {$order_id}" );
            }
        }
    }

}
