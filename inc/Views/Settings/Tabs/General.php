<?php

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Components as Admin_Components;

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
                                                <textarea id="title_modal_add_to_cart" class="form-control" name="collect_lead_modal[title]"><?php echo esc_textarea( Admin::get_setting('collect_lead_modal')['title'] ) ?></textarea>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th class="w-50">
                                                <?php esc_html_e( 'Título do botão de ação do modal', 'fc-recovery-carts' ); ?>
                                                <span class="fc-recovery-carts-description"><?php esc_html_e( 'Título exibido no botão dentro do modal de coleta de leads.', 'fc-recovery-carts' ); ?></span>
                                            </th>
                                            <td class="w-50">
                                                <input type="text" id="title_modal_send_lead" class="form-control" name="collect_lead_modal[button_title]" value="<?php echo Admin::get_setting('collect_lead_modal')['button_title'] ?>" />
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

                                        <tr>
                                            <th class="w-50">
                                                <?php esc_html_e( 'Mostrar modal apenas para usuários logados', 'fc-recovery-carts' ); ?>
                                                <span class="fc-recovery-carts-description"><?php esc_html_e( 'Ative essa opção para exibir o modal de coleta de leads apenas para usuários logados.', 'fc-recovery-carts' ); ?></span>
                                            </th>
                                            <td class="w-50 d-flex align-items-center">
                                                <input type="checkbox" id="display_modal_for_logged_users" class="toggle-switch" name="toggle_switchs[display_modal_for_logged_users]" value="yes" <?php checked( Admin::get_switch('display_modal_for_logged_users') === 'yes' ) ?>/>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th class="w-50">
                                                <?php esc_html_e( 'Seletores de acionamento do modal', 'fc-recovery-carts' ); ?>
                                                <span class="fc-recovery-carts-description"><?php esc_html_e( 'Os seletores são os componentes HTML que ao passar o mouse sobre o elemento mostra o modal. Exemplo: button[name="add-to-cart"]', 'fc-recovery-carts' ); ?></span>
                                            </th>
                                            <td class="w-50">
                                                <textarea id="modal_triggers_list" class="form-control" name="collect_lead_modal[triggers_list]"><?php echo esc_textarea( Admin::get_setting('collect_lead_modal')['triggers_list'] ) ?></textarea>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th class="w-50">
                                                <?php esc_html_e( 'Cupom a ser enviado', 'fc-recovery-carts' ); ?>
                                                <span class="fc-recovery-carts-description"><?php esc_html_e( 'Configure o cupom para ser enviado ao usuário ao preencher o formulário.', 'fc-recovery-carts' ); ?></span>
                                            </th>
                                            <td class="w-50">
                                                <?php echo Admin_Components::render_coupon_form( 'collect_lead_modal',  Admin::get_setting('collect_lead_modal')['coupon'] ); ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th class="w-50">
                                                <?php esc_html_e( 'Mensagem a ser enviada', 'fc-recovery-carts' ); ?>
                                                <span class="fc-recovery-carts-description"><?php esc_html_e( 'Mensagem que será enviada ao usuário ao enviar os dados do formulário.', 'fc-recovery-carts' ); ?></span>
                                            </th>
                                            <td class="w-50">
                                                <textarea id="message_to_send_lead_collected" class="form-control" name="collect_lead_modal[message]"><?php echo esc_textarea( Admin::get_setting('collect_lead_modal')['message'] ) ?></textarea>

                                                <div class="placeholders mt-4">
                                                    <?php echo Admin_Components::render_placeholders(); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <th>
                    <?php esc_html_e( 'Fallback para nome do usuário', 'fc-recovery-carts' ); ?>
                    <span class="fc-recovery-carts-description"><?php esc_html_e( 'Texto alternativo para quando não for possível recuperar o nome do usuário.', 'fc-recovery-carts' ); ?></span>
                </th>
                <td>
                    <input type="text" id="fallback_first_name" class="form-control" name="fallback_first_name" value="<?php echo Admin::get_setting('fallback_first_name') ?>"/>
                </td>
            </tr>

            <tr>
                <th>
                    <?php esc_html_e( 'Tempo para um carrinho ser considerado abandonado', 'fc-recovery-carts' ); ?>
                    <span class="fc-recovery-carts-description"><?php esc_html_e( 'Permite definir o tempo para que um carrinho seja considerado abandonado e a cadência de follow up seja iniciada.', 'fc-recovery-carts' ); ?></span>
                </th>
                <td>
                    <div class="input-group">
                        <input type="number" id="time_for_lost_carts" class="form-control" name="time_for_lost_carts" value="<?php echo esc_attr( Admin::get_setting('time_for_lost_carts') ); ?>" />

                        <select id="time_unit_for_lost_carts" class="form-select" name="time_unit_for_lost_carts">
                            <option value="minutes" <?php selected( Admin::get_setting('time_unit_for_lost_carts'), 'minutes', true ) ?>><?php esc_html_e( 'Minutos', 'fc-recovery-carts' ); ?></option>
                            <option value="hours" <?php selected( Admin::get_setting('time_unit_for_lost_carts'), 'hours', true ) ?>><?php esc_html_e( 'Horas', 'fc-recovery-carts' ); ?></option>
                            <option value="days" <?php selected( Admin::get_setting('time_unit_for_lost_carts'), 'days', true ) ?>><?php esc_html_e( 'Dias', 'fc-recovery-carts' ); ?></option>
                        </select>
                    </div>
                </td>
            </tr>

            <tr>
                <th>
                    <?php esc_html_e( 'Configurar tempo de atraso para formas de pagamentos', 'fc-recovery-carts' ); ?>
                    <span class="fc-recovery-carts-description"><?php esc_html_e( 'Permite definir o tempo para que um pedido seja considerado abandonado de acordo com a forma de pagamento.', 'fc-recovery-carts' ); ?></span>
                </th>
                <td>
                    <?php echo Admin_Components::get_payment_methods_delay_options( Admin::get_setting('payment_methods') ); ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>