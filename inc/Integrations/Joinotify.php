<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Integrations;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Placeholders;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Coupons;

use MeuMouse\Joinotify\Core\Helpers as Joinotify_Helpers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Joinotify integration class
 * 
 * @since 1.0.0
 * @version 1.3.0
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
     * @version 1.1.0
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
                                        
                                        <?php $current_senders = get_option('joinotify_get_phones_senders');
                                        
                                        if ( is_array( $current_senders ) ) :
                                            foreach ( $current_senders as $sender ) : ?>
                                                <option value="<?php esc_attr_e( $sender ) ?>" <?php selected( Admin::get_setting('joinotify_sender_phone') ?? '', $sender, true ) ?> class="get-sender-number"><?php echo class_exists('MeuMouse\Joinotify\Core\Helpers') ? esc_html( Joinotify_Helpers::validate_and_format_phone( $sender ) ) : esc_html( $sender ); ?></option>
                                            <?php endforeach;
                                        endif; ?>
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
     * @version 1.3.0
     * @param int $cart_id | Cart ID
     * @param array $lead_data | Lead data
     * @return void
     */
    public function send_coupon_message( $cart_id, $lead_data ) {
        $modal_data = Admin::get_setting('collect_lead_modal');

        // check if message must be sent
        if ( Admin::get_switch('enable_modal_add_to_cart') !== 'yes' || $modal_data['coupon']['enabled'] !== 'yes' ) {
            return;
        }

        // check if Joinotify is active
        if ( function_exists('joinotify_send_whatsapp_message_text') ) {
            // Replace placeholders in the message
            $message = Placeholders::replace_placeholders( $modal_data['message'], $cart_id );
            $sender = Admin::get_setting('joinotify_sender_phone');
            $receiver = function_exists('joinotify_prepare_receiver') ? joinotify_prepare_receiver( $lead_data['phone'] ) : $lead_data['phone'];

            if ( FC_RECOVERY_CARTS_DEBUG_MODE ) {
                error_log( 'Sending coupon message for cart: ' . $cart_id );
                error_log( 'Message: ' . print_r( $message, true ) );
                error_log( 'Sender: ' . $sender );
                error_log( 'Receiver: ' . $receiver );
            }
            
            joinotify_send_whatsapp_message_text( $sender, $receiver, $message );
        }
    }
}