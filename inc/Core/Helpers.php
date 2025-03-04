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
     * @since 1.0.0
     * @param $admin_page | Page string for check from admin.php?page=
     * @return bool
     */
    public static function check_admin_page( $admin_page ) {
        $current_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
        return strpos( $current_url, "admin.php?page=$admin_page" );
    }


    /**
     * Get message placeholders
     * 
     * @since 1.0.0
     * @return array
     */
    public static function get_message_placeholders() {
        return apply_filters( 'Flexify_Checkout/Recovery_Carts/Message_Placeholders', array(
            '{{ first_name }}' => esc_html__( 'Primeiro nome', 'fc-recovery-carts' ),
            '{{ last_name }}' => esc_html__( 'Sobrenome', 'fc-recovery-carts' ),
            '{{ recovery_link }}' => esc_html__( 'Link de recuperação do carrinho', 'fc-recovery-carts' ),
            '{{ coupon_code }}' => esc_html__( 'Código do cupom', 'fc-recovery-carts' ),
        ));
    }


    /**
     * Check if is product
     * 
     * @since 1.0.0
     * @return bool
     */
    public static function is_product() {
        $page_id = get_queried_object_id();
        $is_product_page = false;
        $is_shop_page = false;
        $has_product = false;
    
        // Verifica se é uma página de produto individual
        if ( is_singular('product') ) {
            return true;
        }
    
        // Verifica se é a página da loja
        if ( function_exists('wc_get_page_id') && $page_id === wc_get_page_id('shop') ) {
            return true;
        }
    
        // Verifica se a página contém produtos (como arquivos de categoria ou busca)
        if ( is_post_type_archive('product') || is_tax('product_cat') || is_tax('product_tag') || is_search() ) {
            return true;
        }

        return false;
    }
}