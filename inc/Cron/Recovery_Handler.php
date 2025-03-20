<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Cron;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Coupons;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Placeholders;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Handle Cron jobs
 * 
 * @since 1.0.0
 * @version 1.0.1
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
        // check for abandoned carts
        add_action( 'init', array( $this, 'check_abandoned_carts' ) );

        // check if cart is resumed from user - after woocommerce loaded
        add_action( 'woocommerce_loaded', array( $this, 'detect_cart_recovery' ) );

        // start recovery carts
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Abandoned', array( $this, 'recovery_carts' ), 10, 1 );

        // Hook into WordPress to check for cart recovery link on page load
        add_action( 'template_redirect', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'maybe_restore_cart' ) );

        // Hook to handle the scheduled follow-up messages
        add_action( 'fcrc_send_follow_up_message', array( $this, 'send_follow_up_message_callback' ), 10, 2 );

        // Hook to handle the scheduled final cart status check
        add_action( 'check_final_cart_status', array( $this, 'check_final_cart_status_callback' ), 10, 1 );

        // Listen for cart changes for clear cart id reference
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Lost', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'clear_cart_id_reference' ) );
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Recovered_Manually', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'clear_cart_id_reference' ) );
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Recovered', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'clear_cart_id_reference' ) );
        add_action( 'Flexify_Checkout/Recovery_Carts/Order_Abandoned', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'clear_cart_id_reference' ) );

        // update coupon expiration
        add_action( 'fcrc_update_coupon_expiration', array( 'MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Coupons', 'update_coupon_expiration' ), 10, 1 );
    }


    /**
     * Checks for abandoned carts by verifying last ping time
     *
     * @since 1.0.0
     * @return void
     */
    public function check_abandoned_carts() {
        $time_limit_seconds = Helpers::get_abandonment_time_seconds();

        $query = new \WP_Query( array(
            'post_type' => 'fc-recovery-carts',
            'post_status' => array('shopping', 'abandoned'),
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fcrc_cart_last_ping',
                    'value' => time() - $time_limit_seconds,
                    'compare' => '<',
                    'type' => 'NUMERIC',
                ),
            ),
        ));

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $cart_id = get_the_ID();
                $current_status = get_post_status( $cart_id );

                // skip to the next cart if the status is already "abandoned", "lost" or "recovered"
                if ( in_array( $current_status, array( 'abandoned', 'lost', 'recovered' ) ) ) {
                    continue;
                }

                // update abandoned time 
                update_post_meta( $cart_id, '_fcrc_abandoned_time', current_time('mysql') );

                wp_update_post( array(
                    'ID' => $cart_id,
                    'post_status' => 'abandoned',
                ));

                if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                    error_log( 'Abandoned cart: ' . $cart_id );
                }

                /**
                 * Fire hook when cart is abandoned
                 * 
                 * @since 1.0.0
                 * @param int $cart_id | Cart ID | Post ID
                 */
                do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Abandoned', $cart_id );
            }
        }

        wp_reset_postdata();
    }


    /**
     * Schedules follow-up messages based on admin settings
     *
     * @since 1.0.0
     * @version 1.1.0
     * @param int $cart_id | The abandoned cart ID
     * @return void
     */
    public function recovery_carts( $cart_id ) {
        $follow_up_events = Admin::get_setting('follow_up_events');
    
        if ( ! $follow_up_events || ! is_array( $follow_up_events ) ) {
            return;
        }
    
        // allowed times
        $start_time = Admin::get_setting('follow_up_time_interval_start');
        $end_time = Admin::get_setting('follow_up_time_interval_end');
    
        // convert to timestamp of the current day
        $current_time = current_time('timestamp');
        $start_time_ts = strtotime( date( 'Y-m-d', $current_time ) . ' ' . $start_time );
        $end_time_ts = strtotime( date( 'Y-m-d', $current_time ) . ' ' . $end_time );
    
        // if the end time is smaller than the start time, it means the interval crosses midnight
        if ( $end_time_ts < $start_time_ts ) {
            $end_time_ts = strtotime( '+1 day', $end_time_ts );
        }
    
        $max_delay = 0;
    
        foreach ( $follow_up_events as $event_key => $event_data ) {
            $delay = Helpers::convert_to_seconds( $event_data['delay_time'], $event_data['delay_type'] );
    
            if ( $delay ) {
                $event_time = $current_time + $delay;
    
                // check if the event is within the allowed time interval
                if ( $event_time < $start_time_ts ) {
                    // if the event time is before the allowed interval, reschedule it for the start of the period
                    $event_time = $start_time_ts;
                } elseif ( $event_time > $end_time_ts ) {
                    // if the event time is after the allowed interval, reschedule it the start of the next allowed period

                    $event_time = strtotime( '+1 day', $start_time_ts );
                }
    
                wp_schedule_single_event( $event_time, "fcrc_send_follow_up_message", array( 'cart_id' => $cart_id, 'event_key' => $event_key ) );
    
                // update the maximum delay found
                if ( $event_time - $current_time > $max_delay ) {
                    $max_delay = $event_time - $current_time;
                }
            }
        }
    
        // if has follow up scheduled, schedule the final check
        if ( $max_delay > 0 ) {
            $final_check_delay = $max_delay + Helpers::convert_to_seconds( 1, 'hours' );
            $final_check_time = $current_time + $final_check_delay;
    
            // ensures that the final check is within the allowed time range
            if ( $final_check_time < $start_time_ts ) {
                $final_check_time = $start_time_ts;
            } elseif ( $final_check_time > $end_time_ts ) {
                $final_check_time = strtotime( '+1 day', $start_time_ts );
            }
    
            wp_schedule_single_event( $final_check_time, "check_final_cart_status", array( 'cart_id' => $cart_id ) );
        }
    }


    /**
     * Sends a follow-up message based on the event
     *
     * @since 1.0.0
     * @param int $cart_id | The abandoned cart ID
     * @param string $event_key | The follow-up event key
     */
    public function send_follow_up_message_callback( $cart_id, $event_key ) {
        $settings = Admin::get_setting('follow_up_events');

        if ( ! isset( $settings[$event_key] ) ) {
            return;
        }

        $event = $settings[$event_key];
        $cart_data = get_post_meta( $cart_id );
        $first_name_fallback = Admin::get_setting('fallback_first_name');
        $first_name = $cart_data['_fcrc_first_name'][0] ?? $first_name_fallback;
        $last_name = $cart_data['_fcrc_last_name'][0] ?? '';
        $phone = $cart_data['_fcrc_cart_phone'][0] ?? '';

        // get coupon code
        if ( $event['coupon']['generate_coupon'] === 'yes' ) {
            Coupons::generate_wc_coupon( $event['coupon'], $cart_id );
            
            $coupon_code = get_post_meta( $cart_id, '_fcrc_coupon_code', true );
        } else {
            $coupon_code = $event['coupon']['coupon_code'];
        }

        $replacement = array(
            '{{ first_name }}' => $first_name,
            '{{ last_name }}' => $last_name,
            '{{ recovery_link }}' => Helpers::generate_recovery_cart_link( $cart_id ),
            '{{ coupon_code }}' => $coupon_code ?? '',
        );

        // Replace placeholders in the message
        $message = Placeholders::replace_placeholders( $event['message'], $replacement );
        $receiver = function_exists('joinotify_prepare_receiver') ? joinotify_prepare_receiver( $phone ) : $phone;

        if ( $event['channels']['whatsapp'] === 'yes' ) {
            self::send_whatsapp_message( $receiver, $message );
        }
    }


    /**
     * Checks the final status of the abandoned cart after the last follow-up.
     *
     * @since 1.0.0
     * @version 1.1.0
     * @param int $cart_id The abandoned cart ID
     * @return void
     */
    public function check_final_cart_status_callback( $cart_id ) {
        $cart_status = get_post_status( $cart_id );

        // if still abandoned after 1 hour, mark as "lost"
        if ( $cart_status === 'abandoned' ) {
            wp_update_post( array(
                'ID' => $cart_id,
                'post_status' => 'lost',
            ));

            if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                error_log( 'Cart marked as lost: ' . $cart_id );
            }

            /**
             * Fire a hook when an order is considered lost
             *
             * @since 1.0.0
             * @param int $cart_id | The abandoned cart ID
             */
            do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Lost', $cart_id );
        }
    }


    /**
     * Sends a WhatsApp message with Joinotify
     *
     * @since 1.0.0
     * @param string $receiver | The recipient's phone number
     * @param string $message | The message to send
     */
    public static function send_whatsapp_message( $receiver, $message ) {
        if ( function_exists('joinotify_send_whatsapp_message_text') ) {
            $sender = Admin::get_setting('joinotify_sender_phone');

            joinotify_send_whatsapp_message_text( $sender, $receiver, $message );
        }
    }


    /**
     * Cancels scheduled follow-up events for a given cart ID
     *
     * @since 1.0.0
     * @param int $cart_id | The cart ID
     * @return void
     */
    public function cancel_follow_up_events( $cart_id ) {
        $follow_up_events = Admin::get_setting('follow_up_events');

        if ( ! $follow_up_events || ! is_array( $follow_up_events ) ) {
            return;
        }

        foreach ( $follow_up_events as $event_key => $event_data ) {
            wp_clear_scheduled_hook( 'fcrc_send_follow_up_message', array( 'cart_id' => $cart_id, 'event_key' => $event_key ) );
        }

        if ( FC_RECOVERY_CARTS_DEV_MODE ) {
            error_log( 'Follow-up events canceled for cart: ' . $cart_id );
        }
    }


    /**
     * Detect if user is restoring an abandoned cart
     *
     * @since 1.0.0
     * @version 1.0.1
     * @return void
     */
    public function detect_cart_recovery() {
        if ( is_admin() || wp_doing_ajax() ) {
            return;
        }

        // get the cart ID from the session or cookie
        if ( function_exists('WC') && WC()->session instanceof WC_Session ) {
            $cart_id = WC()->session->get('fcrc_cart_id') ?: ( $_COOKIE['fcrc_cart_id'] ?? null );
        } else {
            $cart_id = $_COOKIE['fcrc_cart_id'] ?? null;
        }

        if ( ! $cart_id ) {
            return;
        }

        // check if the cart is still marked as abandoned
        $cart_status = get_post_status( $cart_id );

        if ( $cart_status !== 'abandoned' ) {
            return;
        }

        // get current products on cart
        $current_cart_items = WC()->cart->get_cart();

        // get saved cart items on abandoned cart
        $saved_cart_items = get_post_meta( $cart_id, '_fcrc_cart_items', true );
        $saved_cart_items = is_array( $saved_cart_items ) ? $saved_cart_items : array();

        // check if the current cart items match the saved cart items
        if ( ! empty( $current_cart_items ) && ! empty( $saved_cart_items ) ) {
            $is_cart_recovered = $this->compare_cart_items( $current_cart_items, $saved_cart_items );

            if ( $is_cart_recovered ) {
                wp_update_post( array(
                    'ID' => $cart_id,
                    'post_status' => 'shopping',
                ));

                // remove the abandoned time meta
                delete_post_meta( $cart_id, '_fcrc_abandoned_time' );

                // cancel follow-up events
                $this->cancel_follow_up_events( $cart_id );

                if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                    error_log( 'Cart resumed: ' . $cart_id );
                }
            }
        }
    }
    

    /**
     * Compare current cart items with abandoned cart items
     *
     * @since 1.0.0
     * @param array $current_cart_items | The current WooCommerce cart items
     * @param array $saved_cart_items | The saved abandoned cart items
     * @return bool
     */
    private function compare_cart_items( $current_cart_items, $saved_cart_items ) {
        $current_cart = array_map( function( $item ) {
            return array(
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            );
        }, $current_cart_items );

        $saved_cart = array_map( function( $item ) {
            return array(
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            );
        }, $saved_cart_items );

        return $current_cart === $saved_cart;
    }
}