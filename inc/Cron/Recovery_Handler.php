<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Cron;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Placeholders;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Hooks;

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
        add_action( 'fcrc_send_follow_up_message', array( $this, 'send_follow_up_message_callback' ), 10, 3 );

        // Hook to handle the scheduled final cart status check
        add_action( 'fcrc_check_final_cart_status', array( $this, 'check_final_cart_status_callback' ), 10, 2 );
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
                $event_delay = time() + $delay;

                $post_id = wp_insert_post( array(
                    'post_type' => 'fcrc-cron-event',
                    'post_title' => 'fcrc_send_follow_up_message',
                    'post_status' => 'publish',
                    'meta_input' => array(
                        '_fcrc_cron_event_key' => 'fcrc_send_follow_up_message',
                        '_fcrc_cron_scheduled_at'  => date_i18n( 'Y-m-d H:i:s', $event_delay ),
                    ),
                ));

                $args = array(
                    'cart_id' => $cart_id,
                    'event_key' => $event_key,
                    'cron_post_id' => $post_id,
                );
    
                // prevents scheduling the same event if already scheduled
                if ( ! wp_next_scheduled( 'fcrc_send_follow_up_message', $args ) ) {
                    wp_schedule_single_event( $event_delay, 'fcrc_send_follow_up_message', $args );
                }
    
                if ( $delay > $max_delay ) {
                    $max_delay = $delay;
                }
            }
        }
    
        // final schedule event, also with verification
        if ( $max_delay > 0 ) {
            $delay = $max_delay + Helpers::convert_to_seconds( 1, 'hours' );
            $event_key = 'fcrc_check_final_cart_status';
            $event_delay = time() + $delay;

            // create queue process post
            $post_id = wp_insert_post( array(
                'post_type' => 'fcrc-cron-event',
                'post_title' => $event_key,
                'post_status' => 'publish',
                'meta_input' => array(
                    '_fcrc_cron_event_key' => $event_key,
                    '_fcrc_cron_scheduled_at' => date_i18n( 'Y-m-d H:i:s', $event_delay ),
                ),
            ));

            $args = array(
                'cart_id' => $cart_id,
                'cron_post_id' => $post_id,
            );
    
            if ( ! wp_next_scheduled( $event_key, $args ) ) {
                wp_schedule_single_event( $event_delay, $event_key, $args );
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
     * @param int $cron_post_id | The cron post ID
     * @return void
     */
    public function send_follow_up_message_callback( $cart_id, $event_key, $cron_post_id ) {
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

        // save notification data
        $sent_channels = array();

        // send via WhatsApp if enabled
        if ( isset( $event['channels']['whatsapp'] ) && $event['channels']['whatsapp'] === 'yes' ) {
            // send message
            self::send_whatsapp_message( $receiver, $message );

            $sent_channels[] = 'whatsapp';
        }

        if ( empty( $sent_channels ) ) {
            return;
        }

        // get current history
        $notifications = get_post_meta( $cart_id, '_fcrc_notifications_sent', true );

        if ( ! is_array( $notifications ) ) {
            $notifications = array();
        }

        // add new notification data
        foreach ( $sent_channels as $channel ) {
            $notifications[] = array(
                'event_key' => sanitize_key( $event_key ),
                'channel' => sanitize_key( $channel ),
                'sent_at' => current_time('mysql'),
            );
        }

        // save notifications
        update_post_meta( $cart_id, '_fcrc_notifications_sent', $notifications );

        if ( $cron_post_id ) {
            wp_delete_post( intval( $cron_post_id ), true );
        }
    }


    /**
     * Checks the final status of the abandoned cart after the last follow-up.
     *
     * @since 1.0.0
     * @version 1.1.0
     * @param int $cart_id The abandoned cart ID
     * @param int $cron_post_id | The cron post ID
     * @return void
     */
    public function check_final_cart_status_callback( $cart_id, $cron_post_id  ) {
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

        if ( $cron_post_id ) {
            wp_delete_post( intval( $cron_post_id ), true );
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
     * Detect if user is restoring an abandoned cart
     *
     * @since 1.0.0
     * @version 1.3.0
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
        Hooks::cancel_scheduled_cart_process( $cart_id );
    
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
}