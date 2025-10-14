<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Cron;

use GO\Scheduler as GoScheduler;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once FC_RECOVERY_CARTS_INC . 'Cron/Scheduler_Fallback.php';

/**
 * Centralises the interaction with the selected task scheduler.
 *
 * @since 1.3.2
 * @package MeuMouse.com
 */
class Scheduler_Manager {
    
    public const TYPE_WP_CRON  = 'wp_cron';
    public const TYPE_PHP_CRON = 'php_cron';

    /**
     * Retrieve the configured scheduler.
     */
    public static function get_scheduler_type() {
        $type = Admin::get_setting( 'task_scheduler' );

        if ( ! in_array( $type, array( self::TYPE_PHP_CRON, self::TYPE_WP_CRON ), true ) ) {
            $type = self::TYPE_WP_CRON;
        }

        return $type;
    }

    /**
     * Whether PHP Cron is enabled.
     */
    public static function is_php_cron_enabled() {
        return self::TYPE_PHP_CRON === self::get_scheduler_type();
    }

    /**
     * Whether WP Cron is enabled.
     */
    public static function is_wp_cron_enabled() {
        return self::TYPE_WP_CRON === self::get_scheduler_type();
    }

    /**
     * Schedule a single event.
     *
     * @param int    $timestamp Unix timestamp.
     * @param string $hook      Action hook name.
     * @param array  $args      Arguments passed to the action hook.
     * @param array  $meta      Additional meta saved on the queue post.
     * @return int  Post ID of the queue entry.
     */
    public static function schedule_single_event( $timestamp, $hook, $args = array(), $meta = array() ) {
        $timestamp = absint( $timestamp );

        if ( $timestamp <= 0 ) {
            return 0;
        }

        $args = is_array( $args ) ? $args : array();
        $args = Helpers::sanitize_array( $args );

        $meta = is_array( $meta ) ? $meta : array();

        $args_hash = md5( wp_json_encode( array( 'hook' => $hook, 'args' => $args ) ) );
        $existing  = self::find_existing_event( $hook, $args_hash );

        if ( $existing ) {
            $post_id = $existing->ID;
        } else {
            $post_id = wp_insert_post( array(
                'post_type'   => 'fcrc-cron-event',
                'post_status' => 'publish',
                'post_title'  => sanitize_text_field( $hook ),
            ) );
        }

        if ( ! $post_id || is_wp_error( $post_id ) ) {
            return 0;
        }

        $queue_args             = $args;
        $queue_args['cron_post_id'] = $post_id;

        $meta_input = array_merge(
            array(
                '_fcrc_cron_event_key'   => sanitize_text_field( $hook ),
                '_fcrc_cron_scheduled_at' => $timestamp,
                '_fcrc_cron_args'        => $queue_args,
                '_fcrc_cron_args_hash'   => $args_hash,
            ),
            $meta
        );

        if ( isset( $queue_args['cart_id'] ) ) {
            $meta_input['_fcrc_cart_id'] = absint( $queue_args['cart_id'] );
        }

        foreach ( $meta_input as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }

        if ( self::is_wp_cron_enabled() ) {
            if ( ! wp_next_scheduled( $hook, $queue_args ) ) {
                wp_schedule_single_event( $timestamp, $hook, $queue_args );
            }
        }

        return $post_id;
    }

    /**
     * Remove scheduled event and queue entry.
     *
     * @param string $hook Action hook name.
     * @param array  $args Hook args used when scheduling.
     */
    public static function unschedule_event( $hook, $args ) {
        $args = is_array( $args ) ? $args : array();

        if ( self::is_wp_cron_enabled() ) {
            $timestamp = wp_next_scheduled( $hook, $args );

            if ( $timestamp ) {
                wp_unschedule_event( $timestamp, $hook, $args );
            }
        }

        $hash     = md5( wp_json_encode( array( 'hook' => $hook, 'args' => $args ) ) );
        $existing = self::find_existing_event( $hook, $hash );

        if ( $existing ) {
            wp_delete_post( $existing->ID, true );
        }
    }

    /**
     * Locate an existing queue post.
     */
    protected static function find_existing_event( $hook, $hash ) {
        $query = get_posts( array(
            'post_type'      => 'fcrc-cron-event',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'   => '_fcrc_cron_event_key',
                    'value' => sanitize_text_field( $hook ),
                ),
                array(
                    'key'   => '_fcrc_cron_args_hash',
                    'value' => sanitize_text_field( $hash ),
                ),
            ),
            'orderby' => 'ID',
            'order'   => 'ASC',
        ) );

        return ! empty( $query ) ? $query[0] : null;
    }

    /**
     * Helper to build a scheduler instance (real or stub).
     */
    public static function build_scheduler() {
        $scheduler = new GoScheduler();

        $wp_timezone = wp_timezone_string();

        if ( $wp_timezone ) {
            $scheduler->setTimeZone( $wp_timezone );
        }

        return $scheduler;
    }
}