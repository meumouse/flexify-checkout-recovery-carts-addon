<?php

/**
 * Plugin Name:             Flexify Checkout: Recuperação de carrinhos abandonados
 * Description:             Recupere carrinhos e pedidos abandonados com follow up cadenciado. Plugin adicional do Flexify Checkout para WooCommerce.
 * Plugin URI:              https://meumouse.com/plugins/flexify-checkout-para-woocommerce/?utm_source=plugins_list&utm_medium=flexify_checkout_recovery_cart&utm_campaign=plugin_addon
 * Requires Plugins: 		flexify-checkout-for-woocommerce, woocommerce
 * Author:                  MeuMouse.com
 * Author URI:              https://meumouse.com/
 * Version:                 1.3.5
 * Requires PHP:            7.4
 * Tested up to:            6.9
 * WC requires at least:    6.0.0
 * WC tested up to: 		10.4.2
 * Text Domain:             fc-recovery-carts
 * Domain Path:             /languages
 * License:                 GPL2
 */

use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Init;

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Load Composer autoloader if available
$autoload = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( file_exists( $autoload ) ) {
    require_once $autoload;
}

// Initialize the plugin
$plugin = new Init();