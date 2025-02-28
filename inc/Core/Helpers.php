<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Helpers class
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Helpers {
   
    /**
     * Check admin page from partial URL
     * 
     * @since 1.1.0
     * @param $admin_page | Page string for check from admin.php?page=
     * @return bool
     */
    public static function check_admin_page( $admin_page ) {
        $current_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
        return strpos( $current_url, "admin.php?page=$admin_page" );
    }
}