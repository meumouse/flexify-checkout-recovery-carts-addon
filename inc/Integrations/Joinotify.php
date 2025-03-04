<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Integrations;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
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
}