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
                    <input type="checkbox" id="enable_modal_add_to_cart" class="toggle-switch" name="enable_modal_add_to_cart" <?php checked( Admin::get_switch('enable_modal_add_to_cart') === 'yes' ); ?> />
                </td>
            </tr>
        </tbody>
    </table>
</div>