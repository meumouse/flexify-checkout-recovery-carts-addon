<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Views;

use WP_List_Table;

// Exit if accessed directly.
defined('ABSPATH') || exit;

if ( ! class_exists('WP_List_Table') ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Queue Cron vvents table class
 *
 * @since 1.3.0
 * @package MeuMouse.com
 */
class Queue_Table extends WP_List_Table {

    /**
     * Constructor
     * 
     * @since 1.3.0
     * @return void
     */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'Cron Event', 'fc-recovery-carts' ),
            'plural' => __( 'Cron Events', 'fc-recovery-carts' ),
            'ajax' => false,
        ));
    }


    /**
     * Display the table page
     * 
     * @since 1.3.0
     * @return void
     */
    public function display_page() {
        echo '<div class="wrap"><h1 class="wp-heading-inline">' . __( 'Gerenciar fila de processamentos', 'fc-recovery-carts' ) . '</h1>';
       
        echo '<form method="post">';
            echo '<input type="hidden" name="page" value="' . esc_attr( $_REQUEST['page'] ?? '' ) . '" />';
            echo '<input type="hidden" name="post_status" value="' . esc_attr( $_REQUEST['post_status'] ?? '' ) . '" />';

            $this->search_box( __( 'Buscar eventos', 'fc-recovery-carts' ), 'fcrc_cart_search' );
            $this->display();
        echo '</form></div>';
    }


    /**
     * Render the table
     * 
     * @since 1.3.0
     * @return void
     */
    public function display() {
        parent::display();
    }


    /**
     * Get columns
     *
     * @since 1.3.0
     * @return array
     */
    public function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => __( 'ID', 'fc-recovery-carts' ),
            'event_name' => __( 'Evento', 'fc-recovery-carts' ),
            'scheduled_at' => __( 'Data e horário do evento', 'fc-recovery-carts' ),
        );
    }


    /**
     * Render checkbox column
     * 
     * @since 1.3.0
     * @param object $item | Cron event data
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="event_ids[]" value="%d" />', absint( $item->ID ) );
    }


    /**
     * Render ID column
     * 
     * @since 1.3.0
     * @param object $item | Cron event data
     * @return string
     */
    public function column_id( $item ) {
        return sprintf( '#%d', absint( $item->ID ) );
    }


    /**
     * Render event name column
     * 
     * @since 1.3.0
     * @param object $item | Cron event data
     * @return string
     */
    public function column_event_name( $item ) {
        $key = get_post_meta( $item->ID, '_fcrc_cron_event_key', true );

        // Map keys to labels
        $map = array(
            'fcrc_send_follow_up_message'  => __( 'Follow up', 'fc-recovery-carts' ),
            'fcrc_check_final_cart_status' => __( 'Aguardando pagamento', 'fc-recovery-carts' ),
        );

        return esc_html( $map[ $key ] ?? $key );
    }


    /**
     * Render scheduled_at column
     * 
     * @since 1.3.0
     * @param object $item | Cron event data
     * @return string
     */
    public function column_scheduled_at( $item ) {
        $timestamp = get_post_meta( $item->ID, '_fcrc_cron_scheduled_at', true );

        return $timestamp ? esc_html( date_i18n( get_option('date_format') . ' ' . get_option('time_format'), strtotime( $timestamp ) ) ) : '&mdash;';
    }
    

    /**
     * Prepare items for display
     * 
     * @since 1.3.0
     * @return void
     */
    public function prepare_items() {
        $this->process_bulk_action();
        $per_page = $this->get_items_per_page( 'cron_events_per_page', 20 );
        $current_page = $this->get_pagenum();

        // Query cron-event posts
        $args = array(
            'post_type' => 'fcrc-cron-event',
            'posts_per_page' => $per_page,
            'paged' => $current_page,
            'post_status' => 'publish',
        );

        $query = new \WP_Query( $args );

        $this->items = $query->posts;
        $this->_column_headers = array( $this->get_columns(), array(), array() );

        $this->set_pagination_args( array(
            'total_items' => $query->found_posts,
            'per_page' => $per_page,
            'total_pages' => $query->max_num_pages,
        ));
    }


    /**
     * Define bulk actions available in the table
     * 
     * @since 1.3.0
     * @return array
     */
    public function get_bulk_actions() {
        return array(
            'delete' => __('Excluir', 'fc-recovery-carts'),
        );
    }


    /**
     * Process bulk actions
     * 
     * @since 1.3.0
     * @return void
     */
    public function process_bulk_action() {
        if ( isset( $_POST['cart_ids'] ) && is_array( $_POST['cart_ids'] ) ) {
            $cart_ids = array_map( 'intval', $_POST['cart_ids'] );

            // check current bulk action
            switch ( $this->current_action() ) {
                case 'delete':
                    foreach ( $cart_ids as $cart_id ) {
                        wp_delete_post( $cart_id, true );
                    }

                    break;
                case 'mark_lost':
                    foreach ( $cart_ids as $cart_id ) {
                        wp_update_post( array(
                            'ID' => $cart_id,
                            'post_status' => 'lost',
                        ));

                        /**
                         * Fire a hook when an order is considered lost manually
                         *
                         * @since 1.1.0
                         * @param int $cart_id | The abandoned cart ID
                         */
                        do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Lost_Manually', $cart_id );
                    }

                    break;
                case 'mark_abandoned':
                    foreach ( $cart_ids as $cart_id ) {
                        wp_update_post( array(
                            'ID' => $cart_id,
                            'post_status' => 'abandoned',
                        ));

                        /**
                         * Fire hook when cart is abandoned manually
                         * 
                         * @since 1.1.0
                         * @param int $cart_id | Cart ID | Post ID
                         */
                        do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Abandoned_Manually', $cart_id );
                    }

                    break;
                case 'mark_recovered':
                    foreach ( $cart_ids as $cart_id ) {
                        wp_update_post( array(
                            'ID' => $cart_id,
                            'post_status' => 'recovered',
                        ));

                        /**
                         * Fire a hook when a cart is recovered manually
                         *
                         * @since 1.0.0
                         * @param int $cart_id | The cart ID
                         */
                        do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Recovered_Manually', $cart_id );
                    }
                    
                    break;
            }
        }
    }
}