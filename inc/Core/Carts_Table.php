<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use WP_List_Table;

// Exit if accessed directly.
defined('ABSPATH') || exit;

if ( ! class_exists('WP_List_Table') ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Carts table class
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Carts_Table extends WP_List_Table {

    /**
     * Construct function
     * 
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        parent::__construct( array(
            'singular' => __('Carrinho', 'fc-recovery-carts'),
            'plural' => __('Carrinhos', 'fc-recovery-carts'),
            'ajax' => false,
        ));
    }


    /**
     * Define table columns
     * 
     * @since 1.0.0
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'id'         => __('ID', 'fc-recovery-carts'),
            'contact'    => __('Contato', 'fc-recovery-carts'),
            'total'      => __('Valor do carrinho', 'fc-recovery-carts'),
            'abandoned'  => __('Data de abandono', 'fc-recovery-carts'),
            'actions'    => __('Ações', 'fc-recovery-carts'),
            'status'     => __('Status', 'fc-recovery-carts'),
        );

        return $columns;
    }


    /**
     * Render the checkbox column for bulk selection
     * 
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="cart_ids[]" value="%s" />', esc_attr( $item->ID ) );
    }


    /**
     * Render the ID column
     * 
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_id( $item ) {
        return sprintf( '#%s', esc_html( $item->ID ) );
    }


    /**
     * Render the contact column
     * 
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_contact( $item ) {
        $contact = get_post_meta( $item->ID, '_fc_cart_contact', true );

        return esc_html( $contact ? $contact : __('Não informado', 'fc-recovery-carts') );
    }


    /**
     * Render the cart total column
     * 
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_total( $item ) {
        $total = get_post_meta( $item->ID, '_fc_cart_total', true );

        return $total ? wc_price( $total ) : __('N/A', 'fc-recovery-carts');
    }


    /**
     * Render the abandoned date column
     * 
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_abandoned( $item ) {
        $date = get_the_date( 'd/m/Y H:i', $item->ID );

        return esc_html( $date );
    }


    /**
     * Render the status column
     * 
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_status( $item ) {
        $status = get_post_status( $item->ID );

        $statuses = array(
            'shipping' => __('Comprando', 'fc-recovery-carts'),
            'abandoned' => __('Abandonado', 'fc-recovery-carts'),
            'recovered' => __('Recuperado', 'fc-recovery-carts'),
            'lost' => __('Perdido', 'fc-recovery-carts'),
        );

        return sprintf( '<span class="status-label %s">%s</span>', esc_attr( $status ), esc_html( $statuses[$status] ?? ucfirst($status) ) );
    }


    /**
     * Define bulk actions available in the table
     * 
     * @since 1.0.0
     * @return array
     */
    public function get_bulk_actions() {
        return array(
            'delete' => __('Excluir', 'fc-recovery-carts'),
            'mark_lost' => __('Marcar como perdido', 'fc-recovery-carts'),
            'mark_abandoned' => __('Marcar como abandonado', 'fc-recovery-carts'),
            'mark_recovered' => __('Marcar como recuperado', 'fc-recovery-carts'),
        );
    }


    /**
     * Process bulk actions
     * 
     * @since 1.0.0
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
                        ) );
                    }

                    break;
                case 'mark_abandoned':
                    foreach ( $cart_ids as $cart_id ) {
                        wp_update_post( array(
                            'ID' => $cart_id,
                            'post_status' => 'abandoned',
                        ) );
                    }

                    break;
                case 'mark_recovered':
                    foreach ( $cart_ids as $cart_id ) {
                        wp_update_post( array(
                            'ID' => $cart_id,
                            'post_status' => 'recovered',
                        ) );
                    }
                    
                    break;
            }
        }
    }


    /**
     * Prepare items for display in the table
     * 
     * @since 1.0.0
     * @return void
     */
    public function prepare_items() {
        $this->process_bulk_action();

        $per_page = 10;
        $current_page = $this->get_pagenum();

        $args = array(
            'post_type' => 'fc-recovery-carts',
            'posts_per_page' => $per_page,
            'paged' => $current_page,
        );

        $query = new \WP_Query( $args );
        $this->items = $query->posts;

        $this->_column_headers = array( $this->get_columns(), array(), array() );

        $this->set_pagination_args( array(
            'total_items' => $query->found_posts,
            'per_page' => $per_page,
            'total_pages' => ceil( $query->found_posts / $per_page ),
        ));
    }
}