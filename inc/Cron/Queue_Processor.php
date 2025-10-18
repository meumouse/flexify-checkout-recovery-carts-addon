<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Cron;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Processes queued cron events when PHP Cron is enabled.
 *
 * @since 1.3.2
 * @package MeuMouse.com
 */
class Queue_Processor {

    /**
     * Run all due events in the queue
     * 
     * @since 1.3.2
     * @return void
     */
    public static function dispatch_due_events() {
        if ( ! Scheduler_Manager::is_php_cron_enabled() ) {
            return;
        }

        $now = current_time('timestamp', true);

        $events = get_posts( array(
            'post_type'      => 'fcrc-cron-event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
            'meta_key'       => '_fcrc_cron_scheduled_at',
            'meta_query'     => array(
                array(
                    'key'     => '_fcrc_cron_scheduled_at',
                    'value'   => $now,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ),
            ),
        ) );

        if ( empty( $events ) ) {
            return;
        }

        foreach ( $events as $event ) {
            $hook = get_post_meta( $event->ID, '_fcrc_cron_event_key', true );

            if ( ! $hook ) {
                wp_delete_post( $event->ID, true );
                continue;
            }

            $args = get_post_meta( $event->ID, '_fcrc_cron_args', true );

            if ( ! is_array( $args ) ) {
                $args = array();
            }

            $args['cron_post_id'] = $event->ID;

            // Prevent concurrent execution by marking as draft before running.
            wp_update_post( array(
                'ID'          => $event->ID,
                'post_status' => 'draft',
            ) );

            do_action_ref_array( $hook, array_values( $args ) );

            if ( get_post_status( $event->ID ) !== 'trash' ) {
                wp_delete_post( $event->ID, true );
            }
        }
    }
}