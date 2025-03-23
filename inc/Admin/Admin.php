<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Admin actions class
 * 
 * @since 1.0.0
 * @version 1.1.0
 * @package MeuMouse.com
 */
class Admin {

    /**
     * Construct function
     * 
     * @since 1.0.0
     * @version 1.1.0
     * @return void
     */
    public function __construct() {
        // add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // update default options on admin_init
        add_action( 'admin_init', array( $this, 'update_default_options' ) );

        // render settings tabs
        add_action( 'Flexify_Checkout/Recovery_Carts/Settings/Nav_Tabs', array( $this, 'render_settings_tabs' ) );

        // register new post type
        add_action( 'init', array( $this, 'register_post_type' ) );

        // add screen options to carts table
        add_filter( 'set-screen-option', array( $this, 'set_screen_options' ), 10, 3 );
    }

    
    /**
     * Add admin menu
     * 
     * @since 1.0.0
     * @version 1.1.0
     * @return void
     */
    public function add_admin_menu() {
        global $fc_recovery_carts_hook;

        $fc_recovery_carts_hook = add_menu_page(
            esc_html__( 'Recuperação de carrinhos abandonados', 'fc-recovery-carts' ), // label
            esc_html__( 'Carrinhos abandonados', 'fc-recovery-carts' ), // menu label
            'manage_options', // capatibilities
            'fc-recovery-carts', // slug
            array( $this, 'carts_table_page' ), // callback
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 848.15 848.15"><defs><style>.cls-1{fill:#fff;}</style></defs><path class="cls-1" d="M514,116.38c-234.22,0-424.08,189.87-424.08,424.07S279.74,964.53,514,964.53,938,774.67,938,540.45,748.17,116.38,514,116.38Zm171.38,426.1c-141.76.37-257.11,117.69-257.4,259.45H339.72c0-191.79,153.83-347.42,345.62-347.42Zm0-176.64c-141.76.19-266.84,69.9-346,176.13V410.6C431,328.12,551.92,277.5,685.34,277.5Z" transform="translate(-89.88 -116.38)"/></svg>'),
            5, // menu priority
        );

        add_action( "load-{$fc_recovery_carts_hook}", array( $this, 'load_screen_options' ) );

        if ( self::is_pro() ) {
            // Main page as first submenu item with a different name
            add_submenu_page(
                'fc-recovery-carts', // parent page slug
                esc_html__( 'Todos os carrinhos', 'fc-recovery-carts' ), // page title
                esc_html__( 'Todos os carrinhos', 'fc-recovery-carts' ), // submenu title
                'manage_options', // user capabilities
                'fc-recovery-carts', // page slug (same as the main menu page)
                array( $this, 'carts_table_page' ) // callback
            );

            // settings page
            add_submenu_page(
                'fc-recovery-carts', // parent page slug
                esc_html__( 'Configurações', 'fc-recovery-carts' ), // page title
                esc_html__( 'Configurações', 'fc-recovery-carts' ), // submenu title
                'manage_options', // user capabilities
                'fc-recovery-carts-settings', // page slug
                array( $this, 'render_settings_page' ) // callback
            );
        } else {
            add_submenu_page(
                'fc-recovery-carts', // parent page slug
                esc_html__( 'Configurações', 'fc-recovery-carts' ), // page title
                esc_html__( 'Configurações', 'fc-recovery-carts' ), // submenu title
                'manage_options', // user capabilities
                'fc-recovery-carts-settings', // page slug
                array( $this, 'render_settings_page_required_license' ) // callback
            );
        }
    }


    /**
     * Load screen options for carts table
     * 
     * @since 1.1.0
     * @return void
     */
    public function load_screen_options() {
        $screen = get_current_screen();

        if ( ! is_object( $screen ) || $screen->id !== 'toplevel_page_fc-recovery-carts' ) {
            return;
        }

        $args = array(
            'label' => __('Itens por página', 'fc-recovery-carts'),
            'default' => 20,
            'option' => 'fc_recovery_carts_per_page',
        );

        add_screen_option( 'per_page', $args );

        new \MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Carts_Table();
    }


