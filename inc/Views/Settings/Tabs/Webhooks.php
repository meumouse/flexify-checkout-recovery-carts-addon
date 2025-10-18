<?php
/**
 * Tab file for webhooks on settings page
 *
 * @since 1.3.2
 */

use MeuMouse\Flexify_Checkout\Recovery_Carts\Admin\Admin;
use MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Webhooks;

// Exit if accessed directly.
defined('ABSPATH') || exit;

$webhook_settings = Admin::get_setting('webhooks');

if ( ! is_array( $webhook_settings ) ) {
    $webhook_settings = array();
}

$webhook_events = Webhooks::get_registered_events(); ?>

<div id="webhooks" class="nav-content">
    <h3 class="mb-3"><?php esc_html_e( 'Webhooks', 'fc-recovery-carts' ); ?></h3>
    <span class="fc-recovery-carts-description mb-4 d-block"><?php esc_html_e( 'Envie dados dos eventos do plugin para serviços externos através de requisições HTTP com suporte a cabeçalhos personalizados.', 'fc-recovery-carts' ); ?></span>

    <?php foreach ( $webhook_events as $event_key => $event_data ) :
        $event_webhooks = $webhook_settings[ $event_key ] ?? array();

        if ( ! is_array( $event_webhooks ) ) {
            $event_webhooks = array();
        }

        $next_index = count( $event_webhooks );
    ?>
        <div class="card mb-5 fcrc-webhook-event" data-event-key="<?php echo esc_attr( $event_key ); ?>">
            <div class="card-header d-flex justify-content-between align-items-center flex-column flex-lg-row">
                <div class="text-center text-lg-start">
                    <h4 class="card-title mb-1"><?php echo esc_html( $event_data['label'] ?? ucfirst( $event_key ) ); ?></h4>

                    <?php if ( ! empty( $event_data['description'] ) ) : ?>
                        <span class="fc-recovery-carts-description mb-0"><?php echo esc_html( $event_data['description'] ); ?></span>
                    <?php endif; ?>
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm mt-3 mt-lg-0 fcrc-add-webhook" data-event="<?php echo esc_attr( $event_key ); ?>">
                    <?php esc_html_e( 'Adicionar webhook', 'fc-recovery-carts' ); ?>
                </button>
            </div>

            <div class="card-body">
                <div class="alert alert-info fcrc-webhook-empty<?php echo ! empty( $event_webhooks ) ? ' d-none' : ''; ?>">
                    <?php esc_html_e( 'Nenhum webhook configurado para este evento ainda.', 'fc-recovery-carts' ); ?>
                </div>

                <div class="fcrc-webhook-list" data-event="<?php echo esc_attr( $event_key ); ?>" data-next-index="<?php echo esc_attr( $next_index ); ?>">
                    <?php foreach ( $event_webhooks as $index => $webhook ) :
                        $enabled = $webhook['enabled'] ?? 'yes';
                        $name = $webhook['name'] ?? '';
                        $url = $webhook['url'] ?? '';
                        $headers = isset( $webhook['headers'] ) && is_array( $webhook['headers'] ) ? $webhook['headers'] : array();
                        $next_header_index = count( $headers );
                    ?>
                        <div class="card mb-4 fcrc-webhook-item" data-index="<?php echo esc_attr( $index ); ?>">
                            <div class="card-body">
                                <div class="row g-3 align-items-center mb-4">
                                    <div class="col-lg-8">
                                        <label class="form-label text-left"><?php esc_html_e( 'Nome interno', 'fc-recovery-carts' ); ?></label>
                                        <input type="text" class="form-control" name="webhooks[<?php echo esc_attr( $event_key ); ?>][<?php echo esc_attr( $index ); ?>][name]" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Identificação opcional para o webhook', 'fc-recovery-carts' ); ?>">
                                    </div>
                                    <div class="col-lg-4 d-flex align-items-center justify-content-lg-end">
                                        <label class="form-label text-left me-3 mb-0"><?php esc_html_e( 'Ativar webhook', 'fc-recovery-carts' ); ?></label>
                                        <input type="checkbox" class="toggle-switch" name="webhooks[<?php echo esc_attr( $event_key ); ?>][<?php echo esc_attr( $index ); ?>][enabled]" value="yes" <?php checked( $enabled === 'yes' ); ?> />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-left"><?php esc_html_e( 'URL de entrega *', 'fc-recovery-carts' ); ?></label>
                                    <input type="url" class="form-control" name="webhooks[<?php echo esc_attr( $event_key ); ?>][<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" placeholder="<?php esc_attr_e( 'https://exemplo.com/webhook', 'fc-recovery-carts' ); ?>">
                                </div>

                                <div class="fcrc-webhook-headers-wrapper">
                                    <div class="alert alert-secondary fcrc-no-headers<?php echo ! empty( $headers ) ? ' d-none' : ''; ?>">
                                        <?php esc_html_e( 'Nenhum cabeçalho personalizado adicionado.', 'fc-recovery-carts' ); ?>
                                    </div>

                                    <div class="fcrc-webhook-headers" data-event="<?php echo esc_attr( $event_key ); ?>" data-index="<?php echo esc_attr( $index ); ?>" data-next-header-index="<?php echo esc_attr( $next_header_index ); ?>">
                                        <?php foreach ( $headers as $header_index => $header ) :
                                            $header_name = $header['name'] ?? '';
                                            $header_value = $header['value'] ?? '';
                                        ?>
                                            <div class="row g-3 align-items-end fcrc-webhook-header-row" data-header-index="<?php echo esc_attr( $header_index ); ?>">
                                                <div class="col-md-5">
                                                    <label class="form-label text-left"><?php esc_html_e( 'Chave do cabeçalho', 'fc-recovery-carts' ); ?></label>
                                                    <input type="text" class="form-control" name="webhooks[<?php echo esc_attr( $event_key ); ?>][<?php echo esc_attr( $index ); ?>][headers][<?php echo esc_attr( $header_index ); ?>][name]" value="<?php echo esc_attr( $header_name ); ?>" placeholder="<?php esc_attr_e( 'Ex: Authorization', 'fc-recovery-carts' ); ?>">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label text-left"><?php esc_html_e( 'Valor do cabeçalho', 'fc-recovery-carts' ); ?></label>
                                                    <input type="text" class="form-control" name="webhooks[<?php echo esc_attr( $event_key ); ?>][<?php echo esc_attr( $index ); ?>][headers][<?php echo esc_attr( $header_index ); ?>][value]" value="<?php echo esc_attr( $header_value ); ?>" placeholder="<?php esc_attr_e( 'Ex: Bearer 123456', 'fc-recovery-carts' ); ?>">
                                                </div>
                                                <div class="col-md-2 text-md-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm fcrc-remove-webhook-header">
                                                        <?php esc_html_e( 'Remover', 'fc-recovery-carts' ); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <button type="button" class="btn btn-outline-secondary btn-sm mt-3 fcrc-add-webhook-header" data-event="<?php echo esc_attr( $event_key ); ?>" data-index="<?php echo esc_attr( $index ); ?>">
                                        <?php esc_html_e( 'Adicionar cabeçalho', 'fc-recovery-carts' ); ?>
                                    </button>
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm fcrc-remove-webhook">
                                    <?php esc_html_e( 'Remover webhook', 'fc-recovery-carts' ); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script type="text/template" id="fcrc-webhook-template">
        <div class="card mb-4 fcrc-webhook-item" data-index="__index__">
            <div class="card-body">
                <div class="row g-3 align-items-center mb-4">
                    <div class="col-lg-8">
                        <label class="form-label text-left"><?php echo esc_html__( 'Nome interno', 'fc-recovery-carts' ); ?></label>
                        <input type="text" class="form-control" name="webhooks[__event__][__index__][name]" placeholder="<?php echo esc_attr__( 'Identificação opcional para o webhook', 'fc-recovery-carts' ); ?>">
                    </div>
                    <div class="col-lg-4 d-flex align-items-center justify-content-lg-end">
                        <label class="form-label text-left me-3 mb-0"><?php echo esc_html__( 'Ativar webhook', 'fc-recovery-carts' ); ?></label>
                        <input type="checkbox" class="toggle-switch" name="webhooks[__event__][__index__][enabled]" value="yes" checked />
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-left"><?php echo esc_html__( 'URL de entrega *', 'fc-recovery-carts' ); ?></label>
                    <input type="url" class="form-control" name="webhooks[__event__][__index__][url]" placeholder="<?php echo esc_attr__( 'https://exemplo.com/webhook', 'fc-recovery-carts' ); ?>">
                </div>

                <div class="fcrc-webhook-headers-wrapper">
                    <div class="alert alert-secondary fcrc-no-headers">
                        <?php echo esc_html__( 'Nenhum cabeçalho personalizado adicionado.', 'fc-recovery-carts' ); ?>
                    </div>

                    <div class="fcrc-webhook-headers" data-event="__event__" data-index="__index__" data-next-header-index="0"></div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mt-3 fcrc-add-webhook-header" data-event="__event__" data-index="__index__">
                        <?php echo esc_html__( 'Adicionar cabeçalho', 'fc-recovery-carts' ); ?>
                    </button>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <button type="button" class="btn btn-outline-danger btn-sm fcrc-remove-webhook">
                    <?php echo esc_html__( 'Remover webhook', 'fc-recovery-carts' ); ?>
                </button>
            </div>
        </div>
    </script>

    <script type="text/template" id="fcrc-webhook-header-template">
        <div class="row g-3 align-items-end fcrc-webhook-header-row" data-header-index="__header_index__">
            <div class="col-md-5">
                <label class="form-label text-left"><?php echo esc_html__( 'Chave do cabeçalho', 'fc-recovery-carts' ); ?></label>
                <input type="text" class="form-control" name="webhooks[__event__][__index__][headers][__header_index__][name]" placeholder="<?php echo esc_attr__( 'Ex: Authorization', 'fc-recovery-carts' ); ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label text-left"><?php echo esc_html__( 'Valor do cabeçalho', 'fc-recovery-carts' ); ?></label>
                <input type="text" class="form-control" name="webhooks[__event__][__index__][headers][__header_index__][value]" placeholder="<?php echo esc_attr__( 'Ex: Bearer 123456', 'fc-recovery-carts' ); ?>">
            </div>
            <div class="col-md-2 text-md-end">
                <button type="button" class="btn btn-outline-danger btn-sm fcrc-remove-webhook-header">
                    <?php echo esc_html__( 'Remover', 'fc-recovery-carts' ); ?>
                </button>
            </div>
        </div>
    </script>
</div>