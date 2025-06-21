<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Default options class
 * 
 * @since 1.3.0
 * @package MeuMouse.com
 */
class Default_Options {

    /**
     * Set default options
     * 
     * @since 1.0.0
     * @version 1.3.0
     * @return array
     */
    public function set_default_options() {
        // get current payment methods
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        $payment_methods = array();

        foreach ( $payment_gateways as $gateway_id => $gateway ) {
            $payment_methods[$gateway_id] = array(
                'delay_time' => 5,
                'delay_unit' => 'minutes',
            );
        }

        return apply_filters( 'Flexify_Checkout/Recovery_Carts/Set_Default_Options', array(
            'time_for_lost_carts' => 15,
            'time_unit_for_lost_carts' => 'minutes',
            'toggle_switchs' => array(
                'enable_modal_add_to_cart' => 'yes',
                'enable_international_phone_modal' => 'yes',
                'enable_joinotify_integration' => 'yes',
                'enable_email_integration' => 'no',
                'display_modal_for_logged_users' => 'no',
                'enable_get_location_from_ip' => 'yes',
            ),
            'follow_up_events' => array(
                'mensagem_em_1_hora' => array(
                    'title' => 'Mensagem em 1 hora',
                    'message' => "*{{ first_name }}, você esqueceu algo no carrinho?*\n\nOi {{ first_name }}, vimos que você adicionou produtos ao carrinho, mas não finalizou a compra. Eles ainda estão reservados para você! 😊\n\nFinalize seu pedido agora: {{ recovery_link }}\n\nSe precisar de ajuda, estamos por aqui!",
                    'delay_time' => 1,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'no',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => '',
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => '',
                        'expiration_time_unit' => '',
                        'limit_usages' => '',
                        'limit_usages_per_user' => '',
                    ),
                ),
                'mensagem_em_3_horas' => array(
                    'title' => 'Mensagem em 3 horas',
                    'message' => "*🔥 Seus itens ainda estão disponíveis!* \n\n{{ first_name }}, seu carrinho ainda está esperando por você! Mas não podemos garantir que os estoques durem muito tempo. \n\nAproveite e finalize sua compra agora: {{ recovery_link }}\n\nQualquer dúvida, estamos à disposição!",
                    'delay_time' => 3,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'no',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => '',
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => '',
                        'expiration_time_unit' => '',
                        'limit_usages' => '',
                        'limit_usages_per_user' => '',
                    ),
                ),
                'mensagem_em_5_horas' => array(
                    'title' => 'Mensagem em 5 horas',
                    'message' => "*🛍️ Não perca essa chance, {{ first_name }}!* \n\nAinda está interessado nos produtos do seu carrinho? Para te dar um empurrãozinho, conseguimos um *cupom especial de 5% de desconto* para você finalizar sua compra.\n\nUse o código *{{ coupon_code }}* e garanta já: {{ recovery_link }}\n\nMas corra, pois esse desconto expira em 1 hora! ⏳",
                    'delay_time' => 5,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'yes',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => 5,
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => 1,
                        'expiration_time_unit' => 'hours',
                        'limit_usages' => 1,
                        'limit_usages_per_user' => 1,
                    ),
                ),
                'mensagem_em_8_horas' => array(
                    'title' => 'Mensagem em 8 horas',
                    'message' => "*🚀 Última chance antes do estoque acabar!* \n\n{{ first_name }}, alguns itens do seu carrinho estão com *baixa disponibilidade*! Não deixe para depois.\n\nSe precisar de ajuda para concluir sua compra, estamos aqui para te auxiliar.\n\n🔗 Finalize agora: {{ recovery_link }}",
                    'delay_time' => 8,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'no',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => '',
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => '',
                        'expiration_time_unit' => '',
                        'limit_usages' => '',
                        'limit_usages_per_user' => '',
                    ),
                ),
                'mensagem_em_24_horas' => array(
                    'title' => 'Mensagem em 24 horas',
                    'message' => "*🎁 Oferta exclusiva para você, {{ first_name }}!* \n\nNotamos que você não finalizou sua compra e queremos te ajudar! Como um incentivo, liberamos um *cupom especial de 10% de desconto*.\n\nUse o código *{{ coupon_code }}*. *Atenção! Este cupom expira em 1 hora!*\n\nFinalize sua compra pelo link: {{ recovery_link }}\n\n📌 Estamos à disposição caso tenha alguma dúvida!",
                    'delay_time' => 24,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'yes',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => 10,
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => 1,
                        'expiration_time_unit' => 'hours',
                        'limit_usages' => 1,
                        'limit_usages_per_user' => 1,
                    ),
                ),
            ),
            'primary_color' => '#008aff',
            'select_coupon' => 'none',
            'payment_methods' => $payment_methods,
            'joinotify_sender_phone' => 'none',
            'fallback_first_name' => 'Cliente',
            'collect_lead_modal' => array(
                'title' => 'Registre-se para receber um cupom de desconto e ficar por dentro das melhores ofertas!',
                'button_title' => 'Receber meu cupom',
                'message' => "Oi, {{ first_name }}! Aqui está seu cupom para usar em sua próxima compra 🎁:\n\n {{ coupon_code }}\n\nSe tiver qualquer dúvida estamos à disposição!",
                'triggers_list' => 'button[name="add-to-cart"], a.add_to_cart_button, a.ajax_add_to_cart, #wd-add-to-cart',
                'coupon' => array(
                    'enabled' => 'yes',
                    'generate_coupon' => 'yes',
                    'coupon_prefix' => 'CUPOM_',
                    'coupon_code' => 'none',
                    'discount_type' => 'percent',
                    'discount_value' => 5,
                    'allow_free_shipping' => 'yes',
                    'expiration_time' => '',
                    'expiration_time_unit' => '',
                    'limit_usages' => 1,
                    'limit_usages_per_user' => 1,
                ),
            ),
        ));
    }
}