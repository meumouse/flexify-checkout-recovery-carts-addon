<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use Automattic\WooCommerce\Utilities\FeaturesUtil;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class for initialize classes
 * 
 * @since 1.0.0
 * @version 1.3.4
 * @package MeuMouse.com
 */
class Init {

    /**
     * Get plugin basename
     * 
     * @since 1.3.0
     * @return string
     */
    public $basename = FC_RECOVERY_CARTS_BASENAME;

    /**
     * Plugin instance.
     * 
     * @since 1.3.3
     * @var Init
     */
    private static $instance = null;

    /**
     * Array of instanced classes to prevent multiple instances.
     * 
     * @since 1.3.4
     * @var array
     */
    private static $instanced_classes = array();


    /**
     * Get plugin instance
     * 
     * @since 1.3.3
     * @return Init
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Construct function
     * 
     * @since 1.0.0
     * @version 1.3.4
     * @return void
     */
    public function __construct() {
        // load WordPress plugin class if function is_plugin_active() is not defined
        if ( ! function_exists('is_plugin_active') ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        // check if flexify checkout is active
        if ( ! is_plugin_active('flexify-checkout-for-woocommerce/flexify-checkout-for-woocommerce.php') ) {
            add_action( 'admin_notices', array( $this, 'plugin_dependency_notice' ) );
            return;
        }

        // include plugin functions
        include_once( FC_RECOVERY_CARTS_INC . 'Core/Functions.php' );

        // load text domain
        load_plugin_textdomain( 'fc-recovery-carts', false, dirname( $this->basename ) . '/languages/' );

        // add link to plugin settings
        add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'add_action_plugin_links' ), 10, 4 );
        
        // add helper links
        add_filter( 'plugin_row_meta', array( $this, 'add_row_meta_links' ), 10, 4 );

        // set compatibility with WooCommerce HPOS (High-Performance Order Storage)
        add_action( 'before_woocommerce_init', array( $this, 'setup_hpos_compatibility' ) );

        // instance classes after WordPress is fully loaded
        add_action( 'plugins_loaded', array( $this, 'instance_classes' ) );

