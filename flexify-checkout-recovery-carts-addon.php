<?php

/**
 * Plugin Name:             Flexify Checkout - Recuperação de Carrinhos Abandonados Addon
 * Description:             Plugin adicional para Flexify Checkout, adicionando recuperação de carrinhos abandonados.
 * Plugin URI:              https://meumouse.com/plugins/fc-recovery-carts/
 * Author:                  MeuMouse.com
 * Author URI:              https://meumouse.com/
 * Version:                 1.0.0
 * Requires PHP:            7.4
 * Tested up to:            6.7.2
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
        public static $slug = 'fc-recovery-carts';

        /**
         * The plugin version
         *
         * @var string
         * @since 1.0.0
         */
        public static $version = '1.0.0';


        /**
         * Constructor function
         *
         * @since 1.0.0
         * @return void
         */
        public function __construct() {
            do_action('before_fc_recovery_carts_init');

            add_action( 'plugins_loaded', array( $this, 'init' ), 99 );

            do_action('fc_recovery_carts_init');
        }


        /**
         * Initialize the plugin
         *
         * @since 1.0.0
         * @return void
         */
        public function init() {
            if ( version_compare( phpversion(), '7.4', '<' ) ) {
                add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
                return;
            }

            $this->setup_constants();

            load_plugin_textdomain( 'fc-recovery-carts', false, dirname( FC_RECOVERY_CARTS_BASENAME ) . '/languages/' );
            add_filter( 'plugin_action_links_' . FC_RECOVERY_CARTS_BASENAME, array( $this, 'add_action_plugin_links' ), 10, 4 );
            add_filter( 'plugin_row_meta', array( $this, 'add_row_meta_links' ), 10, 4 );

            require_once FC_RECOVERY_CARTS_DIR . 'vendor/autoload.php';

            new \MeuMouse\Flexify_Checkout_Recovery_Carts\Core\Init;
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
         * @return void
         */
        public function setup_constants() {
            $this->define( 'FC_RECOVERY_CARTS_BASENAME', plugin_basename( __FILE__ ) );
            $this->define( 'FC_RECOVERY_CARTS_DIR', plugin_dir_path( __FILE__ ) );
            $this->define( 'FC_RECOVERY_CARTS_INC', FC_RECOVERY_CARTS_DIR . 'inc/' );
            $this->define( 'FC_RECOVERY_CARTS_URL', plugin_dir_url( __FILE__ ) );
            $this->define( 'FC_RECOVERY_CARTS_VERSION', self::$version );
        }


        /**
         * Define a constant if not already set
         *
         * @since 1.0.0
         * @param string $name | Constant name
         * @param string|bool $value | Constant value
         * @return void
         */
        private function define( $name, $value ) {
            if ( ! defined( $name ) ) {
                define( $name, $value );
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


        /**
         * Plugin action links
         *
         * @since 1.0.0
         * @param array $action_links | Default plugin action links
         * @return array
         */
        public function add_action_plugin_links( $action_links ) {
            $plugin_links = array(
                '<a href="' . admin_url('admin.php?page=fc-recovery-carts-settings') . '">'. __( 'Configurações', 'fc-recovery-carts' ) .'</a>',
            );

            return array_merge( $plugin_links, $action_links );
        }


        /**
         * Add meta links on plugin
         *
         * @since 1.0.0
         * @param array $plugin_meta | Plugin metadata
         * @param string $plugin_file | Plugin file path
         * @param array $plugin_data | Plugin data
         * @param string $status | Plugin status
         * @return array
         */
        public function add_row_meta_links( $plugin_meta, $plugin_file, $plugin_data, $status ) {
            if ( strpos( $plugin_file, FC_RECOVERY_CARTS_BASENAME ) !== false ) {
                $new_links = array(
                    'docs' => '<a href="https://ajuda.meumouse.com/docs/fc-recovery-carts/overview" target="_blank">'. __( 'Documentação', 'fc-recovery-carts' ) .'</a>',
                );
                $plugin_meta = array_merge( $plugin_meta, $new_links );
            }

            return $plugin_meta;
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