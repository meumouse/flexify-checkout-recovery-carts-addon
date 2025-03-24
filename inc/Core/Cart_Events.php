<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Cron\Recovery_Handler;

use WC;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Handles cart recovery events, such as tracking and updating cart data
 *
 * @since 1.0.0
 * @version 1.1.2
 * @package MeuMouse.com
 */
class Cart_Events {

    /**
     * Constructor function
     *
     * @since 1.0.0
     * @version 1.1.0
     * @return void
     */
    public function __construct() {
        // Listen to cart changes
        add_action( 'woocommerce_add_to_cart', array( $this, 'update_cart_post' ), 10, 6 );
        add_action( 'woocommerce_cart_item_removed', array( $this, 'update_cart_post_on_change' ), 10, 2 );
        add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_cart_post_on_change' ), 10, 2 );

        // update last modified cart time
        add_action( 'woocommerce_cart_updated', array( $this, 'update_last_modified_cart_time' ) );

        add_action( 'wp_trash_post', array( $this, 'handle_cart_deletion' ), 10, 1 );
        add_action( 'before_delete_post', array( $this, 'handle_cart_deletion' ), 10, 1 );
    }


    /**
     * Updates the cart post when a product is added
     *
     * @since 1.0.0
     * @version 1.1.0
     * @param string  $cart_id          The cart item key
     * @param integer $product_id       The ID of the product added to the cart
     * @param integer $request_quantity The quantity of the item added to the cart
     * @param integer $variation_id     The variation ID (if applicable)
     * @param array   $variation        The variation data
     * @param array   $cart_item_data   Additional cart item data
     *
     * @return void
     */
    public function update_cart_post( $cart_id, $product_id, $request_quantity, $variation_id, $variation, $cart_item_data ) {
        // Check if we're in recovery mode
        if ( function_exists('WC') && WC()->session instanceof WC_Session && WC()->session->get('fcrc_cart_recovery_mode') ) {
            WC()->session->__unset('fcrc_cart_recovery_mode'); // Clear recovery mode flag
            
            return;
        }

        $cart_id = Helpers::get_current_cart_id();

        // check if cycle has already finished
        if ( $cart_id && Helpers::is_cart_cycle_finished( $cart_id ) ) {
            if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                error_log( 'Cart already finished. Skipping cart update. ID: ' . $cart_id );
            }

            return;
        }

        if ( ! $cart_id ) {
            $create_cart_id = self::create_cart_post();
        }

        $get_cart_id = isset( $create_cart_id ) ? $create_cart_id : $cart_id;

