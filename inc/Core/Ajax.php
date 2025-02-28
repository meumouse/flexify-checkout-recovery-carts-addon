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
            'fc_add_recovery_cart' => 'fc_add_recovery_cart_callback',
        );

        // loop for each ajax action
        foreach ( $ajax_actions as $action => $callback ) {
            add_action( "wp_ajax_$action", array( $this, $callback ) );
        }

        $nopriv_ajax_actions = array(
            'fc_add_recovery_cart' => 'fc_add_recovery_cart_callback',
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
            // Convert serialized data into an array
            parse_str( $_POST['form_data'], $form_data );

            $options = array();

            // iterate for each switch options
            foreach ( Admin::get_setting('switch_options') as $switch ) {
                $options[$switch] = isset( $form_data[$switch] ) ? 'yes' : 'no';
            }

            // Merge the form data with the default options
            $updated_options = wp_parse_args( $form_data, $options );

            // Save the updated options
            $saved_options = update_option( 'flexify_checkout_recovery_carts_settings', $updated_options );

            if ( $saved_options ) {
                $response = array(
                    'status' => 'success',
                    'toast_header_title' => esc_html__( 'Salvo com sucesso', 'fc-recovery-carts' ),
                    'toast_body_title' => esc_html__( 'As configurações foram atualizadas!', 'fc-recovery-carts' ),
                );

                if ( JOINOTIFY_DEBUG_MODE ) {
                    $response['debug'] = array(
                        'options' => $updated_options,
                    );
                }

                wp_send_json( $response ); // Send JSON response
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
            //$email = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';

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
                )
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
     * Create a new abandoned cart post when a product is added to the WooCommerce cart
     * 
     * @since 1.0.0
     * @return void
     */
    public function fc_add_recovery_cart() {
        // Verify nonce for security
        check_ajax_referer( 'fc_recovery_carts_nonce', 'security' );

        // Get current user ID or assign guest ID
        $user_id = get_current_user_id();
        $contact = isset( $_POST['contact'] ) ? sanitize_text_field( $_POST['contact'] ) : '';

        // Get WooCommerce cart total
        $cart_total = WC()->cart->get_total( 'edit' );

        // Check if an abandoned cart already exists for this user
        $existing_cart = get_posts( array(
            'post_type' => 'fc-recovery-carts',
            'post_status' => 'shopping',
            'meta_query' => array(
                array(
                    'key' => '_fc_cart_user',
                    'value' => $user_id,
                    'compare' => '='
                )
            ),
        ) );

        if ( ! empty( $existing_cart ) ) {
            wp_send_json_success( array( 'message' => __( 'Carrinho já registrado.', 'fc-recovery-carts' ) ) );
        }

        // Create a new recovery cart post
        $cart_id = wp_insert_post( array(
            'post_type'   => 'fc-recovery-carts',
            'post_status' => 'shopping',
            'post_title'  => 'Carrinho - ' . ( $user_id ? "Usuário #$user_id" : "Visitante" ),
            'post_author' => $user_id ? $user_id : 0,
        ) );

        // Store cart metadata
        if ( $cart_id ) {
            update_post_meta( $cart_id, '_fc_cart_user', $user_id );
            update_post_meta( $cart_id, '_fc_cart_contact', $contact );
            update_post_meta( $cart_id, '_fc_cart_total', $cart_total );
        }

        // Return response
        wp_send_json_success( array(
            'message' => __( 'Carrinho registrado com sucesso.', 'fc-recovery-carts' ),
            'cart_id' => $cart_id
        ) );
    }
}