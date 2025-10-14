<?php

/**
 * Plugin Name:             Flexify Checkout - Recuperação de carrinhos abandonados
 * Description:             Recupere carrinhos e pedidos abandonados com follow up cadenciado. Plugin adicional do Flexify Checkout para WooCommerce.
 * Plugin URI:              https://meumouse.com/plugins/flexify-checkout-para-woocommerce/?utm_source=plugins_list&utm_medium=flexify_checkout_recovery_cart&utm_campaign=plugin_addon
 * Requires Plugins: 		flexify-checkout-for-woocommerce, woocommerce
 * Author:                  MeuMouse.com
 * Author URI:              https://meumouse.com/
 * Version:                 1.3.2
 * Requires PHP:            7.4
 * Tested up to:            6.8.3
 * WC requires at least:    6.0.0
 * WC tested up to: 		10.2.2
 * Text Domain:             fc-recovery-carts
 * Domain Path:             /languages
 * License:                 GPL2
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

if ( ! class_exists('Flexify_Checkout_Recovery_Carts') ) {

    /**
     * Main class for loading the plugin
     *
     * @since 1.0.0
     * @package MeuMouse.com
     */
    class Flexify_Checkout_Recovery_Carts {

        /**
         * The single instance of the class
         *
         * @since 1.0.0
         * @var object
         */
        private static $instance = null;

        /**
         * The plugin slug
         *
         * @var string
         * @since 1.0.0
         */
        public static $slug = 'flexify-checkout-recovery-carts-addon';

        /**
         * The plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public static $version = '1.3.2';


        /**
         * Constructor function
         *
         * @since 1.0.0
         * @version 1.3.0
         * @return void
         */
        public function __construct() {
            // hook before init plugin
            do_action('before_fc_recovery_carts_init');

            // initialize the plugin after woocommerce has loaded
            add_action( 'woocommerce_loaded', array( $this, 'init' ), 99 );

            // hook after init plugin
            do_action('fc_recovery_carts_init');
        }


        /**
         * Initialize the plugin
         *
         * @since 1.0.0
         * @version 1.3.0
         * @return void
         */
        public function init() {
            // check PHP version
            if ( version_compare( phpversion(), '7.4', '<' ) ) {
                add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
                return;
            }

            // define constants
            $this->setup_constants();

            // load Composer autoloader
            require_once( FC_RECOVERY_CARTS_DIR . 'vendor/autoload.php' );

            // instance classes
            new \MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Init;
        }


        /**
         * Ensure only one instance of the class is loaded
         *
         * @since 1.0.0
         * @return object
         */
        public static function run() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @since 1.0.0
         * @version 1.3.0
         * @return void
         */
        public function setup_constants() {
            $base_file = __FILE__;
			$base_dir = plugin_dir_path( $base_file );
			$base_url = plugin_dir_url( $base_file );

			$constants = array(
				'FC_RECOVERY_CARTS_BASENAME' => plugin_basename( $base_file ),
				'FC_RECOVERY_CARTS_FILE' => $base_file,
				'FC_RECOVERY_CARTS_DIR' => $base_dir,
				'FC_RECOVERY_CARTS_INC' => $base_dir . 'inc/',
				'FC_RECOVERY_CARTS_URL' => $base_url,
				'FC_RECOVERY_CARTS_ASSETS' => $base_url . 'assets/',
				'FC_RECOVERY_CARTS_ABSPATH' => dirname( $base_file ) . '/',
				'FC_RECOVERY_CARTS_SLUG' => self::$slug,
				'FC_RECOVERY_CARTS_VERSION' => self::$version,
				'FC_RECOVERY_CARTS_ADMIN_EMAIL' => get_option('admin_email'),
				'FC_RECOVERY_CARTS_DOCS_URL' => 'https://ajuda.meumouse.com/docs/fc-recovery-carts/overview',
				'FC_RECOVERY_CARTS_DEBUG_MODE' => true,
			);

			// iterate for each constant item
			foreach ( $constants as $key => $value ) {
				if ( ! defined( $key ) ) {
					define( $key, $value );
				}
			}
        }


        /**
         * PHP version notice
         *
         * @since 1.0.0
         * @return void
         */
        public function php_version_notice() {
            $class = 'notice notice-error is-dismissible';
            $message = __( '<strong>Flexify Checkout - Recuperação de Carrinhos</strong> requer PHP 7.4 ou superior. Atualize seu ambiente de hospedagem.', 'fc-recovery-carts' );

            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
        }
    }
}

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return object
 */
Flexify_Checkout_Recovery_Carts::run();