<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class for initialize classes
 * 
 * @since 1.0.0
 * @version 1.0.2
 * @package MeuMouse.com
 */
class Init {

    /**
     * Construct function
     * 
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        self::instance_classes();
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