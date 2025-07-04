<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Handle with fire hooks
 * 
 * @since 1.3.0
 * @package MeuMouse.com
 */
class Hooks {

    /**
     * Construct function
     * 
     * @since 1.3.0
     * @return void
     */
    public function __construct() {
        // Listen for cart changes for clear cart id reference
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Lost', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'clear_active_cart' ) );
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Recovered', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'clear_active_cart' ) );

        // delete coupon on expiration
        add_action( 'fcrc_delete_coupon_on_expiration', array( 'MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Coupons', 'delete_coupon_on_expiration' ), 10, 1 );

        // cancel follow up events
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Lost_Manually', array( __CLASS__, 'cancel_follow_up_events' ), 10, 1 );
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Recovered_Manually', array( __CLASS__, 'cancel_follow_up_events' ), 10, 1 );
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Deleted_Manually', array( __CLASS__, 'cancel_follow_up_events' ), 10, 1 );

        // delete anonymous carts
        add_action( 'fcrc_delete_old_anonymous_carts', array( $this, 'delete_old_anonymous_carts' ) );

        if ( ! wp_next_scheduled('fcrc_delete_old_anonymous_carts') ) {
            wp_schedule_event( time(), 'hourly', 'fcrc_delete_old_anonymous_carts' );
        }
    }


    /**
     * Cancels scheduled follow-up events for a given cart ID
     *
     * @since 1.0.0
     * @version 1.1.0
     * @param int $cart_id | The cart ID
     * @return void
     */
    public static function cancel_follow_up_events( $cart_id ) {
        $cron = _get_cron_array(); // Get all scheduled events
    
        if ( empty( $cron ) ) {
            return;
        }
    
        foreach ( $cron as $timestamp => $hooks ) {
            if ( isset( $hooks['fcrc_send_follow_up_message'] ) ) {
                foreach ( $hooks['fcrc_send_follow_up_message'] as $key => $event ) {
                    if ( isset( $event['args']['cart_id'] ) && intval( $event['args']['cart_id'] ) === intval( $cart_id ) ) {
                        wp_unschedule_event( $timestamp, 'fcrc_send_follow_up_message', $event['args'] );
    
                        if ( $this->debug_mode ) {
                            error_log( "Removed fcrc_send_follow_up_message event for cart_id {$cart_id}" );
                        }
                    }
                }
            }
    
            if ( isset( $hooks['fcrc_check_final_cart_status'] ) ) {
                foreach ( $hooks['fcrc_check_final_cart_status'] as $key => $event ) {
                    if ( isset( $event['args']['cart_id'] ) && intval( $event['args']['cart_id'] ) === intval( $cart_id ) ) {
                        wp_unschedule_event( $timestamp, 'fcrc_check_final_cart_status', $event['args'] );
    
                        if ( $this->debug_mode ) {
                            error_log( "Removed fcrc_check_final_cart_status event for cart_id {$cart_id}" );
                        }
                    }
                }
            }
        }
    }


    /**
     * Deletes carts without contact info older than 1 hour
     *
     * @since 1.2.0
     * @return void
     */
    public function delete_old_anonymous_carts() {
        $one_hour_ago = time() - HOUR_IN_SECONDS;

        $query = new \WP_Query( array(
            'post_type' => 'fc-recovery-carts',
            'post_status' => array( 'shopping', 'abandoned' ),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_fcrc_cart_updated_time',
                    'value' => $one_hour_ago,
                    'compare' => '<',
                    'type' => 'NUMERIC',
                ),
                array(
                    'relation' => 'AND',
                    array(
                        'key' => '_fcrc_cart_phone',
                        'value' => '',
                        'compare' => '=',
                    ),
                    array(
                        'key' => '_fcrc_cart_email',
                        'value' => '',
                        'compare' => '=',
                    ),
                ),
            ),
        ) );

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post_id ) {
                wp_delete_post( $post_id, true );

                if ( defined('$this->debug_mode') && $this->debug_mode ) {
                    error_log( '[FCRC] Cart deleted for missing contact: ' . $post_id );
                }

                /**
                 * Trigger deletion hook
                 * 
                 * @since 1.2.0
                 * @param int $post_id | The cart ID
                 */
                do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Deleted_Manually', $post_id );
            }
        }
    }
}