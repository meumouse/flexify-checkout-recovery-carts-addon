<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;

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
 * @version 1.1.0
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
     * Display the admin page content
     * 
     * @since 1.1.0
     * @return void
     */
    public function display_page() {
        echo '<div class="wrap"><h1 class="wp-heading-inline">' . __( 'Gerenciar carrinhos', 'fc-recovery-carts' ) . '</h1>';
       
        echo '<form method="post">';
            echo '<input type="hidden" name="page" value="' . esc_attr( $_REQUEST['page'] ?? '' ) . '" />';
            echo '<input type="hidden" name="post_status" value="' . esc_attr( $_REQUEST['post_status'] ?? '' ) . '" />';

            $this->search_box( __( 'Buscar carrinhos', 'fc-recovery-carts' ), 'fcrc_cart_search' );
            $this->display();
        echo '</form></div>';
    }


    /**
     * Display navigation tabs for filtering carts by status
     * 
     * @since 1.1.0
     * @return void
     */
    public function display_navigation_tabs() {
        global $wpdb;

        $statuses = array(
            'all' => __( 'Todos', 'fc-recovery-carts' ),
            'shopping' => __( 'Comprando', 'fc-recovery-carts' ),
            'abandoned' => __( 'Abandonado', 'fc-recovery-carts' ),
            'order_abandoned' => __( 'Pedido Abandonado', 'fc-recovery-carts' ),
            'recovered' => __( 'Recuperado', 'fc-recovery-carts' ),
            'lead' => __( 'Lead', 'fc-recovery-carts' ),
            'lost' => __( 'Perdido', 'fc-recovery-carts' ),
        );

        $current_status = isset( $_GET['post_status'] ) ? sanitize_text_field( $_GET['post_status'] ) : 'all';

        // count the number of carts for each status
        $counts = array();

        foreach ( $statuses as $status => $label ) {
            if ( $status === 'all' ) {
                $counts[$status] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'fc-recovery-carts'");
            } else {
                $counts[$status] = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'fc-recovery-carts' AND post_status = %s",
                    $status
                ));
            }
        }

        echo '<ul class="subsubsub">';

        $links = array();

        foreach ( $statuses as $status => $label ) {
            $url = add_query_arg( 'post_status', $status, admin_url('admin.php?page=fc-recovery-carts') );
            $class = ( $status === $current_status ) ? 'current' : '';
    
            $links[] = sprintf( '<li><a href="%s" class="%s">%s <span class="count">(%d)</span></a></li>', esc_url( $url ), esc_attr( $class ), esc_html( $label ), intval( $counts[$status] ?? 0 ) );
        }

        echo implode(' | ', $links);
        echo '</ul>';
    }


    /**
     * Render the navigation tabs and the table
     * 
     * @since 1.1.0
     * @return void
     */
    public function display() {
        $this->display_navigation_tabs();

        parent::display();
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
            'abandoned'   => __('Data do evento', 'fc-recovery-carts'),
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
     * @return void
     */
    public function column_contact( $item ) {
        $contact_name = get_post_meta( $item->ID, '_fcrc_full_name', true ) ?? Admin::get_setting('fallback_first_name');
        $phone = get_post_meta( $item->ID, '_fcrc_cart_phone', true ) ?? '';
        $email = get_post_meta( $item->ID, '_fcrc_cart_email', true ) ?? '';
        $user_id = get_post_meta( $item->ID, '_fcrc_user_id', true ) ?? '';

        if ( $email ) {
            echo esc_html( $contact_name ) . '<br>'. esc_html( $phone ) . '<br><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
        }

        // if has a user associated, display the link to the profile
        if ( $user_id ) {
            $user_profile_link = get_edit_user_link( $user_id );

            echo $contact_display = '<br><small><a href="' . esc_url( $user_profile_link ) . '" class="button button-small" style="margin-top: 1rem;">' . __( 'Ver usuário', 'fc-recovery-carts' ) . '</a></small>';
        }
    }


    /**
     * Render the location column from user meta data
     *
     * @since 1.0.0
     * @version 1.0.1
     * @param object $item | Cart data
     * @return string
     */
    public function column_location( $item ) {
        $city = get_post_meta( $item->ID, '_fcrc_location_city', true );
        $state = get_post_meta( $item->ID, '_fcrc_location_state', true );
        $zipcode = get_post_meta( $item->ID, '_fcrc_location_zipcode', true );
        $country = get_post_meta( $item->ID, '_fcrc_location_country_code', true );
        $ip = get_post_meta( $item->ID, '_fcrc_location_ip', true );
    
        // Se os dados de localização não existirem no post_meta, tenta buscar no perfil do usuário
        if ( empty( $city ) || empty( $state ) || empty( $country ) ) {
            $user_id = get_post_meta( $item->ID, '_fcrc_user_id', true );
    
            if ( $user_id ) {
                $city = get_user_meta( $user_id, 'billing_city', true ) ?: $city;
                $state = get_user_meta( $user_id, 'billing_state', true ) ?: $state;
                $zipcode = get_user_meta( $user_id, 'billing_postcode', true ) ?: $zipcode;
                $country = get_user_meta( $user_id, 'billing_country', true ) ?: $country;
            }
        }
    
        // formatt the location only if there are data available
        if ( ! empty( $city ) || ! empty( $state ) || ! empty( $country ) ) {
            $formatted_location = sprintf(
                '%s - %s (%s) - %s',
                esc_html( $city ?: 'N/A' ),
                esc_html( $state ?: 'N/A' ),
                esc_html( $zipcode ?: 'N/A' ),
                esc_html( $country ?: 'N/A' )
            );
    
            // add the IP if available
            if ( ! empty( $ip ) ) {
                $formatted_location .= sprintf( ' <br><small>IP: %s</small>', esc_html( $ip ) );
            }
    
            return $formatted_location;
        }
    
        return esc_html__( 'Não informado', 'fc-recovery-carts' );
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
     * @version 1.1.0
     * @param object $item | Cart data
     * @return string
     */
    public function column_abandoned( $item ) {
        if ( get_post_status( $item->ID ) === 'shopping' ) {
            return esc_html__( 'Carrinho ainda ativo', 'fc-recovery-carts' );
        }

        $purchased = get_post_meta( $item->ID, '_fcrc_purchased', true );
        $order_date = get_post_meta( $item->ID, '_fcrc_order_date_created', true );
        $order_id = get_post_meta( $item->ID, '_fcrc_order_id', true );
    
        if ( isset( $purchased ) && $purchased ) {
            if ( isset( $order_date ) && isset( $order_id ) ) {
                $order_edit_link = get_edit_post_link( $order_id );
    
                // convert the date to the WordPress timezone
                $formatted_date = wp_date( get_option('date_format') . ' - ' . get_option('time_format'), strtotime( $order_date ) );
    
                $order_text = sprintf( __( 'Pedido #%s', 'fc-recovery-carts' ), esc_html( $order_id ) );
    
                if ( $order_edit_link ) {
                    return sprintf( '<div><a href="%s">%s</a><p>%s</p></div>', esc_url( $order_edit_link ), $order_text, esc_html( $formatted_date ), );
                }
    
                return $order_text;
            } else {
                return esc_html__( 'Pedido realizado', 'fc-recovery-carts' );
            }
        }

        $abandoned_time = get_post_meta( $item->ID, '_fcrc_abandoned_time', true );
    
        if ( ! empty( $abandoned_time ) ) {
            return esc_html( date( 'd/m/Y H:i', strtotime( $abandoned_time ) ) );
        }
    
        return esc_html__( 'N/A', 'fc-recovery-carts' );
    }


    /**
     * Render the status column
     * 
     * @since 1.0.0
     * @version 1.0.1
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
            'purchased' => __('Comprou', 'fc-recovery-carts'),
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
     * @version 1.1.0
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
                         * @since 1.0.0
                         * @param int $cart_id | The abandoned cart ID
                         */
                        do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Lost', $cart_id );
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


    /**
     * Prepare items for display in the table
     * 
     * @since 1.0.0
     * @version 1.1.0
     * @return void
     */
    public function prepare_items() {
        $this->process_bulk_action();
        $per_page = $per_page = $this->get_items_per_page( 'fc_recovery_carts_per_page', 20 );        ;
        $current_page  = $this->get_pagenum();
        $post_status = isset( $_GET['post_status'] ) ? sanitize_key( $_GET['post_status'] ) : 'all';

        // Sets query arguments based on the selected tab
        $args = array(
            'post_type' => 'fc-recovery-carts',
            'posts_per_page' => $per_page,
            'paged' => $current_page,
        );

        // Sets the status of posts based on the selected tab
        if ( $post_status !== 'all' ) {
            $args['post_status'] = $post_status;
        } else {
            $args['post_status'] = array( 'lead', 'shopping', 'abandoned', 'order_abandoned', 'recovered', 'lost', 'purchased' );
        }

        // filter posts based on search
        $search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';

        if ( $search ) {
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'key' => '_fcrc_full_name',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => '_fcrc_cart_email',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => '_fcrc_cart_phone',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => '_fcrc_cart_items',
                    'value' => $search,
                    'compare' => 'LIKE',
                ),
            );

            // if is numeric, also search by post ID and order ID
            if ( is_numeric( $search ) ) {
                $args['post__in'] = array( intval( $search ) );

                $meta_query[] = array(
                    'key' => '_fcrc_order_id',
                    'value' => $search,
                    'compare' => '=',
                );
            }

            $args['meta_query'] = $meta_query;
        }

        $query = new \WP_Query( $args );
        $total_items = $query->found_posts;
        $this->items = $query->posts;
        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil( $total_items / $per_page ),
        ));
    }
}