<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Admin;

use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Helpers;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Admin components class
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Components {

    /**
     * Register settings tabs through a filter
     *
     * @since 1.0.0
     * @return array
     */
    public static function get_settings_tabs() {
        return apply_filters( 'Flexify_Checkout/Recovery_Carts/Admin/Register_Settings_Tabs', array(
            'general' => array(
                'id' => 'general',
                'label' => esc_html__('Geral', 'fc-recovery-carts'),
                'icon' => '<svg class="fc-recovery-carts-tab-icon" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 14.5c-1.58 0-2.903 1.06-3.337 2.5H2v2h2.163c.434 1.44 1.757 2.5 3.337 2.5s2.903-1.06 3.337-2.5H22v-2H10.837c-.434-1.44-1.757-2.5-3.337-2.5zm0 5c-.827 0-1.5-.673-1.5-1.5s.673-1.5 1.5-1.5S9 17.173 9 18s-.673 1.5-1.5 1.5zm9-11c-1.58 0-2.903 1.06-3.337 2.5H2v2h11.163c.434 1.44 1.757 2.5 3.337 2.5s2.903-1.06 3.337-2.5H22v-2h-2.163c-.434-1.44-1.757-2.5-3.337-2.5zm0 5c-.827 0-1.5-.673-1.5-1.5s.673-1.5 1.5-1.5 1.5.673 1.5 1.5-.673 1.5-1.5 1.5z"></path><path d="M12.837 5C12.403 3.56 11.08 2.5 9.5 2.5S6.597 3.56 6.163 5H2v2h4.163C6.597 8.44 7.92 9.5 9.5 9.5s2.903-1.06 3.337-2.5h9.288V5h-9.288zM9.5 7.5C8.673 7.5 8 6.827 8 6s.673-1.5 1.5-1.5S11 5.173 11 6s-.673 1.5-1.5 1.5z"></path></svg>',
                'file' => FC_RECOVERY_CARTS_INC . 'Views/Settings/Tabs/General.php',
            ),
            'follow_up' => array(
                'id' => 'follow_up',
                'label' => esc_html__('Follow Up', 'fc-recovery-carts'),
                'icon' => '<svg class="fc-recovery-carts-tab-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22 7.999a1 1 0 0 0-.516-.874l-9.022-5a1.003 1.003 0 0 0-.968 0l-8.978 4.96a1 1 0 0 0-.003 1.748l9.022 5.04a.995.995 0 0 0 .973.001l8.978-5A1 1 0 0 0 22 7.999zm-9.977 3.855L5.06 7.965l6.917-3.822 6.964 3.859-6.918 3.852z"></path><path d="M20.515 11.126 12 15.856l-8.515-4.73-.971 1.748 9 5a1 1 0 0 0 .971 0l9-5-.97-1.748z"></path><path d="M20.515 15.126 12 19.856l-8.515-4.73-.971 1.748 9 5a1 1 0 0 0 .971 0l9-5-.97-1.748z"></path></svg>',
                'file' => FC_RECOVERY_CARTS_INC . 'Views/Settings/Tabs/FollowUp.php',
            ),
            'coupons' => array(
                'id' => 'coupons',
                'label' => esc_html__('Cupons', 'fc-recovery-carts'),
                'icon' => '<svg class="fc-recovery-carts-tab-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g> <path fill-rule="evenodd" d="M15,6 C15,6.55228475 14.5522847,7 14,7 C13.4477153,7 13,6.55228475 13,6 L3,6 L3,7.99946819 C4.2410063,8.93038753 5,10.3994926 5,12 C5,13.6005074 4.2410063,15.0696125 3,16.0005318 L3,18 L13,18 C13,17.4477153 13.4477153,17 14,17 C14.5522847,17 15,17.4477153 15,18 L21,18 L21,16.0005318 C19.7589937,15.0696125 19,13.6005074 19,12 C19,10.3994926 19.7589937,8.93038753 21,7.99946819 L21,6 L15,6 Z M23,18 C23,19.1045695 22.1045695,20 21,20 L3,20 C1.8954305,20 1,19.1045695 1,18 L1,14.8880798 L1.49927404,14.5992654 C2.42112628,14.0660026 3,13.0839642 3,12 C3,10.9160358 2.42112628,9.93399737 1.49927404,9.40073465 L1,9.11192021 L1,6 C1,4.8954305 1.8954305,4 3,4 L21,4 C22.1045695,4 23,4.8954305 23,6 L23,9.11192021 L22.500726,9.40073465 C21.5788737,9.93399737 21,10.9160358 21,12 C21,13.0839642 21.5788737,14.0660026 22.500726,14.5992654 L23,14.8880798 L23,18 Z M14,16 C13.4477153,16 13,15.5522847 13,15 C13,14.4477153 13.4477153,14 14,14 C14.5522847,14 15,14.4477153 15,15 C15,15.5522847 14.5522847,16 14,16 Z M14,13 C13.4477153,13 13,12.5522847 13,12 C13,11.4477153 13.4477153,11 14,11 C14.5522847,11 15,11.4477153 15,12 C15,12.5522847 14.5522847,13 14,13 Z M14,10 C13.4477153,10 13,9.55228475 13,9 C13,8.44771525 13.4477153,8 14,8 C14.5522847,8 15,8.44771525 15,9 C15,9.55228475 14.5522847,10 14,10 Z"></path></g></svg>',
                'file' => FC_RECOVERY_CARTS_INC . 'Views/Settings/Tabs/Coupons.php',
            ),
            'integrations' => array(
                'id' => 'integrations',
                'label' => esc_html__('Integrações', 'fc-recovery-carts'),
                'icon' => '<svg class="fc-recovery-carts-tab-icon"><path d="M3 8h2v5c0 2.206 1.794 4 4 4h2v5h2v-5h2c2.206 0 4-1.794 4-4V8h2V6H3v2zm4 0h10v5c0 1.103-.897 2-2 2H9c-1.103 0-2-.897-2-2V8zm0-6h2v3H7zm8 0h2v3h-2z"></path></svg>',
                'file' => FC_RECOVERY_CARTS_INC . 'Views/Settings/Tabs/Integrations.php',
            ),
        ));
    }


    /**
     * Render follow up list settings
     * 
     * @since 1.0.0
     * @return string
     */
    public static function follow_up_list() {
        $follow_up_list = Admin::get_setting('follow_up_events');

        ob_start(); ?>

        <?php if ( ! empty( $follow_up_list ) ) : ?>
            <ul class="list-group fcrc-follow-up-list mb-3">
                <?php foreach ( $follow_up_list as $key => $follow_up ) : ?>
                    <li class="list-group-item px-4 py-3" data-follow-up-item="<?php esc_attr_e( $key ) ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fcrc-follow-up-item-title fs-6"><?php esc_html_e( $follow_up['title'] ); ?></span>

                            <div class="d-flex align-items-center">
                                <div class="edit-follow-up-actions">
                                    <button id="fcrc_edit_follow_up_<?php esc_attr_e( $key ) ?>" class="btn btn-sm btn-outline-primary edit-follow-up-item"><?php esc_html_e( 'Editar', 'fc-recovery-carts' ); ?></button>

                                    <div id="fcrc_edit_follow_up_container_<?php esc_attr_e( $key ) ?>" class="fcrc-popup-container edit-follow-up-container" data-follow-up-item="<?php esc_attr_e( $key ) ?>">
                                        <div class="fcrc-popup-content">
                                            <div class="fcrc-popup-header">
                                                <h5 class="fcrc-popup-title"><?php esc_html_e( 'Editar evento de follow up', 'fc-recovery-carts' ); ?></h5>
                                                <button id="fcrc_edit_follow_up_close_<?php esc_attr_e( $key ) ?>" class="btn-close edit-follow-up-close fs-5 " aria-label="<?php esc_attr_e( 'Fechar', 'fc-recovery-carts' ); ?>"></button>
                                            </div>

                                            <div class="fcrc-popup-body">
                                                <div class="mb-4">
                                                    <label class="form-label text-left"><?php esc_html_e( 'Nome do evento: *', 'fc-recovery-carts' ); ?></label>
                                                    <input type="text" class="form-control get-follow-up-title" name="follow_up_events[<?php esc_attr_e( $key ) ?>][title]" value="<?php esc_attr_e( $follow_up['title'] ); ?>" placeholder="<?php esc_attr_e( 'Nome do evento', 'fc-recovery-carts' ); ?>">
                                                </div>

                                                <div class="mb-4">
                                                    <label class="form-label text-left"><?php esc_html_e( 'Mensagem: *', 'fc-recovery-carts' ); ?></label>
                                                    <textarea class="form-control get-follow-up-message" name="follow_up_events[<?php esc_attr_e( $key ) ?>][message]" placeholder="<?php esc_attr_e( 'Mensagem que será enviada', 'fc-recovery-carts' ); ?>"><?php echo esc_textarea( $follow_up['message'] ) ?></textarea>
                                                </div>

                                                <div class="mb-4">
                                                    <label class="form-label text-left mb-3"><?php esc_html_e( 'Canais: *', 'fc-recovery-carts' ); ?></label>
                                                    
                                                    <div class="d-flex align-items-center">
                                                        <span class="fs-6 me-3"><?php esc_html_e( 'WhatsApp (Joinotify)', 'fc-recovery-carts' ); ?></span>
                                                        <input type="checkbox" class="toggle-switch toggle-switch-sm mt-1 get-channel whatsapp" name="follow_up_events[<?php esc_attr_e( $key ) ?>][channels][whatsapp]" <?php checked( $follow_up['channels']['whatsapp'] === 'yes' ); ?> />
                                                    </div>
                                                </div>

                                                <?php echo self::render_coupon_form(); ?>

                                                <div class="mb-4">
                                                    <label class="form-label text-left"><?php esc_html_e( 'Atraso: *', 'fc-recovery-carts' ); ?></label>

                                                    <div class="input-group get-delay-info">
                                                        <input type="number" class="form-control get-delay-time" name="follow_up_events[<?php esc_attr_e( $key ) ?>][delay_time]" placeholder="<?php esc_attr_e( '1', 'fc-recovery-carts' ); ?>" value="<?php esc_attr_e( $follow_up['delay_time'] ?? '' ); ?>">

                                                        <select class="form-select get-delay-unit" name="follow_up_events[<?php esc_attr_e( $key ) ?>][delay_type]">
                                                            <option value="minutes" <?php selected( $follow_up['delay_type'] ?? '', 'minutes' ); ?>><?php esc_html_e( 'Minutos', 'fc-recovery-carts' ); ?></option>
                                                            <option value="hours" <?php selected( $follow_up['delay_type'] ?? '', 'hours' ); ?>><?php esc_html_e( 'Horas', 'fc-recovery-carts' ); ?></option>
                                                            <option value="days" <?php selected( $follow_up['delay_type'] ?? '', 'days' ); ?>><?php esc_html_e( 'Dias', 'fc-recovery-carts' ); ?></option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="placeholders mb-4">
                                                    <?php echo self::render_placeholders(); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-icon btn-outline-danger delete-follow-up-item ms-3" data-follow-up-item="<?php esc_attr_e( $key ) ?>">
                                    <svg class="icon icon-danger" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15 2H9c-1.103 0-2 .897-2 2v2H3v2h2v12c0 1.103.897 2 2 2h10c1.103 0 2-.897 2-2V8h2V6h-4V4c0-1.103-.897-2-2-2zM9 4h6v2H9V4zm8 16H7V8h10v12z"></path></svg>
                                </button>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <div class="alert alert-info w-fit"><?php esc_html_e( 'Nenhum evento de follow up adicionado ainda.', 'fc-recovery-carts' ); ?></div>
        <?php endif; ?>

        <?php return ob_get_clean();
    }


    /**
     * Render message placeholders
     * 
     * @since 1.0.0
     *  @return string
     */
    public static function render_placeholders() {
        ob_start(); ?>

        <div class="message-placeholders w-fit">
            <label class="form-label text-left mb-3"><?php esc_html_e( 'Variáveis de texto:', 'fc-recovery-carts' ); ?></label>

            <?php foreach ( Helpers::get_message_placeholders() as $placeholder => $title ) : ?>
                <div class="d-flex align-items-center mb-3">
                    <span class="fs-sm fs-italic me-2"><code><?php esc_html_e( $placeholder ) ?></code></span>
                    <span class="fs-sm mt-1"><?php esc_html_e( $title ) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php return ob_get_clean();
    }


    /**
     * Render  coupon form component
     * 
     * @since 1.0.0
     * @param array $settings | Current coupon settings
     * @return string
     */
    public static function render_coupon_form( $settings = array() ) {
        ob_start(); ?>

        <div class="coupon-form-wrapper">
            <div class="coupon-prefix-wrapper mb-4">
                <label class="form-label text-left mb-3"><?php esc_html_e( 'Prefixo do cupom: *', 'fc-recovery-carts' ); ?></label>
                <input type="text" class="form-control get-coupon-prefix" name="coupon_prefix" placeholder="<?php esc_attr_e( 'CUPOM_', 'fc-recovery-carts' ); ?>" value="<?php esc_attr_e( $settings['coupon_prefix'] ?? '' ); ?>">
            </div>

            <div class="discount-type-wrapper mb-4">
                <label class="form-label text-left mb-3"><?php esc_html_e( 'Tipo do desconto: *', 'fc-recovery-carts' ); ?></label>

                <select class="form-select get-coupon-type" name="coupon_type">
                    <option value="fixed_cart" <?php selected( $settings[''] ?? '', 'fixed_cart' ); ?>><?php esc_html_e( 'Valor fixo', 'fc-recovery-carts' ); ?></option>
                    <option value="percent" <?php selected( $settings[''] ?? '', 'percent' ); ?>><?php esc_html_e( 'Percentual', 'fc-recovery-carts' ); ?></option>
                </select>
            </div>

            <div class="coupon-value-wrapper mb-4">
                <label class="form-label text-left mb-3"><?php esc_html_e( 'Valor do cupom: *', 'fc-recovery-carts' ); ?></label>
                <input type="number" class="form-control get-coupon-value" value="<?php esc_attr_e( $settings[''] ?? '' ); ?>">
            </div>

            <div class="coupon-allow-free-shipping-wrapper mb-4 d-flex align-items-center">
                <label class="form-label text-left me-3"><?php esc_html_e( 'Permitir frete grátis:', 'fc-recovery-carts' ); ?></label>
                <input type="checkbox" class="toggle-switch toggle-switch-sm get-coupon-allow-free-shipping" <?php checked( $settings[''] ?? '', 'yes' ); ?>>
            </div>

            <div class="coupon-expire-time-wrapper mb-4">
                <label class="form-label text-left mb-3"><?php esc_html_e( 'Tempo de expiração do cupom: *', 'fc-recovery-carts' ); ?></label>

                <div class="input-group">
                    <input type="number" class="form-control get-coupon-expire-time" value="<?php esc_attr_e( $settings[''] ?? '' ); ?>">
                    
                    <select class="form-select get-coupon-expire-time-type">
                        <option value="minutes" <?php selected( $settings[''] ?? '', 'minutes' ); ?>><?php esc_html_e( 'Minutos', 'fc-recovery-carts' ); ?></option>
                        <option value="hours" <?php selected( $settings[''] ?? '', 'hours' ); ?>><?php esc_html_e( 'Horas', 'fc-recovery-carts' ); ?></option>
                        <option value="days" <?php selected( $settings[''] ?? '', 'days' ); ?>><?php esc_html_e( 'Dias', 'fc-recovery-carts' ); ?></option>
                    </select>
                </div>
            </div>

            <div class="container-separator"></div>

            <div class="restrictions-wrapper mb-4">
                <span class="d-block text-left mb-4 fs-6"><?php esc_html_e( 'Restrições:', 'fc-recovery-carts' ); ?></span>

                <div class="mb-3">
                    <label class="form-label text-left mb-3"><?php esc_html_e( 'Limite de uso por cupom:', 'fc-recovery-carts' ); ?></label>
                    <input type="number" class="get-coupon-limit-usage form-control" value="<?php esc_attr_e( $settings[''] ?? '' ); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label text-left mb-3"><?php esc_html_e( 'Limite de uso por cliente:', 'fc-recovery-carts' ); ?></label>
                    <input type="number" class="get-coupon-limit-usage form-control" value="<?php esc_attr_e( $settings[''] ?? '' ); ?>">
                </div>
            </div>
        </div>

        <?php return ob_get_clean();
    }
}