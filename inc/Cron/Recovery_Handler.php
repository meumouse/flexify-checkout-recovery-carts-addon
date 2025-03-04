<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Placeholders;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Handle Cron jobs
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
        // add new schedule interval
        add_filter( 'cron_schedules', array( $this, 'custom_schedule_interval' ), 10, 1 );

        // schedule cron job
        add_action( 'wp', array( $this, 'fcrc_schedule_abandoned_cart_checker' ) );

        // check for abandoned carts
        add_action( 'fcrc_check_abandoned_carts', array( $this, 'check_abandoned_carts' ) );

        // start recovery carts
        add_action( 'Flexify_Checkout/Recovery_Carts/Cart_Abandoned', array( $this, 'recovery_carts' ), 10, 1 );

        // Hook into WordPress to check for cart recovery link on page load
        add_action( 'template_redirect', array( __CLASS__, 'maybe_restore_cart' ) );
    }


    /**
     * Add new schedule interval
     * 
     * @since 1.0.0
     * @param array $schedules | Current schedules
     * @return array
     */
    public function custom_schedule_interval( $schedules ) {
        $schedules['every_30_minutes'] = array(
            'interval' => 1800,
            'display' => __('A cada 30 minutos', 'fc-recovery-carts'),
        );

        return $schedules;
    }


    /**
     * Schedules a cron job to check for abandoned carts every hour
     *
     * @since 1.0.0
     * @return void
     */
    public function fcrc_schedule_abandoned_cart_checker() {
        if ( ! wp_next_scheduled('fcrc_check_abandoned_carts') ) {
            wp_schedule_event( time(), 'every_30_minutes', 'fcrc_check_abandoned_carts' );
        }
    }


    /**
     * Checks if carts have been abandoned based on the configured time.
     *
     * @since 1.0.0
     * @return void
     */
    public function fcrc_check_abandoned_carts() {
        $time_limit_seconds = Helpers::get_abandonment_time_seconds();

        // Get all shopping carts that haven't been updated recently
        $args = array(
            'post_type' => 'fc-recovery-carts',
            'post_status' => 'shopping',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fcrc_cart_updated_time',
                    'value' => time() - $time_limit_seconds,
                    'compare' => '<',
                    'type' => 'NUMERIC',
                ),
            ),
        );

        $query = new \WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $cart_id = get_the_ID();

                // Mark as abandoned and store the abandonment time
                update_post_meta( $cart_id, '_fcrc_abandoned_time', current_time('mysql') );

                wp_update_post( array(
                    'ID'=> $cart_id,
                    'post_status' => 'abandoned',
                ));

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
     * @param int $cart_id The abandoned cart ID
     * @return void
     */
    public function recovery_carts( $cart_id ) {
        $settings = Admin::get_setting('follow_up_events');

        if ( ! $settings || ! is_array( $settings ) ) {
            return;
        }

        foreach ( $settings as $event_key => $event_data ) {
            $delay = Helpers::convert_to_seconds( $event_data['delay_time'], $event_data['delay_type'] );

            if ( $delay) {
                wp_schedule_single_event( time() + $delay, "send_follow_up_message", ['cart_id' => $cart_id, 'event_key' => $event_key] );
            }
        }
    }


    /**
     * Sends a follow-up message based on the event
     *
     * @since 1.0.0
     * @param int $cart_id The abandoned cart ID
     * @param string $event_key The follow-up event key
     */
    public function send_follow_up_message( $cart_id, $event_key ) {
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

        $replacement = array(
            '{{ first_name }}' => $first_name,
            '{{ last_name }}' => $last_name,
            '{{ recovery_link }}' => Helpers::generate_recovery_cart_link( $cart_id ),
            '{{ coupon_code }}' => $event['coupon'] ?? '',
        );

        // Replace placeholders in the message
        $message = Placeholders::replace_placeholders( $event['message'], $replacement );

        if ( $event['channels']['whatsapp'] === 'yes' ) {
            self::send_whatsapp_message( $phone, $message );
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
}