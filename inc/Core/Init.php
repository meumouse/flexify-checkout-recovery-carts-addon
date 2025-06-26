<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class for initialize classes
 * 
 * @since 1.0.0
 * @version 1.3.0
 * @package MeuMouse.com
 */
class Init {

    /**
     * Get plugin basename
     * 
     * @since 1.3.0
     * @return string
     */
    public $basename = FC_RECOVERY_CARTS_BASENAME;

    /**
     * Construct function
     * 
     * @since 1.0.0
     * @version 1.3.0
     * @return void
     */
    public function __construct() {
        // load WordPress plugin class if function is_plugin_active() is not defined
        if ( ! function_exists('is_plugin_active') ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        // check if flexify checkout is active
        if ( ! is_plugin_active('flexify-checkout-for-woocommerce/flexify-checkout-for-woocommerce.php') ) {
            add_action( 'admin_notices', array( $this, 'plugin_dependency_notice' ) );
            return;
        }

        // load text domain
        load_plugin_textdomain( 'fc-recovery-carts', false, dirname( $this->basename ) . '/languages/' );

        // add link to plugin settings
        add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'add_action_plugin_links' ), 10, 4 );
        
        // add helper links
        add_filter( 'plugin_row_meta', array( $this, 'add_row_meta_links' ), 10, 4 );

        // include plugin functions
        include_once( FC_RECOVERY_CARTS_INC . 'Core/Functions.php' );

        // instance classes
        self::instance_classes();
    }


    /**
     * Flexify Checkout requires notice
     * 
     * @since 1.0.0
     * @version 1.3.0
     * @return void
     */
    public function plugin_dependency_notice() {
        $class = 'notice notice-error is-dismissible';
        $message = __( '<strong>Flexify Checkout - Recuperação de carrinhos abandonados</strong> requer o plugin Flexify Checkout para WooCommerce.', 'fc-recovery-carts' );

        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
    }


    /**
     * Plugin action links
     *
     * @since 1.0.0
     * @version 1.3.0
     * @param array $action_links | Default plugin action links
     * @return array
     */
    public function add_action_plugin_links( $action_links ) {
        $plugin_links = array(
            '<a href="'. admin_url('admin.php?page=fc-recovery-carts-settings') .'">'. esc_html__( 'Configurações', 'fc-recovery-carts' ) .'</a>',
        );

        return array_merge( $plugin_links, $action_links );
    }


    /**
     * Add meta links on plugin
     *
     * @since 1.0.0
     * @version 1.3.0
     * @param array $plugin_meta | Plugin metadata
     * @param string $plugin_file | Plugin file path
     * @param array $plugin_data | Plugin data
     * @param string $status | Plugin status
     * @return array
     */
    public function add_row_meta_links( $plugin_meta, $plugin_file, $plugin_data, $status ) {
        if ( strpos( $plugin_file, $this->basename ) !== false ) {
            $new_links = array(
                'docs' => '<a href="'. esc_attr( FC_RECOVERY_CARTS_DOCS_URL ) .'" target="_blank">'. esc_html__( 'Documentação', 'fc-recovery-carts' ) .'</a>',
            );

            $plugin_meta = array_merge( $plugin_meta, $new_links );
        }

        return $plugin_meta;
    }


    /**
     * Instance classes after load Composer
     * 
     * @since 1.0.0
     * @version 1.0.2
     * @return void
     */
    public static function instance_classes() {
        $classes = apply_filters( 'Flexify_Checkout/Recovery_Carts/Instance_Classes', array(
			'\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Compatibility',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Assets',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Ajax',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Frontend\Lead_Capture',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Frontend\Styles',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Integrations\Joinotify',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Cart_Events',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Cron\Recovery_Handler',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Session_Handler',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Order_Events',
            '\MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Updater',
        ));

        foreach ( $classes as $class ) {
            if ( class_exists( $class ) ) {
                new $class();
            }
        }
    }
}