<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Cron;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Placeholders;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Handle Cron jobs
 * 
 * @since 1.0.0
 * @version 1.3.0
 * @package MeuMouse.com
 */
class Recovery_Handler {

    /**
     * Get debug mode
     * 
     * @since 1.3.0
     * @return bool
     */
    public $debug_mode = FC_RECOVERY_CARTS_DEBUG_MODE;
   
    /**
     * Construct function
     *
     * @since 1.0.0
     * @version 1.2.0
     * @return void
     */
    public function __construct() {
        // check for abandoned carts
        add_action( 'init', array( $this, 'check_abandoned_carts' ) );

        // start recovery carts
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Abandoned', array( $this, 'init_follow_up_events' ), 10, 1 );

        // Hook into WordPress to check for cart recovery link on page load
        add_action( 'template_redirect', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'maybe_restore_cart' ) );

        // Hook to handle the scheduled follow-up messages
        add_action( 'fcrc_send_follow_up_message', array( $this, 'send_follow_up_message_callback' ), 10, 2 );

        // Hook to handle the scheduled final cart status check
        add_action( 'fcrc_check_final_cart_status', array( $this, 'check_final_cart_status_callback' ), 10, 1 );

        // Listen for cart changes for clear cart id reference
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Lost', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'clear_active_cart' ) );
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Recovered', array( '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers', 'clear_active_cart' ) );
        
        // cancel follow up events
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Lost_Manually', array( $this, 'cancel_follow_up_events' ), 10, 1 );
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Recovered_Manually', array( $this, 'cancel_follow_up_events' ), 10, 1 );
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Deleted_Manually', array( $this, 'cancel_follow_up_events' ), 10, 1 );
        
        // delete coupon on expiration
        add_action( 'fcrc_delete_coupon_on_expiration', array( 'MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Coupons', 'delete_coupon_on_expiration' ), 10, 1 );

        // delete anonymous carts
        add_action( 'fcrc_delete_old_anonymous_carts', array( $this, 'delete_old_anonymous_carts' ) );

