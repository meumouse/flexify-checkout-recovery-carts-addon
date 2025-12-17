<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

use Automattic\WooCommerce\Utilities\FeaturesUtil;

use Exception;
use ReflectionClass;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Main plugin initialization class
 * 
 * Handles all initialization logic previously in the main plugin file
 * 
 * @since 1.0.0
 * @version 1.3.5
 * @package MeuMouse\Flexify_Checkout\Recovery_Carts\Core
 * @author MeuMouse.com
 */
class Init {

    /**
     * Plugin version
     * 
     * @since 1.0.0
     * @version 1.3.5
     * @return string
     */
    private static $version = '1.3.6';

    /**
     * Plugin basename
     * 
     * @since 1.3.0
     * @return string
     */
    public $basename;

    /**
     * Plugin instance
     * 
     * @since 1.3.3
     * @return object Init
     */
    private static $instance = null;

    /**
     * Array of instanced classes to prevent multiple instances
     * 
     * @since 1.3.4
     * @return array
     */
    private static $instanced_classes = array();

    /**
     * Plugin constants defined
     * 
     * @since 1.3.5
     * @return bool
     */
    private static $constants_defined = false;


    /**
     * Get plugin instance (Singleton pattern)
     * 
     * @since 1.3.3
     * @version 1.3.5
     * @return object Init
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Constructor
     * 
     * @since 1.0.0
     * @version 1.3.5
     * @return void
     */
    public function __construct() {
        // Hook before initialization
        do_action('before_fc_recovery_carts_init');
        
        // Setup plugin constants
        $this->setup_constants();

        if ( defined('FLEXIFY_CHECKOUT_VERSION') && version_compare( FLEXIFY_CHECKOUT_VERSION, '5.4.1', '>=' ) ) {
            // initialize the plugin after Flexify Checkout initialized
            add_action( 'Flexify_Checkout/Init', array( $this, 'init' ) );
        } else {
            // initialize the plugin after WooCommerce loaded
            add_action( 'woocommerce_loaded', array( $this, 'init' ), 99 );
        }
        
        // Set plugin basename
        $this->basename = plugin_basename( FC_RECOVERY_CARTS_FILE );
    }


    /**
     * Initialize the plugin
     * 
     * @since 1.3.5
     * @return void
     * @throws Exception If plugin requirements are not met
     */
    public function init() {
        try {
            // Check PHP version requirement
            $this->check_php_version();
            
            // Check plugin dependencies
            $this->check_dependencies();

            // add plugin functions
            $this->include_functions();
            
            // Load text domain
            load_plugin_textdomain( 'fc-recovery-carts', false, dirname( $this->basename ) . '/languages/' );
            
            // Add plugin action links
            add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'add_action_plugin_links' ), 10, 4 );
            
            // Add plugin row meta links
            add_filter( 'plugin_row_meta', array( $this, 'add_row_meta_links' ), 10, 4 );
            
            // Setup HPOS compatibility
            add_action( 'before_woocommerce_init', array( $this, 'setup_hpos_compatibility' ) );
            
            // Instance classes after plugins are loaded
            add_action( 'plugins_loaded', array( $this, 'instance_classes' ), 99 );

