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
}