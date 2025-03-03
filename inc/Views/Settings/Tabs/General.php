<?php

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;

/**
 * Template file for general settings
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */

// Exit if accessed directly.
defined('ABSPATH') || exit; ?>

<div id="general" class="nav-content">
    <table class="form-table">
        <tbody>
            <tr>
                <th>
                    <?php esc_html_e( 'Ativar modal de coleta de lead', 'fc-recovery-carts' ); ?>
                    <span class="fc-recovery-carts-description"><?php esc_html_e( 'Ative essa opção para exibir o modal de coleta informações de contato quando o usuário adicionar um produto ao carrinho.', 'fc-recovery-carts' ); ?></span>
                </th>
                <td>
                    <input type="checkbox" id="enable_modal_add_to_cart" class="toggle-switch" name="toggle_switchs[enable_modal_add_to_cart]" value="yes" <?php checked( Admin::get_switch('enable_modal_add_to_cart') === 'yes' ); ?> />

                    <button id="collect_lead_modal_settings_trigger" class="btn btn-outline-primary ms-3"><?php esc_html_e( 'Configurar', 'fc-recovery-carts' ) ?></button>

                    <div id="collect_lead_modal_settings_container" class="fcrc-popup-container">
                        <div class="fcrc-popup-content popup-lg">
                            <div class="fcrc-popup-header">
                                <h5 class="fcrc-popup-title"><?php esc_html_e( 'Configurar modal de coleta de leads', 'fc-recovery-carts' ); ?></h5>
                                <button id="collect_lead_modal_settings_close" class="btn-close fs-5" aria-label="<?php esc_attr_e( 'Fechar', 'fc-recovery-carts' ); ?>"></button>
                            </div>

                            <div class="fcrc-popup-body">
                                <table class="popup-table">
                                    <tbody>
                                        <tr>
                                            <th class="w-50">
                                                <?php esc_html_e( 'Título do modal de de coleta de leads', 'fc-recovery-carts' ); ?>
                                                <span class="fc-recovery-carts-description"><?php esc_html_e( 'Título exibido dentro do modal de coleta de leads.', 'fc-recovery-carts' ); ?></span>
                                            </th>
                                            <td class="w-50">
                                                <input type="text" id="title_modal_add_to_cart" class="form-control" name="title_modal_add_to_cart" value="<?php echo Admin::get_setting('title_modal_add_to_cart') ?>"/>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th class="w-50">
                                                <?php esc_html_e( 'Título do botão de ação do modal', 'fc-recovery-carts' ); ?>
                                                <span class="fc-recovery-carts-description"><?php esc_html_e( 'Título exibido no botão dentro do modal de coleta de leads.', 'fc-recovery-carts' ); ?></span>
                                            </th>
                                            <td class="w-50">
                                                <input type="text" id="title_modal_send_lead" class="form-control" name="title_modal_send_lead" value="<?php echo Admin::get_setting('title_modal_send_lead') ?>"/>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th class="w-50">
                                                <?php esc_html_e( 'Ativar telefone internacional no modal', 'fc-recovery-carts' ); ?>
                                                <span class="fc-recovery-carts-description"><?php esc_html_e( 'Ative essa opção para exibir o seletor de lista de países dentro do modal de coleta de leads (Recomendado).', 'fc-recovery-carts' ); ?></span>
                                            </th>
                                            <td class="w-50 d-flex align-items-center">
                                                <input type="checkbox" id="enable_international_phone_modal" class="toggle-switch" name="toggle_switchs[enable_international_phone_modal]" value="yes" <?php checked( Admin::get_switch('enable_international_phone_modal') === 'yes' ) ?>/>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>