        // hook after init plugin
        do_action('fc_recovery_carts_init');
    }


    /**
     * Flexify Checkout requires notice
     * 
     * @since 1.0.0
     * @version 1.3.0
     * @return void
     */
    public function plugin_dependency_notice() {
        $class = 'notice notice-error is-dismissible';
        $message = __( '<strong>Flexify Checkout - Recuperação de carrinhos abandonados</strong> requer o plugin Flexify Checkout para WooCommerce.', 'fc-recovery-carts' );

        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
    }


    /**
     * Plugin action links
     *
     * @since 1.0.0
     * @version 1.3.0
     * @param array $action_links | Default plugin action links
     * @return array
     */
    public function add_action_plugin_links( $action_links ) {
        $plugin_links = array(
            '<a href="'. admin_url('admin.php?page=fc-recovery-carts-settings') .'">'. esc_html__( 'Configurações', 'fc-recovery-carts' ) .'</a>',
        );

        return array_merge( $plugin_links, $action_links );
    }


    /**
     * Add meta links on plugin
     *
     * @since 1.0.0
     * @version 1.3.0
     * @param array $plugin_meta | Plugin metadata
     * @param string $plugin_file | Plugin file path
     * @param array $plugin_data | Plugin data
     * @param string $status | Plugin status
     * @return array
     */
    public function add_row_meta_links( $plugin_meta, $plugin_file, $plugin_data, $status ) {
        if ( strpos( $plugin_file, $this->basename ) !== false ) {
            $new_links = array(
                'docs' => '<a href="'. esc_attr( FC_RECOVERY_CARTS_DOCS_URL ) .'" target="_blank">'. esc_html__( 'Documentação', 'fc-recovery-carts' ) .'</a>',
            );

            $plugin_meta = array_merge( $plugin_meta, $new_links );
        }

        return $plugin_meta;
    }


    /**
     * Instance classes after load Composer
     * 
     * @since 1.0.0
     * @version 1.3.4
     * @return void
     */
    public function instance_classes() {
        // Check WooCommerce dependency
        if ( ! class_exists('WooCommerce') ) {
            return;
        }

        // Legacy instance for compatibility
        $legacy_classes = apply_filters( 'Flexify_Checkout/Recovery_Carts/Instance_Classes', array() );

        foreach ( $legacy_classes as $class ) {
            if ( class_exists( $class ) && ! isset( self::$instanced_classes[ $class ] ) ) {
                $instance = new $class();
                self::$instanced_classes[ $class ] = $instance;
                
                // Call init method if exists
                if ( method_exists( $instance, 'init' ) ) {
                    $instance->init();
                }
            }
        }

        // Auto instance classes from Composer
        add_action( 'init', array( $this, 'auto_instance_classes' ) );
    }


    /**
     * Auto instance classes from Composer classmap
     * 
     * @since 1.3.3
     * @return void
     */
    public function auto_instance_classes() {
        $classmap_file = FC_RECOVERY_CARTS_DIR . 'vendor/composer/autoload_classmap.php';

        if ( ! file_exists( $classmap_file ) ) {
            return;
        }

        $classmap = include_once $classmap_file;

        // Ensure classmap is an array
        if ( ! is_array( $classmap ) ) {
            $classmap = array();
        }

        // Filter and instance classes
        $this->instance_filtered_classes( $classmap );
    }


    /**
     * Filter and instance classes
     * 
     * @since 1.3.4
     * @param array $classmap
     * @return void
     */
    private function instance_filtered_classes( $classmap ) {
        $filtered_classes = array_filter( $classmap, function( $file, $class ) {
            // Skip if not in our namespace
            if ( strpos( $class, 'MeuMouse\\Flexify_Checkout\\Recovery_Carts\\' ) !== 0 ) {
                return false;
            }

            // Skip abstract classes
            if ( strpos( $class, 'Abstract' ) !== false ) {
                return false;
            }
            
            // Skip interfaces
            if ( strpos( $class, 'Interface' ) !== false ) {
                return false;
            }
            
            // Skip traits
            if ( strpos( $class, 'Trait' ) !== false ) {
                return false;
            }
            
            // Skip Init class itself
            if ( $class === 'MeuMouse\\Flexify_Checkout\\Recovery_Carts\\Core\\Init' ) {
                return false;
            }

            // Check if class already instanced
            if ( isset( self::$instanced_classes[ $class ] ) ) {
                return false;
            }

            // Check if class exists
            if ( ! class_exists( $class ) ) {
                return false;
            }

            // Special handling for Views classes (WP_List_Table)
            if ( strpos( $class, 'MeuMouse\\Flexify_Checkout\\Recovery_Carts\\Views\\' ) !== false ) {
                // Only load these classes in admin context and when we're on our plugin pages
                if ( wp_doing_ajax() || ! is_admin() ) {
                    return false;
                }
                
                // Check if we're in the right context for WP_List_Table
                // These classes should only be instantiated on demand, not automatically
                return false;
            }
            
            return true;
            
        }, ARRAY_FILTER_USE_BOTH );

        foreach ( array_keys( $filtered_classes ) as $class ) {
            $this->safe_instance_class( $class );
        }
    }


    /**
     * Safely instance a class
     * 
     * @since 1.3.4
     * @param string $class
     * @return void
     */
    private function safe_instance_class( $class ) {
        // Check if class already instanced
        if ( isset( self::$instanced_classes[ $class ] ) ) {
            return;
        }

        try {
            $reflection = new \ReflectionClass( $class );
            
            if ( ! $reflection->isInstantiable() ) {
                return;
            }

            $constructor = $reflection->getConstructor();
            
            // Only instance classes without required constructor parameters
            if ( $constructor && $constructor->getNumberOfRequiredParameters() > 0 ) {
                return;
            }

            $instance = new $class();
            
            // Store instance to prevent multiple instances
            self::$instanced_classes[ $class ] = $instance;
            
            // Call init method if exists
            if ( method_exists( $instance, 'init' ) ) {
                $instance->init();
            }
            
        } catch ( \Exception $e ) {
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                error_log( 'Flexify Checkout Recovery Carts: Error instancing class ' . $class . ' - ' . $e->getMessage() );
            }
        }
    }


    /**
     * Instance Views classes on demand
     * 
     * @since 1.3.4
     * @param string $class_name
     * @return mixed|null
     */
    public static function instance_view_class( $class_name ) {
        // Only instance view classes in admin context
        if ( ! is_admin() ) {
            return null;
        }

        // Check if class already instanced
        if ( isset( self::$instanced_classes[ $class_name ] ) ) {
            return self::$instanced_classes[ $class_name ];
        }

        if ( class_exists( $class_name ) ) {
            try {
                $instance = new $class_name();
                self::$instanced_classes[ $class_name ] = $instance;
                return $instance;
            } catch ( \Exception $e ) {
                if ( defined('WP_DEBUG') && WP_DEBUG ) {
                    error_log( 'Flexify Checkout Recovery Carts: Error instancing view class ' . $class_name . ' - ' . $e->getMessage() );
                }
                return null;
            }
        }
        
        return null;
    }


    /**
     * Get instance of a specific class
     * 
     * @since 1.3.4
     * @param string $class_name
     * @return mixed|null
     */
    public static function get_class_instance( $class_name ) {
        if ( isset( self::$instanced_classes[ $class_name ] ) ) {
            return self::$instanced_classes[ $class_name ];
        }
        
        return null;
    }


    /**
     * Check if a class has been instanced
     * 
     * @since 1.3.4
     * @param string $class_name
     * @return bool
     */
    public static function is_class_instanced( $class_name ) {
        return isset( self::$instanced_classes[ $class_name ] );
    }


    /**
     * Setup HPOS compatibility.
     *
     * @since 1.3.4
     * @return void
     */
    public function setup_hpos_compatibility() {
        if ( class_exists( FeaturesUtil::class ) ) {
            FeaturesUtil::declare_compatibility( 'custom_order_tables', FC_RECOVERY_CARTS_FILE, true );
        }
    }


    /**
     * Cloning is forbidden
     *
     * @since 1.3.3
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Trapaceando?', 'fc-recovery-carts' ), '1.3.3' );
    }


    /**
     * Unserializing instances of this class is forbidden
     *
     * @since 1.3.3
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Trapaceando?', 'fc-recovery-carts' ), '1.3.3' );
    }
}