            // Hook after successful initialization
            do_action('fc_recovery_carts_init');
        } catch ( Exception $e ) {
            // Rethrow exception for main file to handle
            throw $e;
        }
    }


    /**
     * Check PHP version requirement
     * 
     * @since 1.3.5
     * @return void
     * @throws Exception If PHP version is insufficient
     */
    private function check_php_version() {
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            throw new Exception(
                sprintf(
                    /* translators: %s: Required PHP version */
                    esc_html__(
                        'Requer PHP %s ou superior.',
                        'fc-recovery-carts'
                    ),
                    '7.4'
                )
            );
        }
    }


    /**
     * Check plugin dependencies
     * 
     * @since 1.3.5
     * @return void
     * @throws Exception If dependencies are not met
     */
    private function check_dependencies() {
        // Load WordPress plugin functions if needed
        if ( ! function_exists('is_plugin_active') ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        // Check if WooCommerce is active
        if ( ! is_plugin_active('woocommerce/woocommerce.php') ) {
            throw new Exception(
                esc_html__( 'Requer WooCommerce para funcionar.', 'fc-recovery-carts' )
            );
        }
        
        // Check if Flexify Checkout is active and meets version requirement
        if ( ! is_plugin_active('flexify-checkout-for-woocommerce/flexify-checkout-for-woocommerce.php') ) {
            throw new Exception(
                esc_html__( 'Requer o plugin Flexify Checkout para WooCommerce.', 'fc-recovery-carts' )
            );
        }
        
        // Check Flexify Checkout version if constant is defined
        if ( defined('FLEXIFY_CHECKOUT_VERSION') && version_compare( FLEXIFY_CHECKOUT_VERSION, '5.4.1', '<' ) ) {
            throw new Exception(
                sprintf(
                    /* translators: %s: Required Flexify Checkout version */
                    esc_html__( 'Requer Flexify Checkout versão %s ou superior.', 'fc-recovery-carts' ), '5.4.1'
                )
            );
        }
    }


    /**
     * Setup plugin constants
     * 
     * @since 1.0.0
     * @version 1.3.5
     * @return void
     */
    private function setup_constants() {
        if ( self::$constants_defined ) {
            return;
        }
        
        $base_file = dirname( __DIR__, 2 ) . '/flexify-checkout-recovery-carts-addon.php';
        $base_dir = plugin_dir_path( $base_file );
        $base_url = plugin_dir_url( $base_file );
        
        $constants = array(
            'FC_RECOVERY_CARTS_BASENAME'    => plugin_basename( $base_file ),
            'FC_RECOVERY_CARTS_FILE'        => $base_file,
            'FC_RECOVERY_CARTS_DIR'         => $base_dir,
            'FC_RECOVERY_CARTS_INC'         => $base_dir . 'inc/',
            'FC_RECOVERY_CARTS_URL'         => $base_url,
            'FC_RECOVERY_CARTS_ASSETS'      => $base_url . 'assets/',
            'FC_RECOVERY_CARTS_ABSPATH'     => dirname( $base_file ) . '/',
            'FC_RECOVERY_CARTS_SLUG'        => 'flexify-checkout-recovery-carts-addon',
            'FC_RECOVERY_CARTS_VERSION'     => self::$version,
            'FC_RECOVERY_CARTS_ADMIN_EMAIL' => get_option( 'admin_email' ),
            'FC_RECOVERY_CARTS_DOCS_URL'    => 'https://ajuda.meumouse.com/docs/fc-recovery-carts/overview',
            'FC_RECOVERY_CARTS_DEBUG_MODE'  => false,
        );
        
        foreach ( $constants as $key => $value ) {
            if ( ! defined( $key ) ) {
                define( $key, $value );
            }
        }
        
        self::$constants_defined = true;
    }


    /**
     * Include plugin functions
     * 
     * @since 1.3.5
     * @return void
     */
    private function include_functions() {
        // Include core functions
        if ( file_exists( FC_RECOVERY_CARTS_INC . 'Core/Functions.php' ) ) {
            include_once FC_RECOVERY_CARTS_INC . 'Core/Functions.php';
        }
    }


    /**
     * Instance classes after load Composer
     * 
     * @since 1.0.0
     * @version 1.3.5
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
        $this->auto_instance_classes();
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
            $reflection = new ReflectionClass( $class );
            
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
        } catch ( Exception $e ) {
            if ( defined('FC_RECOVERY_CARTS_DEBUG_MODE') && FC_RECOVERY_CARTS_DEBUG_MODE ) {
                error_log(
                    sprintf( 'Flexify Checkout Recovery Carts: Error instancing class %s - %s', $class, $e->getMessage() )
                );
            }
        }
    }


    /**
     * Plugin action links
     *
     * @since 1.0.0
     * @version 1.3.5
     * @param array $action_links Default plugin action links
     * @return array
     */
    public function add_action_plugin_links( $action_links ) {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=fc-recovery-carts-settings' ) . '">' . esc_html__( 'Configurações', 'fc-recovery-carts' ) . '</a>',
        );
        
        return array_merge( $plugin_links, $action_links );
    }


    /**
     * Add meta links on plugin
     *
     * @since 1.0.0
     * @version 1.3.5
     * @param array  $plugin_meta Plugin metadata
     * @param string $plugin_file Plugin file path
     * @param array  $plugin_data Plugin data
     * @param string $status      Plugin status
     * @return array
     */
    public function add_row_meta_links( $plugin_meta, $plugin_file, $plugin_data, $status ) {
        if ( strpos( $plugin_file, $this->basename ) !== false ) {
            $new_links = array(
                'docs' => '<a href="' . esc_attr( FC_RECOVERY_CARTS_DOCS_URL ) . '" target="_blank">' . esc_html__( 'Documentação', 'fc-recovery-carts' ) . '</a>',
            );
            
            $plugin_meta = array_merge( $plugin_meta, $new_links );
        }
        
        return $plugin_meta;
    }


    /**
     * Setup HPOS compatibility
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
        _doing_it_wrong(  __FUNCTION__, esc_html__( 'Trapaceando?', 'fc-recovery-carts' ), '1.3.3' );
    }
}