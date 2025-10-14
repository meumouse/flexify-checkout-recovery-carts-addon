<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Cron;

use WP_CLI;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Registers WP-CLI commands to execute the PHP scheduler.
 *
 * @since 1.3.2
 */
class WP_CLI_Command {

    /**
     * Construct function
     * 
     * @since 1.3.2
     * @return void
     */
    public function __construct() {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            WP_CLI::add_command( 'fcrc scheduler', array( $this, 'run_scheduler' ) );
        }
    }

    /**
     * Execute pending jobs using the configured scheduler.
     *
     * @param array $args      Positional arguments.
     * @param array $assoc_args Associative arguments.
     */
    public function run_scheduler( $args, $assoc_args ) {
        if ( ! Scheduler_Manager::is_php_cron_enabled() ) {
            WP_CLI::warning( 'PHP Cron is not enabled in the plugin settings.' );
            return;
        }

        $scheduler = Scheduler_Manager::build_scheduler();

        $scheduler->call( function() {
            Queue_Processor::dispatch_due_events();
        } )->everyMinute();

        $scheduler->call( function() {
            do_action( 'fcrc_delete_old_anonymous_carts' );
        } )->hourly();

        $scheduler->run();

        WP_CLI::success( 'Scheduler executed successfully.' );
    }
}