        self::sync_cart_with_post( $get_cart_id );
    }


    /**
     * Creates a new cart post if none exists
     * 
     * @since 1.1.0
     * @version 1.1.2
     * @return int $cart_id | The cart ID
     */
    public static function create_cart_post() {
        // stop processing if the cart already exists, admin requests or if cart is empty
        if ( is_admin() || Helpers::is_cart_cycle_finished() || WC()->cart->is_empty() ) {
            return;
        }
        
        // check if cart already exists
        if ( function_exists('WC') && WC()->session instanceof \WC_Session && WC()->session->get('fcrc_active_cart') ) {
            if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                error_log('Cart already exists. Skipping cart creation. ' . 'Current cart ID: ' . Helpers::get_current_cart_id() );
            }

            return;
        }

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $first_name = $user->first_name ?: '';
            $last_name = $user->last_name ?: '';
            $email = $user->user_email ?: '';
            $phone = get_user_meta( $user->ID, 'billing_phone', true ) ?: '';
        } elseif ( isset( WC()->session ) && WC()->session->get('flexify_checkout_customer_fields') ) {
            // get customer data from checkout session
            $customer_fields = WC()->session->get('flexify_checkout_customer_fields');
            $first_name = $customer_fields['billing_first_name'] ?? '';
            $last_name = $customer_fields['billing_last_name'] ?? '';
            $email = $customer_fields['billing_email'] ?? '';
            $phone = $customer_fields['billing_phone'] ?? '';
        } else {
            $first_name = $_COOKIE['fcrc_first_name'] ?? '';
            $last_name = $_COOKIE['fcrc_last_name'] ?? '';
            $email = $_COOKIE['fcrc_email'] ?? '';
            $phone = $_COOKIE['fcrc_phone'] ?? '';
        }

        // Get WooCommerce cart contents
        $cart_items_data = WC()->cart->get_cart();
        $cart_items = array();
        $cart_total = 0;

        foreach ( $cart_items_data as $cart_item_key => $cart_item ) {
            $product = wc_get_product( $cart_item['product_id'] );

            if ( ! $product ) {
                continue;
            }

            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            $price = floatval( $product->get_price() );
            $total_price = $quantity * $price;
            $cart_total += $total_price;

            $cart_items[$product_id] = array(
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total_price,
                'name' => $product->get_name(),
                'image' => get_the_post_thumbnail_url( $product_id, 'thumbnail' ),
            );
        }

        // get cached location data
        $get_location_data = isset( $_COOKIE['fcrc_location'] ) ? json_decode( stripslashes( $_COOKIE['fcrc_location'] ), true ) : null;

        // Create new cart post
        $cart_id = wp_insert_post( array(
            'post_type' => 'fc-recovery-carts',
            'post_status' => 'shopping',
            'post_title' => sprintf( __( 'Novo carrinho - %s', 'fc-recovery-carts' ), current_time('mysql') ),
            'meta_input' => array(
                '_fcrc_cart_items' => $cart_items,
                '_fcrc_cart_total' => $cart_total,
                '_fcrc_cart_updated_time' => time(),
                '_fcrc_cart_last_ping' => time(),
                '_fcrc_abandoned_time' => '',
                '_fcrc_first_name' => $first_name,
                '_fcrc_last_name' => $last_name,
                '_fcrc_full_name' => sprintf( '%s %s', $first_name, $last_name ),
                '_fcrc_cart_phone' => $phone,
                '_fcrc_cart_email' => $email,
                '_fcrc_location_city' => $get_location_data['city'] ?? '',
                '_fcrc_location_state' => $get_location_data['region'] ?? '',
                '_fcrc_location_country_code' => $get_location_data['country_code'] ?? '',
                '_fcrc_location_ip' => $get_location_data['ip'] ?? '',
            ),
        ));

        // Store cart ID in session
        WC()->session->set( 'fcrc_cart_id', $cart_id );

        // Store cart ID in cookie
        setcookie( 'fcrc_cart_id', $cart_id, time() + ( 7 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN ); // Expires in 7 days

        // set flag for prevent duplicate carts
        WC()->session->set( 'fcrc_active_cart', true );

        /**
         * Fires when a new cart is created
         * 
         * @since 1.0.1
         * @param int $cart_id | The cart ID
         */
        do_action( 'Flexify_Checkout/Recovery_Carts/New_Cart_Created', $cart_id );

        if ( FC_RECOVERY_CARTS_DEV_MODE ) {
            error_log( "New cart created: " . $cart_id );
        }

        return $cart_id;
    }


    /**
     * Updates the cart post when an item is removed or its quantity is updated.
     *
     * @since 1.0.0
     * @param string $cart_item_key | The cart item key
     * @param array  $cart | The cart object
     *
     * @return void
     */
    public function update_cart_post_on_change( $cart_item_key, $cart ) {
        self::sync_cart_with_post();
    }


    /**
     * Synchronizes WooCommerce cart data with the recovery cart post
     *
     * @since 1.0.0
     * @version 1.1.2
     * @param string $cart_id | The cart ID
     * @return void
     */
    public static function sync_cart_with_post( $cart_id = null ) {
        if ( is_admin() || WC()->cart->is_empty() ) {
            return;
        }

        $cart_id = ! empty( $cart_id ) ? $cart_id : Helpers::get_current_cart_id();

        if ( $cart_id && get_post_type( $cart_id ) !== 'fc-recovery-carts' ) {
            Helpers::clear_active_cart();
    
            if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                error_log( 'Invalid cart reference found. Session cleared.' );
            }
        }

        // check if is a cart completed
        if ( Helpers::is_cart_cycle_finished() ) {
            Helpers::clear_active_cart();

            if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                error_log( 'Cart completed detected — do not create new cart post.' );
            }

            return;
        } else {
            Recovery_Handler::detect_cart_recovery();
        }

        // if there is no cart ID, create a new one
        if ( ! $cart_id ) {
            self::create_cart_post();
        }

        $has_post = get_post( $cart_id );
        $cart_status = get_post_status( $cart_id );

        if ( ! $has_post || in_array( $cart_status, array( 'recovered', 'purchased' ), true ) ) {
            self::create_cart_post();
        }

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $first_name = $user->first_name ?: '';
            $last_name = $user->last_name ?: '';
            $email = $user->user_email ?: '';
            $phone = get_user_meta( $user->ID, 'billing_phone', true ) ?: '';
        } elseif ( isset( WC()->session ) && WC()->session->get('flexify_checkout_customer_fields') ) {
            // get customer data from checkout session
            $customer_fields = WC()->session->get('flexify_checkout_customer_fields');
            $first_name = $customer_fields['billing_first_name'] ?? '';
            $last_name = $customer_fields['billing_last_name'] ?? '';
            $email = $customer_fields['billing_email'] ?? '';
            $phone = $customer_fields['billing_phone'] ?? '';
        } else {
            $first_name = $_COOKIE['fcrc_first_name'] ?? '';
            $last_name = $_COOKIE['fcrc_last_name'] ?? '';
            $email = $_COOKIE['fcrc_email'] ?? '';
            $phone = $_COOKIE['fcrc_phone'] ?? '';
        }

        /**
         * Update contact phone
         * 
         * @since 1.0.1
         * @param string $phone | The phone number
         */
        $phone = apply_filters( 'Flexify_Checkout/Recovery_Carts/Contact_Phone', $phone );
        $contact_name = sprintf( '%s %s', $first_name, $last_name );

        // update contact data
        if ( ! empty( $first_name ) ) {
            update_post_meta( $cart_id, '_fcrc_first_name', $first_name );
        }
        
        if ( ! empty( $last_name ) ) {
            update_post_meta( $cart_id, '_fcrc_last_name', $last_name );
        }
        
        if ( ! empty( $contact_name ) ) {
            update_post_meta( $cart_id, '_fcrc_full_name', $contact_name );
        }
        
        if ( ! empty( $phone ) ) {
            update_post_meta( $cart_id, '_fcrc_cart_phone', $phone );
        }

        if ( ! empty( $email ) ) {
            update_post_meta( $cart_id, '_fcrc_cart_email', $email );
        }

        // get cached location data
        $get_location_data = isset( $_COOKIE['fcrc_location'] ) ? json_decode( stripslashes( $_COOKIE['fcrc_location'] ), true ) : null;

        // has location data
        if ( ! empty( $get_location_data ) ) {
            if ( ! empty( $get_location_data['city'] ) ) {
                update_post_meta( $cart_id, '_fcrc_location_city', $get_location_data['city'] ?? '' );
            }

            if ( ! empty( $get_location_data['region'] ) ) {
                update_post_meta( $cart_id, '_fcrc_location_state', $get_location_data['region'] ?? '' );
            }

            if ( ! empty( $get_location_data['country_code'] ) ) {
                update_post_meta( $cart_id, '_fcrc_location_country_code', $get_location_data['country_code'] ?? '' );
            }

            if ( ! empty( $get_location_data['ip'] ) ) {
                update_post_meta( $cart_id, '_fcrc_location_ip', $get_location_data['ip'] ?? '' );
            }
        }

        // Get WooCommerce cart contents
        $cart_items_data = WC()->cart->get_cart();
        $cart_items = array();
        $cart_total = 0;

        foreach ( $cart_items_data as $cart_item_key => $cart_item ) {
            $product = wc_get_product( $cart_item['product_id'] );

            if ( ! $product ) {
                continue;
            }

            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            $price = floatval( $product->get_price() );
            $total_price = $quantity * $price;
            $cart_total += $total_price;

            $cart_items[$product_id] = array(
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total_price,
                'name' => $product->get_name(),
                'image' => get_the_post_thumbnail_url( $product_id, 'thumbnail' ),
            );
        }

        // Update cart post metadata
        update_post_meta( $cart_id, '_fcrc_cart_items', $cart_items );
        update_post_meta( $cart_id, '_fcrc_cart_total', $cart_total );
        update_post_meta( $cart_id, '_fcrc_cart_updated_time', time() );
        
        if ( ! is_admin() ) {
            update_post_meta( $cart_id, '_fcrc_cart_last_ping', time() );
        }
    }


    /**
     * Updates the cart's last modified time when it's updated
     *
     * @since 1.0.0
     * @version 1.1.0
     * @return void
     */
    public function update_last_modified_cart_time() {
        self::sync_cart_with_post();
    }


    /**
     * Handle cart deletion to cancel follow-ups
     *
     * @since 1.1.0
     * @param int $post_id | Post ID
     * @return void
     */
    public function handle_cart_deletion( $post_id ) {
        if ( get_post_type( $post_id ) !== 'fc-recovery-carts' ) {
            return;
        }

        /**
         * Fired when a cart is manually deleted
         *
         * @since 1.1.0
         * @param int $post_id
         */
        do_action( 'Flexify_Checkout/Recovery_Carts/Cart_Deleted_Manually', $post_id );
    }
}