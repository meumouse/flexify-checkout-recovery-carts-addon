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
            'cb'          => '<input type="checkbox" />',
            'id'          => __('ID', 'fc-recovery-carts'),
            'contact'     => __('Contato', 'fc-recovery-carts'),
            'location'    => __('Localização', 'fc-recovery-carts'),
            'products'    => __('Produtos', 'fc-recovery-carts'),
            'total'       => __('Valor do carrinho', 'fc-recovery-carts'),
            'abandoned'   => __('Data de abandono', 'fc-recovery-carts'),
            'status'      => __('Status', 'fc-recovery-carts'),
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
        $contact_name = get_post_meta( $item->ID, '_fcrc_full_name', true );
        $phone = get_post_meta( $item->ID, '_fcrc_cart_phone', true );
        $email = get_post_meta( $item->ID, '_fcrc_cart_email', true );
        $user_id = get_post_meta( $item->ID, '_fcrc_user_id', true );

        if ( $email ) {
            echo esc_html( $contact_name ) . '<br>'. esc_html( $phone ) . '<br><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
        }

        // if has a user associated, display the link to the profile
        if ( $user_id ) {
            $user_profile_link = get_edit_user_link( $user_id );
            $contact_display = '<br><small><a href="' . esc_url( $user_profile_link ) . '" class="button button-small" style="margin-top: 1rem;">' . __( 'Ver usuário', 'fc-recovery-carts' ) . '</a></small>';
        }

        return $contact_display ? $contact_display : __('Não informado', 'fc-recovery-carts');
    }


    /**
     * Render the location column from user meta data
     *
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_location( $item ) {
        // Get the user ID associated with the cart
        $user_id = get_post_meta( $item->ID, '_fcrc_user_id', true );

        // Get user billing information
        $city = esc_html( get_user_meta( $user_id, 'billing_city', true ) ) ?? '';
        $state = esc_html( get_user_meta( $user_id, 'billing_state', true ) ) ?? '';
        $zipcode = esc_html( get_user_meta( $user_id, 'billing_postcode', true ) ) ?? '';
        $country = esc_html( get_user_meta( $user_id, 'billing_country', true ) ) ?? '';

        // Format the location if the data exists
        if ( ! empty( $city ) ) {
            return sprintf( '%s - %s (%s) - %s', $city, $state, $zipcode, $country );
        }

        return esc_html__('Não informado', 'fc-recovery-carts');
    }


    /**
     * Render the products column
     *
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_products( $item ) {
        $cart_items = get_post_meta( $item->ID, '_fcrc_cart_items', true );
        $cart_items = is_array( $cart_items ) ? $cart_items : array();

        if ( empty( $cart_items ) ) {
            return __('Nenhum produto', 'fc-recovery-carts');
        }

        $output = '<div class="fcrc-cart-products">';

        foreach ( $cart_items as $item_data ) {
            $image = ! empty( $item_data['image'] ) ? esc_url( $item_data['image'] ) : wc_placeholder_img_src();
            $product_name = isset( $item_data['name'] ) ? esc_html( $item_data['name'] ) : __('Produto desconhecido', 'fc-recovery-carts');
            $quantity = isset( $item_data['quantity'] ) ? intval( $item_data['quantity'] ) : 1;
            $formatted_product = sprintf( __('%s - Qtd: %d', 'fc-recovery-carts'), $product_name, $quantity );

            $output .= sprintf(
                '<div class="fcrc-cart-product fcrc-tooltip" data-text="%s">
                    <img src="%s" alt="%s"/>
                </div>',
                esc_attr( $formatted_product ),
                esc_url( $image ),
                esc_attr( $product_name )
            );
        }

        $output .= '</div>';

        return $output;
    }


    /**
     * Render the cart total column
     * 
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_total( $item ) {
        $total = get_post_meta( $item->ID, '_fcrc_cart_total', true );
        $status = get_post_status( $item->ID );

        if ( $status === 'lead' ) {
            return __( 'Não informado', 'fc-recovery-carts' );
        }

        $output = '<div class="fcrc-cart-total">';
            $output .= $total ? wc_price( $total ) : __('N/A', 'fc-recovery-carts');
        $output .= '</div>';

        return $output;
    }


    /**
     * Render the abandoned date column
     * 
     * @since 1.0.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_abandoned( $item ) {
        $abandoned_time = get_post_meta( $item->ID, '_fcrc_abandoned_time', true );

        if ( ! empty( $abandoned_time ) ) {
            return esc_html( date( 'd/m/Y H:i', strtotime( $abandoned_time ) ) );
        }

        return __( 'Carrinho ainda ativo', 'fc-recovery-carts' );
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
            'lead' => __('Lead', 'fc-recovery-carts'),
            'shopping' => __('Comprando', 'fc-recovery-carts'),
            'abandoned' => __('Abandonado', 'fc-recovery-carts'),
            'order_abandoned' => __('Pedido abandonado', 'fc-recovery-carts'),
            'recovered' => __('Recuperado', 'fc-recovery-carts'),
            'lost' => __('Perdido', 'fc-recovery-carts'),
        );

        return sprintf( '<span class="status-label %s">%s</span>', esc_attr( $status ), esc_html( $statuses[$status] ?? ucfirst( $status ) ) );
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
            'post_status' => array( 'lead', 'shopping', 'abandoned', 'order_abandoned', 'recovered', 'lost' ),
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