    /**
     * Render menu page settings
     * 
     * @since 1.0.0
     * @return void
     */
    public function render_settings_page() {
        include FC_RECOVERY_CARTS_INC . 'Views/Settings.php';
    }


    /**
     * Render settings page for not Pro users
     * 
     * @since 1.0.0
     * @return void
     */
    public function render_settings_page_required_license() {
        include FC_RECOVERY_CARTS_INC . 'Views/Settings_Info.php';
    }


    /**
     * Display table with all carts
     * 
     * @since 1.0.0
     * @version 1.1.0
     * @return void
     */
    public function carts_table_page() {
        global $fc_recovery_carts_table;

        if ( empty( $fc_recovery_carts_table ) ) {
            $fc_recovery_carts_table = new \MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Carts_Table();
        }

        $fc_recovery_carts_table->prepare_items();
        $fc_recovery_carts_table->display_page();
    }


    /**
     * Handles saving and loading screen options
     * 
     * @since 1.1.0
     * @param mixed $status | The current status of the screen option
     * @param string $option | The option name
     * @param mixed $value | The option value
     * @return mixed
     */
    public function set_screen_options( $status, $option, $value ) {
        if ( $option === 'fc_recovery_carts_per_page' ) {
            return (int) $value;
        }

        return $status;
    }


