<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Integrations;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Placeholders;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;

use MeuMouse\Joinotify\Core\Helpers as Joinotify_Helpers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Joinotify integration class
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Joinotify extends Integrations_Base {

    /**
     * Construct function
     *
     * @since 1.0.0
     * @return void
     */
    public function __construct() {
        add_action( 'Flexify_Checkout/Recovery_Carts/Integrations/Joinotify', array( $this, 'joinotify_settings' ) );

        // send coupon message on collect lead
        add_action( 'Flexify_Checkout/Recovery_Carts/Lead_Collected', array( $this, 'send_coupon_message' ), 10, 2 );
    }


    /**
     * Display Joinotify settings
     * 
     * @since 1.0.0
     * @return void
     */
    public function joinotify_settings() {
        ?>
        <button id="fcrc_joinotify_settings_trigger" class="btn btn-outline-primary mb-5"><?php esc_html_e( 'Configurar', 'fc-recovery-carts' ) ?></button>

        <div id="fcrc_joinotify_settings_container" class="fcrc-popup-container">
            <div class="fcrc-popup-content">
                <div class="fcrc-popup-header">
                    <h5 class="fcrc-popup-title"><?php esc_html_e( 'Configurações da integração: Joinotify', 'fc-recovery-carts' ); ?></h5>
                    <button id="fcrc_joinotify_settings_close" class="btn-close fs-5" aria-label="<?php esc_attr_e( 'Fechar', 'fc-recovery-carts' ); ?>"></button>
                </div>

                <div class="fcrc-popup-body">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th class="w-50">
                                    <?php esc_html_e( 'Remetente das notificações', 'fc-recovery-carts' ); ?>
                                    <span class="fc-recovery-carts-description"><?php esc_html_e( 'Selecione um remetente que fará o envio das notificações de recuperações de carrinhos e pedidos.', 'fc-recovery-carts' ); ?></span>
                                </th>
                                <td class="w-50">
                                    <select class="form-select" id="joinotify_sender_phone" name="joinotify_sender_phone">
                                        <option value="none" <?php selected( Admin::get_setting('joinotify_sender_phone') ?? '', 'none', true ) ?>><?php esc_html_e( 'Selecione um remetente', 'fc-recovery-carts' ) ?></option>
                                        
                                        <?php foreach ( get_option('joinotify_get_phones_senders') as $phone ) : ?>
                                            <option value="<?php esc_attr_e( $phone ) ?>" <?php selected( Admin::get_setting('joinotify_sender_phone') ?? '', $phone, true ) ?> class="get-sender-number"><?php echo class_exists('MeuMouse\Joinotify\Core\Helpers') ? esc_html( Joinotify_Helpers::validate_and_format_phone( $phone ) ) : esc_html( $phone ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }


    /**
     * Send coupon message for user
     * 
     * @since 1.0.0
     * @param int $cart_id | Cart ID
     * @param array $lead_data | Lead data
     * @return void
     */
    public function send_coupon_message( $cart_id, $lead_data ) {
        // check if message must be sent
        if ( Admin::get_switch('enable_modal_add_to_cart') !== 'yes' || Admin::get_setting('collect_lead_modal')['coupon']['enabled'] !== 'yes' ) {
            return;
        }

        // check if Joinotify is active
        if ( function_exists('joinotify_send_whatsapp_message_text') ) {
            $replacement = array(
                '{{ first_name }}' => $lead_data['first_name'] ?? Admin::get_setting('fallback_first_name'),
                '{{ last_name }}' => $lead_data['last_name'] ?? '',
                '{{ recovery_link }}' => Helpers::generate_recovery_cart_link( $cart_id ),
                '{{ coupon_code }}' => 'FALTA ALTERAR AQUI',
            );

            // Replace placeholders in the message
            $message = Placeholders::replace_placeholders( Admin::get_setting('collect_lead_modal')['message'], $replacement );
            $sender = Admin::get_setting('joinotify_sender_phone');
            $receiver = function_exists('joinotify_prepare_receiver') ? joinotify_prepare_receiver( $lead_data['phone'] ) : $lead_data['phone'];

            joinotify_send_whatsapp_message_text( $sender, $receiver, $message );
        }
    }
}