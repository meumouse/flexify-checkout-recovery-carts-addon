<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Admin;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Admin actions class
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Admin {

    /**
     * Construct function
     * 
     * @since 1.0.0
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
    }


    /**
     * Add admin menu
     * 
     * @since 1.0.0
     * @return void
     */
    public function add_admin_menu() {
        add_menu_page(
            esc_html__( 'Recuperação de carrinhos abandonados', 'fc-recovery-carts' ), // label
            esc_html__( 'Carrinhos abandonados', 'fc-recovery-carts' ), // menu label
            'manage_options', // capatibilities
            'fc-recovery-carts', // slug
            array( $this, 'carts_table_page' ), // callback
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 703 882.5"><path d="M908.66,248V666a126.5,126.5,0,0,1-207.21,97.41l-16.7-16.7L434.08,496.07l-62-62a47.19,47.19,0,0,0-72,30.86V843.36a47.52,47.52,0,0,0,69.57,35.22l19.3-19.3,56-56,81.19-81.19,10.44-10.44a47.65,47.65,0,0,1,67.63,65.05l-13,13L428.84,952.12l-9.59,9.59a128,128,0,0,1-213.59-95.18V413.17a124.52,124.52,0,0,1,199.78-82.54l22.13,22.13L674.45,599.64l46.22,46.22,17,17a47.8,47.8,0,0,0,71-31.44V270.19a48.19,48.19,0,0,0-75-40.05L720.43,243.4l-68.09,68.09L575.7,388.13a48.39,48.39,0,0,1-67.43-67.93L680,148.46A136,136,0,0,1,908.66,248Z" transform="translate(-205.66 -112.03)" style="fill:#fff"/></svg>'),
            5, // menu priority
        );

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
     * Display table with all carts
     * 
     * @since 1.0.0
     * @return void
     */
    public function carts_table_page() {
        $cart_table = new \MeuMouse\Flexify_Checkout\Recovery_Carts\Core\Carts_Table();
        $cart_table->prepare_items();

        echo '<div class="wrap"><h1 class="wp-heading-inline">' . __( 'Gerenciar carrinhos', 'fc-recovery-carts' ) . '</h1>';

        echo '<form method="post">';
            $cart_table->display();
        echo '</form></div>';
    }


    /**
     * Set default options
     * 
     * @since 1.0.0
     * @return array
     */
    public static function set_default_options() {
        return apply_filters( 'Flexify_Checkout/Recovery_Carts/Set_Default_Options', array(
            'default_time_for_lost_orders' => 10,
            'toggle_switchs' => array(
                
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
                ),
                'mensagem_em_5_horas' => array(
                    'title' => 'Mensagem em 5 horas',
                    'message' => "*🛍️ Não perca essa chance, {{ first_name }}!* \n\nAinda está interessado nos produtos do seu carrinho? Para te dar um empurrãozinho, conseguimos um *cupom especial de 5% de desconto* para você finalizar sua compra.\n\nUse o código *RECUPERA5* e garanta já: {{ recovery_link }}\n\nMas corra, pois esse desconto expira em breve! ⏳",
                    'delay_time' => 5,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
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
                ),
                'mensagem_em_24_horas' => array(
                    'title' => 'Mensagem em 24 horas',
                    'message' => "*🎁 Oferta exclusiva para você, {{ first_name }}!* \n\nNotamos que você não finalizou sua compra e queremos te ajudar! Como um incentivo, liberamos um *cupom especial de 10% de desconto*.\n\nUse o código *ULTIMACHANCE10* antes que ele expire e conclua sua compra agora: {{ recovery_link }}\n\n📌 Estamos à disposição para qualquer dúvida!",
                    'delay_time' => 24,
                    'delay_type' => 'hours',
                    'channels' => array(
                        'email' => 'no',
                        'whatsapp' => 'yes',
                    ),
                ),
            ),
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
            foreach ( $get_options as $key => $value ) {
                if ( ! isset( $default_options[$key] ) ) {
                    $default_options[$key] = $value;
                }
            }

            update_option( 'flexify_checkout_recovery_carts_settings', $default_options );
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
        return self::get_setting('toggle_switchs')[$key];
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

        // update permacarrinhos
        flush_rewrite_rules();
    }
}