    /**
     * Set default options
     * 
     * @since 1.0.0
     * @version 1.1.0
     * @return array
     */
    public static function set_default_options() {
        // get current payment methods
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        $payment_methods = array();

        foreach ( $payment_gateways as $gateway_id => $gateway ) {
            $payment_methods[$gateway_id] = array(
                'delay_time' => 5,
                'delay_unit' => 'minutes',
            );
        }

        return apply_filters( 'Flexify_Checkout/Recovery_Carts/Set_Default_Options', array(
            'time_for_lost_carts' => 15,
            'time_unit_for_lost_carts' => 'minutes',
            'toggle_switchs' => array(
                'enable_modal_add_to_cart' => 'yes',
                'enable_international_phone_modal' => 'yes',
                'enable_joinotify_integration' => 'yes',
                'enable_email_integration' => 'no',
                'display_modal_for_logged_users' => 'no',
                'enable_get_location_from_ip' => 'yes',
            ),
            'follow_up_events' => array(
                'mensagem_em_1_hora' => array(
                    'title' => 'Mensagem em 1 hora',
                    'message' => "*{{ first_name }}, você esqueceu algo no carrinho?*\n\nOi {{ first_name }}, vimos que você adicionou produtos ao carrinho, mas não finalizou a compra. Eles ainda estão reservados para você! 😊\n\nFinalize seu pedido agora: {{ recovery_link }}\n\nSe precisar de ajuda, estamos por aqui!",
                    'delay_time' => 1,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'no',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => '',
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => '',
                        'expiration_time_unit' => '',
                        'limit_usages' => '',
                        'limit_usages_per_user' => '',
                    ),
                ),
                'mensagem_em_3_horas' => array(
                    'title' => 'Mensagem em 3 horas',
                    'message' => "*🔥 Seus itens ainda estão disponíveis!* \n\n{{ first_name }}, seu carrinho ainda está esperando por você! Mas não podemos garantir que os estoques durem muito tempo. \n\nAproveite e finalize sua compra agora: {{ recovery_link }}\n\nQualquer dúvida, estamos à disposição!",
                    'delay_time' => 3,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'no',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => '',
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => '',
                        'expiration_time_unit' => '',
                        'limit_usages' => '',
                        'limit_usages_per_user' => '',
                    ),
                ),
                'mensagem_em_5_horas' => array(
                    'title' => 'Mensagem em 5 horas',
                    'message' => "*🛍️ Não perca essa chance, {{ first_name }}!* \n\nAinda está interessado nos produtos do seu carrinho? Para te dar um empurrãozinho, conseguimos um *cupom especial de 5% de desconto* para você finalizar sua compra.\n\nUse o código *{{ coupon_code }}* e garanta já: {{ recovery_link }}\n\nMas corra, pois esse desconto expira em 1 hora! ⏳",
                    'delay_time' => 5,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'yes',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => 5,
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => 1,
                        'expiration_time_unit' => 'hours',
                        'limit_usages' => 1,
                        'limit_usages_per_user' => 1,
                    ),
                ),
                'mensagem_em_8_horas' => array(
                    'title' => 'Mensagem em 8 horas',
                    'message' => "*🚀 Última chance antes do estoque acabar!* \n\n{{ first_name }}, alguns itens do seu carrinho estão com *baixa disponibilidade*! Não deixe para depois.\n\nSe precisar de ajuda para concluir sua compra, estamos aqui para te auxiliar.\n\n🔗 Finalize agora: {{ recovery_link }}",
                    'delay_time' => 8,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'no',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => '',
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => '',
                        'expiration_time_unit' => '',
                        'limit_usages' => '',
                        'limit_usages_per_user' => '',
                    ),
                ),
                'mensagem_em_24_horas' => array(
                    'title' => 'Mensagem em 24 horas',
                    'message' => "*🎁 Oferta exclusiva para você, {{ first_name }}!* \n\nNotamos que você não finalizou sua compra e queremos te ajudar! Como um incentivo, liberamos um *cupom especial de 10% de desconto*.\n\nUse o código *{{ coupon_code }}*. *Atenção! Este cupom expira em 1 hora!*\n\nFinalize sua compra pelo link: {{ recovery_link }}\n\n📌 Estamos à disposição caso tenha alguma dúvida!",
                    'delay_time' => 24,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                    'coupon' => array(
                        'enabled' => 'yes',
                        'generate_coupon' => 'yes',
                        'coupon_prefix' => 'CUPOM_',
                        'coupon_code' => 'none',
                        'discount_type' => 'percent',
                        'discount_value' => 10,
                        'allow_free_shipping' => 'yes',
                        'expiration_time' => 1,
                        'expiration_time_unit' => 'hours',
                        'limit_usages' => 1,
                        'limit_usages_per_user' => 1,
                    ),
                ),
            ),
            'primary_color' => '#008aff',
            'select_coupon' => 'none',
            'payment_methods' => $payment_methods,
            'joinotify_sender_phone' => 'none',
            'fallback_first_name' => 'Cliente',
            'collect_lead_modal' => array(
                'title' => 'Registre-se para receber um cupom de desconto e ficar por dentro das melhores ofertas!',
                'button_title' => 'Receber meu cupom',
                'message' => "Oi, {{ first_name }}! Aqui está seu cupom para usar em sua próxima compra 🎁:\n\n {{ coupon_code }}\n\nSe tiver qualquer dúvida estamos à disposição!",
                'triggers_list' => 'button[name="add-to-cart"], a.add_to_cart_button, a.ajax_add_to_cart, #wd-add-to-cart',
                'coupon' => array(
                    'enabled' => 'yes',
                    'generate_coupon' => 'yes',
                    'coupon_prefix' => 'CUPOM_',
                    'coupon_code' => 'none',
                    'discount_type' => 'percent',
                    'discount_value' => 5,
                    'allow_free_shipping' => 'yes',
                    'expiration_time' => '',
                    'expiration_time_unit' => '',
                    'limit_usages' => 1,
                    'limit_usages_per_user' => 1,
                ),
            ),
            'fcrc_heartbeat_interval' => 30,
        ));
    }


    /**
     * Gets the items from the array and inserts them into the option if it is empty,
     * or adds new items with default value to the option
     * 
     * @since 1.0.0
     * @return void
     */
    public function update_default_options() {
        $get_options = self::set_default_options();
        $default_options = get_option('flexify_checkout_recovery_carts_settings', array());

        if ( empty( $default_options ) ) {
            update_option( 'flexify_checkout_recovery_carts_settings', $get_options );
        } else {
            $merged_options = array_replace_recursive( $get_options, $default_options );

            update_option( 'flexify_checkout_recovery_carts_settings', $merged_options );
        }
    }


    /**
     * Checks if the option exists and returns the indicated array item
     * 
     * @since 1.0.0
     * @param string $key | Option key
     * @return mixed | string or false
     */
    public static function get_setting( $key ) {
        $options = get_option('flexify_checkout_recovery_carts_settings', array());

        // check if array key exists and return key
        if ( isset( $options[$key] ) ) {
            return $options[$key];
        }

        return false;
    }


    /**
     * Get switch option value
     * 
     * @since 1.0.0
     * @param string $key | Option key
     * @return string
     */
    public static function get_switch( $key ) {
        $options = get_option('flexify_checkout_recovery_carts_settings', array());

        // check if array key exists and return key
        if ( isset( $options['toggle_switchs'][$key] ) ) {
            return $options['toggle_switchs'][$key];
        }

        return false;
    }


    /**
     * Render settings nav tabs
     *
     * @since 1.0.0
     */
    public function render_settings_tabs() {
        $tabs = Components::get_settings_tabs();

        foreach ( $tabs as $tab ) {
            printf( '<a href="#%1$s" class="nav-tab">%2$s %3$s</a>', esc_attr( $tab['id'] ), $tab['icon'], $tab['label'] );
        }
    }


    /**
     * Register "fc-recovery-carts" post type
     * 
     * @since 1.0.0
     * @version 1.1.0
     * @return void
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x( 'Carrinhos', 'post type general name', 'fc-recovery-carts' ),
            'singular_name'      => _x( 'Carrinho', 'post type singular name', 'fc-recovery-carts' ),
            'menu_name'          => _x( 'Carrinhos', 'admin menu', 'fc-recovery-carts' ),
            'name_admin_bar'     => _x( 'Carrinho', 'add new on admin bar', 'fc-recovery-carts' ),
            'add_new'            => _x( 'Adicionar novo', 'carrinho', 'fc-recovery-carts' ),
            'add_new_item'       => __( 'Adicionar novo carrinho', 'fc-recovery-carts' ),
            'new_item'           => __( 'Novo carrinho', 'fc-recovery-carts' ),
            'edit_item'          => __( 'Editar carrinho', 'fc-recovery-carts' ),
            'view_item'          => __( 'Ver carrinho', 'fc-recovery-carts' ),
            'all_items'          => __( 'Todos os carrinhos', 'fc-recovery-carts' ),
            'search_items'       => __( 'Pesquisar carrinhos', 'fc-recovery-carts' ),
            'parent_item_colon'  => __( 'Carrinho pai:', 'fc-recovery-carts' ),
            'not_found'          => __( 'Nenhum carrinho encontrado.', 'fc-recovery-carts' ),
            'not_found_in_trash' => __( 'Nenhum carrinho encontrado na lixeira.', 'fc-recovery-carts' )
        );
    
        $args = array(
            'labels'             => $labels,
            'description'        => __( 'Descrição.', 'fc-recovery-carts' ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => false,
            'show_in_menu'       => false,
            'query_var'          => true,
            'capability_type'    => 'post',
            'rewrite'            => array( 'slug' => '/fc-recovery-carts', 'with_front' => false ),
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' )
        );
    
        register_post_type( 'fc-recovery-carts', $args );

        $custom_statuses = array( 'lead', 'shopping', 'abandoned', 'order_abandoned', 'recovered', 'lost', 'purchased' );

        foreach ( $custom_statuses as $status ) {
            register_post_status( $status, array(
                'label'                     => ucfirst( $status ),
                'public'                    => true,
                'internal'                  => false,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( ucfirst( $status ) . ' <span class="count">(%s)</span>', ucfirst( $status ) . ' <span class="count">(%s)</span>' ),
            ));
        }

        // update permacarrinhos
        flush_rewrite_rules();
    }


    /**
     * Check if plugin Flexify Checkout is Pro
     * 
     * @since 1.0.0
     * @return bool
     */
    public static function is_pro() {
        $get_status = get_option( 'flexify_checkout_license_status', 'invalid' );

        if ( $get_status === 'valid' ) {
            return true;
        }

        return false;
    }
}