        if ( ! wp_next_scheduled('fcrc_delete_old_anonymous_carts') ) {
            wp_schedule_event( time(), 'hourly', 'fcrc_delete_old_anonymous_carts' );
        }
    }


    /**
     * Checks for abandoned carts by verifying last ping time
     *
     * @since 1.0.0
     * @version 1.2.0
     * @return void
     */
    public function check_abandoned_carts() {
        $time_limit_seconds = Helpers::get_abandonment_time_seconds();
        $time_threshold = time() - ( $time_limit_seconds + 30 ); // add 30 seconds to account for any time differences

        $query = new \WP_Query( array(
            'post_type' => 'fc-recovery-carts',
            'post_status' => array( 'shopping' ),
            'posts_per_page' => -1, // get all posts
            'meta_query' => array(
                array(
                    'key' => '_fcrc_cart_updated_time',
                    'value' => $time_threshold,
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

                if ( $current_status !== 'shopping' ) {
                    continue;
                }

                // Get the last ping time
                $last_ping = get_post_meta( $cart_id, '_fcrc_cart_updated_time', true );
                $last_ping = intval( $last_ping );

                // Check if cart should be marked as abandoned
                if ( empty( $last_ping ) || $last_ping < $time_threshold ) {
                    // Update abandoned time
                    update_post_meta( $cart_id, '_fcrc_abandoned_time', current_time('mysql') );

                    // Update status to abandoned
                    wp_update_post( array(
                        'ID' => $cart_id,
                        'post_status' => 'abandoned',
                    ));

                    if ( $this->debug_mode ) {
                        error_log( 'Abandoned cart: ' . $cart_id . ' | Last ping: ' . $last_ping );
                    }

                    /**
                     * Fire hook when a cart is abandoned
                     *
                     * @since 1.0.0
                     * @param int $cart_id
                     */
                    do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Abandoned', $cart_id );
                }
            }
        }

        wp_reset_postdata();
    }


    /**
     * Schedules follow-up messages based on admin settings
     *
     * @since 1.0.0
     * @version 1.3.0
     * @param int $cart_id | The abandoned cart ID
     * @return void
     */
    public function init_follow_up_events( $cart_id ) {
        $follow_up_events = Admin::get_setting('follow_up_events');
    
        // check if has follow up events
        if ( ! $follow_up_events || ! is_array( $follow_up_events ) ) {
            return;
        }

        $user_phone = get_post_meta( $cart_id, '_fcrc_cart_phone', true );
        $user_email = get_post_meta( $cart_id, '_fcrc_cart_email', true );

        // check if cart is from a guest
        if ( empty( $user_phone ) || empty( $user_email ) ) {
            return;
        }
    
        $max_delay = 0;
    
        // iterate for each follow up event
        foreach ( $follow_up_events as $event_key => $event_data ) {
            // check if follow up event is enabled
            if ( ! isset( $event_data['enabled'] ) || $event_data['enabled'] !== 'yes' ) {
                continue;
            }

            // get delay time for event
            $delay = Helpers::convert_to_seconds( $event_data['delay_time'], $event_data['delay_type'] );
    
            if ( $delay ) {
                $args = array( 'cart_id' => $cart_id, 'event_key' => $event_key );
    
                // prevents scheduling the same event if already scheduled
                if ( ! wp_next_scheduled( 'fcrc_send_follow_up_message', $args ) ) {
                    wp_schedule_single_event( time() + $delay, 'fcrc_send_follow_up_message', $args );
                }
    
                if ( $delay > $max_delay ) {
                    $max_delay = $delay;
                }
            }
        }
    
        // final schedule event, also with verification
        if ( $max_delay > 0 ) {
            $final_check_delay = $max_delay + Helpers::convert_to_seconds( 1, 'hours' );
            $final_check_args = array( 'cart_id' => $cart_id );
    
            if ( ! wp_next_scheduled( 'fcrc_check_final_cart_status', $final_check_args ) ) {
                wp_schedule_single_event( time() + $final_check_delay, 'fcrc_check_final_cart_status', $final_check_args );
            }
        }
    }


    /**
     * Sends a follow-up message based on the event
     *
     * @since 1.0.0
     * @version 1.3.0
     * @param int $cart_id | The abandoned cart ID
     * @param string $event_key | The follow-up event key
     */
    public function send_follow_up_message_callback( $cart_id, $event_key ) {
        $settings = Admin::get_setting('follow_up_events');

        if ( ! isset( $settings[ $event_key ] ) ) {
            return;
        }

        $event = $settings[ $event_key ];
        $cart_data = get_post_meta( $cart_id );
        $phone = $cart_data['_fcrc_cart_phone'][0] ?? '';

        // get message with placeholders replaced
        $message = Placeholders::replace_placeholders( $event['message'], $cart_id, $event );

        // get receiver phone number
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

            if ( $this->debug_mode ) {
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
     * Detect if user is restoring an abandoned cart
     *
     * @since 1.0.0
     * @version 1.2.0
     * @return void
     */
    public static function detect_cart_recovery() {
        if ( is_admin() ) {
            return;
        }
        
        $cart_id = Helpers::get_current_cart_id();
    
        if ( ! $cart_id ) {
            return;
        }
    
        // check current status
        $cart_status = get_post_status( $cart_id );
    
        // ignore already recovered or purchased carts
        if ( in_array( $cart_status, array( 'recovered', 'purchased' ), true ) ) {
            return;
        }
    
        // only proceed if status is abandoned
        if ( $cart_status !== 'abandoned' ) {
            return;
        }
    
        // check if there was recent activity
        $last_ping = (int) get_post_meta( $cart_id, '_fcrc_cart_updated_time', true );
        $time_limit_seconds = Helpers::get_abandonment_time_seconds();
        $time_threshold = time() - $time_limit_seconds;
    
        // if last ping is before the time threshold, it's considered abandoned
        if ( $last_ping < $time_threshold ) {
            return;
        }
    
        // cancel scheduled events
        self::cancel_follow_up_events( $cart_id );
    
        if ( $this->debug_mode ) {
            error_log( 'Cart resumed: ' . $cart_id );
        }

        // set flag for prevent duplicate carts
        WC()->session->set( 'fcrc_active_cart', true );
    
        // update status
        wp_update_post( array(
            'ID' => $cart_id,
            'post_status' => 'shopping',
        ));
    
        // clean up
        delete_post_meta( $cart_id, '_fcrc_abandoned_time' );
    }
    

    /**
     * Compare current cart items with abandoned cart items
     *
     * @since 1.0.0
     * @param array $current_cart_items | The current WooCommerce cart items
     * @param array $saved_cart_items | The saved abandoned cart items
     * @return bool
     */
    public static function compare_cart_items( $current_cart_items, $saved_cart_items ) {
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