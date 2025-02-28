<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Admin;

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
                'label' => esc_html__('Integrações', 'joinotify'),
                'icon' => '<svg class="fc-recovery-carts-tab-icon"><path d="M3 8h2v5c0 2.206 1.794 4 4 4h2v5h2v-5h2c2.206 0 4-1.794 4-4V8h2V6H3v2zm4 0h10v5c0 1.103-.897 2-2 2H9c-1.103 0-2-.897-2-2V8zm0-6h2v3H7zm8 0h2v3h-2z"></path></svg>',
                'file' => FC_RECOVERY_CARTS_INC . 'Views/Settings/Tabs/Integrations.php',
            ),
        ));
    }
}