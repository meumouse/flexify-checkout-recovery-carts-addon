<?php

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Components as Admin_Components;

/**
 * Tab file for follow up on settings page
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */

// Exit if accessed directly.
defined('ABSPATH') || exit; ?>

<div id="follow_up" class="nav-content">
    <div class="ps-5">
        <?php echo Admin_Components::follow_up_list(); ?>

        <button id="fcrc_add_new_follow_up_trigger" class="btn btn-primary mt-3"><?php esc_html_e( 'Adicionar novo evento', 'fc-recovery-carts' ); ?></button>

        <div id="fcrc_add_new_follow_up_container" class="fcrc-popup-container">
            <div class="fcrc-popup-content">
                <div class="fcrc-popup-header">
                    <h5 class="fcrc-popup-title"><?php esc_html_e( 'Adicionar novo follow up', 'fc-recovery-carts' ); ?></h5>
                    <button id="fcrc_add_new_follow_up_close" class="btn-close fs-5" aria-label="<?php esc_attr_e( 'Fechar', 'fc-recovery-carts' ); ?>"></button>
                </div>

                <div class="fcrc-popup-body">
                    <div class="mb-4">
                        <label class="form-label text-left"><?php esc_html_e( 'Nome do evento: *', 'fc-recovery-carts' ); ?></label>
                        <input id="fcrc_add_new_follow_up_title" type="text" class="form-control" placeholder="<?php esc_attr_e( 'Nome do evento', 'fc-recovery-carts' ); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-left"><?php esc_html_e( 'Mensagem: *', 'fc-recovery-carts' ); ?></label>
                        <textarea id="fcrc_add_new_follow_up_message" class="form-control" placeholder="<?php esc_attr_e( 'Mensagem que será enviada', 'fc-recovery-carts' ); ?>"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-left mb-3"><?php esc_html_e( 'Canal da notificação: *', 'fc-recovery-carts' ); ?></label>
                        
                        <div class="d-flex align-items-center">
                            <span class="fs-6 me-3"><?php esc_html_e( 'WhatsApp (Joinotify)', 'fc-recovery-carts' ); ?></span>
                            <input type="checkbox" id="fcrc_add_new_follow_up_channels_whatsapp" class="toggle-switch toggle-switch-sm mt-1"/>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-left mb-3"><?php esc_html_e( 'Cupom de desconto: *', 'fc-recovery-carts' ); ?></label>

                        <?php $coupons = get_posts( array(
                            'post_type' => 'shop_coupon',
                            'posts_per_page' => -1, // Get all coupons
                            'post_status' => 'publish',
                        )); ?>

                        <select id="fcrc_get_coupon" class="form-select">
                            <option value="none"><?php esc_html_e( 'Não enviar nenhum cupom', 'fc-recovery-carts' ); ?></option>

                            <?php foreach ( $coupons as $coupon ) : 
                                $coupon_code = get_the_title( $coupon->ID ); ?>

                                <option value="<?php echo esc_attr( $coupon_code ); ?>"><?php echo esc_html( $coupon_code ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-left"><?php esc_html_e( 'Atraso: *', 'fc-recovery-carts' ); ?></label>

                        <div class="input-group get-delay-info">
                            <input id="fcrc_add_new_follow_up_delay_time" type="number" class="form-control" min="0" placeholder="<?php esc_attr_e( '1', 'fc-recovery-carts' ); ?>">

                            <select id="fcrc_add_new_follow_up_delay_type" class="form-select">
                                <option value="minutes"><?php esc_html_e( 'Minutos', 'fc-recovery-carts' ); ?></option>
                                <option value="hours"><?php esc_html_e( 'Horas', 'fc-recovery-carts' ); ?></option>
                                <option value="days"><?php esc_html_e( 'Dias', 'fc-recovery-carts' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="placeholders mb-4">
                        <?php echo Admin_Components::render_placeholders(); ?>
                    </div>
                </div>

                <div class="fcrc-popup-footer">
                    <button id="fcrc_add_new_follow_up_save" class="btn btn-primary"><?php esc_html_e( 'Salvar', 'fc-recovery-carts' ); ?></button>
                </div> 
            </div>
        </div>
    </div>
</div>