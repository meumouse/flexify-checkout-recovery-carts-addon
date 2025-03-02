<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Enqueue assets class
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Assets {
   
    /**
     * Construct function
     *
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        // register settings scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'settings_scripts' ) );

        // register frontend scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
    }


    /**
     * Register settings scripts
     * 
     * @since 1.0.0
     * @return void
     */
    public function settings_scripts() {
        $min_file = FC_RECOVERY_CARTS_DEBUG_MODE ? '' : '.min';

        if ( Helpers::check_admin_page('fc-recovery-carts-settings') ) {
            // check if Flexify Dashboard is active for prevent duplicate Bootstrap files
			if ( ! class_exists('Flexify_Dashboard') ) {
                wp_enqueue_style( 'bootstrap-grid', FC_RECOVERY_CARTS_ASSETS . 'vendor/bootstrap/bootstrap-grid.min.css', array(), '5.3.3' );
                wp_enqueue_style( 'bootstrap-utilities', FC_RECOVERY_CARTS_ASSETS . 'vendor/bootstrap/bootstrap-utilities.min.css', array(), '5.3.3' );
			}

            // settings scripts
			wp_enqueue_style( 'fc-recovery-carts-styles', FC_RECOVERY_CARTS_ASSETS . 'admin/css/settings'. $min_file .'.css', array(), FC_RECOVERY_CARTS_VERSION );
			wp_enqueue_script( 'fc-recovery-carts-scripts', FC_RECOVERY_CARTS_ASSETS . 'admin/js/settings'. $min_file .'.js', array('jquery'), FC_RECOVERY_CARTS_VERSION, true );

			// settings params
			wp_localize_script( 'fc-recovery-carts-scripts', 'fcrc_settings_params', array(
				'debug_mode' => FC_RECOVERY_CARTS_DEBUG_MODE,
				'dev_mode' => FC_RECOVERY_CARTS_DEV_MODE,
				'ajax_url' => admin_url('admin-ajax.php'),
				'i18n' => array(
					'toast_aria_label' => esc_html__( 'Fechar', 'fc-recovery-carts' ),
                    'confirm_delete_follow_up' => esc_html__( 'Tem certeza que deseja excluir este evento?', 'fc-recovery-carts' ),
				),
			));
        }
    }


    /**
     * Register frontend scripts
     * 
     * @since 1.0.0
     * @return void
     */
    public function frontend_scripts() {
        $min_file = FC_RECOVERY_CARTS_DEBUG_MODE ? '' : '.min';

        if ( Helpers::is_product() ) {
            wp_enqueue_style( 'fc-recovery-carts-events-intl-tel-input-styles', FC_RECOVERY_CARTS_ASSETS . 'vendor/intl-tel-input/css/intlTelInput'. $min_file .'.css', array(), '24.6.0' );
			wp_enqueue_style( 'fc-recovery-carts-events-intl-tel-input-styles-flag-offset-2x', FC_RECOVERY_CARTS_ASSETS . 'vendor/intl-tel-input/css/flag-offset-2x.min.css', array(), FC_RECOVERY_CARTS_VERSION );
            wp_enqueue_script( 'fc-recovery-carts-events-intl-tel-input', FC_RECOVERY_CARTS_ASSETS . 'vendor/intl-tel-input/js/intlTelInput'. $min_file .'.js', array(), '24.6.0' );

            wp_enqueue_style( 'fc-recovery-carts-elements-styles', FC_RECOVERY_CARTS_ASSETS . 'frontend/css/fcrc-elements'. $min_file .'.css', array(), FC_RECOVERY_CARTS_VERSION );
			wp_enqueue_script( 'fc-recovery-carts-events-script', FC_RECOVERY_CARTS_ASSETS . 'frontend/js/events'. $min_file .'.js', array('jquery'), FC_RECOVERY_CARTS_VERSION, true );

            // events params
			wp_localize_script( 'fc-recovery-carts-events-script', 'fcrc_events_params', array(
				'debug_mode' => FC_RECOVERY_CARTS_DEBUG_MODE,
				'dev_mode' => FC_RECOVERY_CARTS_DEV_MODE,
				'ajax_url' => admin_url('admin-ajax.php'),
                'triggers_list' => Admin::get_setting('modal_triggers_list'),
                'path_to_utils' => FC_RECOVERY_CARTS_ASSETS . 'vendor/intl-tel-input/js/utils.js',
                'i18n' => array(
                    'intl_search_input_placeholder' => esc_html__( 'Pesquisar', 'fc-recovery-carts' ),
                ),
			));
        }
    }
}