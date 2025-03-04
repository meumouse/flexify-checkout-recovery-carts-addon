<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Helpers class
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Helpers {
   
    /**
     * Check admin page from partial URL
     * 
     * @since 1.0.0
     * @param $admin_page | Page string for check from admin.php?page=
     * @return bool
     */
    public static function check_admin_page( $admin_page ) {
        $current_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
        return strpos( $current_url, "admin.php?page=$admin_page" );
    }


    /**
     * Get message placeholders
     * 
     * @since 1.0.0
     * @return array
     */
    public static function get_message_placeholders() {
        return apply_filters( 'Flexify_Checkout/Recovery_Carts/Message_Placeholders', array(
            '{{ first_name }}' => esc_html__( 'Primeiro nome', 'fc-recovery-carts' ),
            '{{ last_name }}' => esc_html__( 'Sobrenome', 'fc-recovery-carts' ),
            '{{ recovery_link }}' => esc_html__( 'Link de recuperação do carrinho', 'fc-recovery-carts' ),
            '{{ coupon_code }}' => esc_html__( 'Código do cupom', 'fc-recovery-carts' ),
        ));
    }


    /**
     * Check if is product
     * 
     * @since 1.0.0
     * @return bool
     */
    public static function is_product() {
        $page_id = get_queried_object_id();
        $is_product_page = false;
        $is_shop_page = false;
        $has_product = false;
    
        // check if is a single product page
        if ( is_singular('product') ) {
            return true;
        }
    
        // check if is shop page
        if ( function_exists('wc_get_page_id') && $page_id === wc_get_page_id('shop') ) {
            return true;
        }
    
        // check if the page contains products (like category or search files)
        if ( is_post_type_archive('product') || is_tax('product_cat') || is_tax('product_tag') || is_search() ) {
            return true;
        }

        return false;
    }


    /**
     * Converts abandonment time into seconds
     *
     * @since 1.0.0
     * @return int Time in seconds
     */
    public static function get_abandonment_time_seconds() {
        $time_limit = Admin::get_setting('time_for_lost_carts');
        $time_unit = Admin::get_setting('time_unit_for_lost_carts');

        switch ( $time_unit ) {
            case 'minutes':
                return $time_limit * 60;
            case 'hours':
                return $time_limit * 3600;
            case 'days':
                return $time_limit * 86400;
            default:
                return 1800; // Default: 30 minutes
        }
    }

    
    /**
     * Converts time to seconds based on unit
     *
     * @since 1.0.0
     * @param int $time | The time value
     * @param string $unit | The unit of time (minutes, hours, days)
     * @return int Time in seconds
     */
    public static function convert_to_seconds( $time, $unit ) {
        switch ( $unit ) {
            case 'minutes':
                return $time * 60;
            case 'hours':
                return $time * 3600;
            case 'days':
                return $time * 86400;
            default:
                return 0; // Default: 0 seconds
        }
    }


    /**
     * Generates a recovery cart link with initial products and UTM parameters
     *
     * @since 1.0.0
     * @param int $cart_id | The recovery cart post ID
     * @return string The recovery cart URL
     */
    public static function generate_recovery_cart_link( $cart_id ) {
        if ( ! $cart_id ) {
            return '';
        }

        // Get cart items from the stored cart post
        $cart_items = get_post_meta( $cart_id, '_fcrc_cart_items', true );

        if ( empty( $cart_items ) || ! is_array( $cart_items ) ) {
            return '';
        }

        // Base URL (Cart page)
        $cart_page_url = wc_get_cart_url();

        // Build query parameters
        $query_params = array(
            'recovery_cart' => $cart_id, // Cart ID identifier
            'utm_source' => 'joinotify',
            'utm_medium' => 'whatsapp',
            'utm_campaign' => 'recovery_carts',
        );

        // Append product data to the query parameters
        foreach ( $cart_items as $index => $item ) {
            $query_params["product_{$index}"] = $item['product_id'];
            $query_params["quantity_{$index}"] = $item['quantity'];
        }

        // Generate recovery link
        return add_query_arg( $query_params, $cart_page_url );
    }


    /**
     * Restores the cart from the recovery link
     *
     * @since 1.0.0
     * @return void
     */
    public static function maybe_restore_cart() {
        if ( is_admin() || ! isset( $_GET['recovery_cart'] ) ) {
            return;
        }

        $cart_id = intval( $_GET['recovery_cart'] );

        if ( ! $cart_id ) {
            return;
        }

        // Get cart items from stored cart post
        $cart_items = get_post_meta( $cart_id, '_fcrc_cart_items', true );

        if ( empty( $cart_items ) || ! is_array( $cart_items ) ) {
            return;
        }

        // Empty the current WooCommerce cart
        WC()->cart->empty_cart();

        // Add products to the cart
        foreach ( $cart_items as $item ) {
            WC()->cart->add_to_cart( $item['product_id'], $item['quantity'] );
        }

        // Redirect to cart page without query parameters
        wp_safe_redirect( wc_get_cart_url() );

        exit;
    }
}