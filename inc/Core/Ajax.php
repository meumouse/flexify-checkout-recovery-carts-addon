<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Components as Admin_Components;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Handler for ajax requests
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Ajax {
   
    /**
     * Construct function
     *
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        $ajax_actions = array(
            'fc_recovery_carts_save_options' => 'admin_save_options_callback',
            'fcrc_add_new_follow_up' => 'fcrc_add_new_follow_up_callback',
            'fcrc_delete_follow_up' => 'fcrc_delete_follow_up_callback',
            'fcrc_lead_collected' => 'fcrc_lead_collected_callback',
            'fcrc_cart_ping' => 'fcrc_cart_ping_callback',
            'fcrc_save_checkout_lead' => 'fcrc_save_checkout_lead_callback',
        );

        // loop for each ajax action
        foreach ( $ajax_actions as $action => $callback ) {
            add_action( "wp_ajax_$action", array( $this, $callback ) );
        }

        // ajax actions for not logged in users
        $nopriv_ajax_actions = array(
            'fcrc_lead_collected' => 'fcrc_lead_collected_callback',
            'fcrc_cart_ping' => 'fcrc_cart_ping_callback',
            'fcrc_save_checkout_lead' => 'fcrc_save_checkout_lead_callback',
        );

        // loop for each nopriv ajax action
        foreach ( $nopriv_ajax_actions as $action => $callback ) {
            add_action( "wp_ajax_nopriv_$action", array( $this, $callback ) );
        }
    }


    /**
     * Save options in AJAX
     * 
     * @since 1.0.0
     * @return void
     */
    public function admin_save_options_callback() {
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'fc_recovery_carts_save_options' ) {
            // convert form data for associative array
            parse_str( $_POST['form_data'], $form_data );
    
            // get current options 
            $options = get_option( 'flexify_checkout_recovery_carts_settings', array() );
    
            // get default options
            $default_options = Admin::set_default_options();
    
            // update toggle switchs
            if ( isset( $default_options['toggle_switchs'] ) ) {
                foreach ( array_keys( $default_options['toggle_switchs'] ) as $switch ) {
                    $options['toggle_switchs'][$switch] = isset( $form_data['toggle_switchs'][$switch] ) ? 'yes' : 'no';
                }
            }
    
            // update all fields except arrays like toggle_switchs and follow_up_events
            foreach ( $default_options as $key => $value ) {
                if ( ! is_array( $value ) && isset( $form_data[$key] ) ) {
                    $options[$key] = sanitize_text_field( $form_data[$key] );
                }
            }
    
            // update dynamic arrays (including follow_up_events and others)
            foreach ( $default_options as $key => $default_value ) {
                if ( is_array( $default_value ) && isset( $form_data[$key] ) ) {
                    $options[$key] = array_replace_recursive( $options[$key] ?? array(), $form_data[$key] );
                }
            }
    
            $saved_options = update_option( 'flexify_checkout_recovery_carts_settings', $options );
    
            if ( $saved_options ) {
                $response = array(
                    'status' => 'success',
                    'toast_header_title' => esc_html__( 'Salvo com sucesso', 'fc-recovery-carts' ),
                    'toast_body_title' => esc_html__( 'As configurações foram atualizadas!', 'fc-recovery-carts' ),
                );
    
                if ( FC_RECOVERY_CARTS_DEBUG_MODE ) {
                    $response['debug'] = array(
                        'options' => $options,
                    );
                }
    
                wp_send_json( $response );
            }
        }
    }


    /**
     * Add new follow up event in AJAX
     * 
     * @since 1.0.0
     * @return void
     */
    public function fcrc_add_new_follow_up_callback() {
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'fcrc_add_new_follow_up' ) {
            $title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
            $event_key = strtolower( str_replace( ' ', '_', $title ) );
            $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
            $delay_time = isset( $_POST['delay_time'] ) ? sanitize_text_field( $_POST['delay_time'] ) : '';
            $delay_type = isset( $_POST['delay_type'] ) ? sanitize_text_field( $_POST['delay_type'] ) : '';
            $whatsapp = isset( $_POST['whatsapp'] ) ? sanitize_text_field( $_POST['whatsapp'] ) : '';
            $coupon = isset( $_POST['coupon'] ) ? json_decode( stripslashes( $_POST['coupon'] ), true ) : array();

            // get current settings
            $settings = get_option( 'flexify_checkout_recovery_carts_settings', array() );

            // check if follow up events exists
            if ( ! isset( $settings['follow_up_events'] ) || ! is_array( $settings['follow_up_events'] ) ) {
                $settings['follow_up_events'] = array();
            }

            // add new follow up event on array
            $settings['follow_up_events'][$event_key] = array(
                'title' => $title,
                'message' => $message,
                'delay_time' => $delay_time,
                'delay_type' => $delay_type,
                'channels' => array(
                    'whatsapp' => $whatsapp,
                ),
                'coupon' => array(
                    'enabled' => $coupon['enabled'],
                    'generate_coupon' => $coupon['generate_coupon'],
                    'coupon_prefix' => $coupon['coupon_prefix'] ?? '',
                    'coupon_code' => $coupon['coupon_code'] ?? '',
                    'discount_type' => $coupon['discount_type'] ?? '',
                    'discount_value' => $coupon['discount_value'] ?? '',
                    'allow_free_shipping' => $coupon['allow_free_shipping'],
                    'expiration_time' => $coupon['expiration_time'] ?? '',
                    'expiration_time_unit' => $coupon['expiration_time_unit'] ?? '',
                    'limit_usages' => $coupon['limit_usages'] ?? '',
                    'limit_usages_per_user' => $coupon['limit_usages_per_user'] ?? '',
                ),
            );

            // update options
            $saved_options = update_option( 'flexify_checkout_recovery_carts_settings', $settings );

            if ( $saved_options ) {
                $response = array(
                    'status' => 'success',
                    'toast_header_title' => esc_html__( 'Salvo com sucesso', 'fc-recovery-carts' ),
                    'toast_body_title' => esc_html__( 'O evento foi adicionado com sucesso!', 'fc-recovery-carts' ),
                    'follow_up_list' => Admin_Components::follow_up_list(),
                );
            } else {
                $response = array(
                    'status' => 'error',
                    'toast_header_title' => esc_html__( 'Ops! Ocorreu um erro', 'fc-recovery-carts' ),
                    'toast_body_title' => esc_html__( 'Não foi possível adicionar o novo evento.', 'fc-recovery-carts' ),
                );
            }

            wp_send_json( $response ); // Send JSON response
        }
    }


    /**
     * Delete follow up event in AJAX
     * 
     * @since 1.0.0
     * @return void
     */
    public function fcrc_delete_follow_up_callback() {
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'fcrc_delete_follow_up' ) {
            $event_key = isset( $_POST['event_key'] ) ? sanitize_text_field( $_POST['event_key'] ) : '';

            // get current settings
            $settings = get_option( 'flexify_checkout_recovery_carts_settings', array() );

            // check if follow up events exists
            if ( isset( $settings['follow_up_events'] ) && is_array( $settings['follow_up_events'] ) ) {
                // check if follow up event exists
                if ( isset( $settings['follow_up_events'][$event_key] ) ) {
                    // delete follow up event
                    unset( $settings['follow_up_events'][$event_key] );

                    // update options
                    $saved_options = update_option( 'flexify_checkout_recovery_carts_settings', $settings );

                    if ( $saved_options ) {
                        $response = array(
                            'status' => 'success',
                            'toast_header_title' => esc_html__( 'Salvo com sucesso', 'fc-recovery-carts' ),
                            'toast_body_title' => esc_html__( 'O evento foi removido com sucesso!', 'fc-recovery-carts' ),
                            'follow_up_list' => Admin_Components::follow_up_list(),
                        );
                    } else {
                        $response = array(
                            'status' => 'error',
                            'toast_header_title' => esc_html__( 'Ops! Ocorreu um erro', 'fc-recovery-carts' ),
                            'toast_body_title' => esc_html__( 'Não foi possível remover o evento.', 'fc-recovery-carts' ),
                        );
                    }
                }
            } else {
                $response = array(
                    'status' => 'error',
                    'toast_header_title' => esc_html__( 'Ops! Ocorreu um erro', 'fc-recovery-carts' ),
                    'toast_body_title' => esc_html__( 'Não foi possível remover o evento.', 'fc-recovery-carts' ),
                );
            }
            
            wp_send_json( $response ); // Send JSON response
        }
    }

    /**
     * Get lead collected
     * 
     * @since 1.0.0
     * @return void
     */
    public function fcrc_lead_collected_callback() {
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'fcrc_lead_collected' ) {
            // Sanitiza os dados recebidos
            $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
            $last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
            $phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
            $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
            $country_ccountry_dataode = isset( $_POST['country_data'] ) ? json_decode( stripslashes( $_POST['country_data'] ), true ) : array();
            $country_code = $country_data['iso2'] ?? '';
            $country_dial_code = $country_data['dialCode'] ?? '';
            $format_phone = $country_dial_code . $phone;
            $international_phone = preg_replace( '/\D/', '', $format_phone );

            // get full name
            $contact_name = sprintf( '%s %s', $first_name, $last_name );

            // get cart total
            $cart_total = WC()->cart ? WC()->cart->get_cart_contents_total() : 0;

            // check if user exists
            $user = get_user_by( 'email', $email );

            if ( $user ) {
                // add user meta indicating that the lead was collected
                update_user_meta( $user->ID, '_fcrc_lead_collected', true );
                update_user_meta( $user->ID, 'billing_first_name', $first_name );
                update_user_meta( $user->ID, 'billing_last_name', $last_name );
                update_user_meta( $user->ID, 'billing_email', $email );
                update_user_meta( $user->ID, 'billing_phone', $phone );
                update_user_meta( $user->ID, 'billing_country', strtoupper( $country_code ) );
            }

            // create a new post of type 'fc-recovery-carts'
            $cart_id = wp_insert_post( array(
                'post_type' => 'fc-recovery-carts',
                'post_status' => 'lead',
                'post_title' => 'Lead: ' . $contact_name,
                'post_content' => '',
                'meta_input' => array(
                    '_fcrc_first_name' => $first_name,
                    '_fcrc_last_name' => $last_name,
                    '_fcrc_full_name' => $contact_name,
                    '_fcrc_cart_phone' => $international_phone,
                    '_fcrc_cart_email' => $email,
                    '_fcrc_cart_total' => $cart_total,
                    '_fcrc_cart_items' => WC()->cart ? WC()->cart->get_cart() : array(),
                    '_fcrc_user_id' => $user ? $user->ID : 0,
                ),
            ));

            // storage cart id (post id) in session
            if ( $cart_id ) {
                WC()->session->set( 'fcrc_cart_id', $cart_id );
            }

            /**
             * Hook fired on lead collected
             * 
             * @since 1.0.0
             * @param int $cart_id | Cart ID | Post ID
             * @param array $data | Lead data
             */
            do_action( 'Flexify_Checkout/Recovery_Carts/Lead_Collected', $cart_id, array(
                'first_name' => $first_name,
                'last_name' => $last_name,
                'full_name' => $contact_name,
                'phone' => $international_phone,
                'email' => $email,
                'country' => $country_code,
                'cart_total' => $cart_total,
                'user_id' => $user ? $user->ID : 0,
            ));

            // check if post was created
            if ( $cart_id ) {
                $response = array(
                    'status' => 'success',
                    'cart_id' => $cart_id,
                );
            } else {
                $response = array(
                    'status' => 'error',
                );
            }

            // send response
            wp_send_json( $response );
        }
    }


    /**
     * Handles cart activity pings from the frontend
     *
     * @since 1.0.0
     * @return void
     */
    public function fcrc_cart_ping_callback() {
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'fcrc_cart_ping' ) {
            $cart_id = intval( $_POST['cart_id'] );

            if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                error_log('Ping received from cart ID: ' . $cart_id);
            }

            if ( $cart_id ) {
                update_post_meta( $cart_id, '_fcrc_cart_last_ping', time() );
                
                wp_send_json( array(
                    'status' => 'success',
                    'response' => 'pong',
                    'timestamp' => time(),
                ));
            }
        }
    }


    /**
     * Get checkout lead data
     * 
     * @since 1.0.0
     * @return void
     */
    public function fcrc_save_checkout_lead_callback() {
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'fcrc_save_checkout_lead' ) {
            $cart_id = intval( $_POST['cart_id'] ); // get cart id from cookie inserted by update_cart_post()
            $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
            $last_name = isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
            $phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
            $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
            $full_name = sprintf( '%s %s', $first_name, $last_name );

            // check if user exists
            $user = get_user_by( 'email', $email );
            $user_id = $user ? $user->ID : 0;

            if ( $cart_id ) {
                update_post_meta( $cart_id, '_fcrc_first_name', $first_name );
                update_post_meta( $cart_id, '_fcrc_last_name', $last_name );
                update_post_meta( $cart_id, '_fcrc_full_name', $full_name );
                update_post_meta( $cart_id, '_fcrc_cart_phone', $phone );
                update_post_meta( $cart_id, '_fcrc_cart_email', $email );
                update_post_meta( $cart_id, '_fcrc_user_id', $user_id );

                /**
                 * Hook fired on lead collected on checkout
                 * 
                 * @since 1.0.0
                 * @param int $cart_id | Cart ID | Post ID
                 * @param array $data | Lead data
                 */
                do_action( 'Flexify_Checkout/Recovery_Carts/Checkout_Lead_Collected', $cart_id, array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $phone,
                    'email' => $email,
                    'user_id' => $user_id,
                ));

                if ( FC_RECOVERY_CARTS_DEV_MODE ) {
                    error_log('New checkout lead collected from cart ID: ' . $cart_id);
                }
                
                wp_send_json( array(
                    'status' => 'success',
                ));
            }
        }
    }
}