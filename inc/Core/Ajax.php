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
        );

        // loop for each ajax action
        foreach ( $ajax_actions as $action => $callback ) {
            add_action( "wp_ajax_$action", array( $this, $callback ) );
        }

        $nopriv_ajax_actions = array(
            'fcrc_lead_collected' => 'fcrc_lead_collected_callback',
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

            // Get current options
            $options = get_option( 'flexify_checkout_recovery_carts_settings', array() );

            // Get default options
            $default_options = Admin::set_default_options();

            // Get switch options
            $get_switchs = array_keys( $default_options['toggle_switchs'] );

            // Update switch options
            foreach ( $get_switchs as $switch ) {
                $options['toggle_switchs'][$switch] = isset( $form_data['toggle_switchs'][$switch] ) ? 'yes' : 'no';
            }

             // Update all other fields (excluding toggle_switchs and follow_up_events)
            foreach ( $default_options as $key => $value ) {
                if ( $key !== 'toggle_switchs' && $key !== 'follow_up_events' ) {
                    if ( isset( $form_data[$key] ) ) {
                        $options[$key] = sanitize_text_field( $form_data[$key] );
                    }
                }
            }

            // Preserve existing follow_up_events if not modified in the form
            if ( isset( $form_data['follow_up_events'] ) && is_array( $form_data['follow_up_events'] ) ) {
                $options['follow_up_events'] = array_replace_recursive( $options['follow_up_events'] ?? array(), $form_data['follow_up_events'] );
            }

            // Save the updated options
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
     * Get lead collected
     * 
     * @since 1.0.0
     * @return void
     */
    public function fcrc_lead_collected_callback() {
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'fcrc_lead_collected' ) {
            
        }
    }
}