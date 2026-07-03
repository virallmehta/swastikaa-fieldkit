<?php
/**
 * Main plugin orchestrator. Bootstraps core systems, registers the Field Group CPT,
 * auto-discovers fields, and manages activation/deactivation routines.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Plugin {
    /**
     * Single instance.
     */
    protected static ?self $instance = null;

    public static function instance(): self {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
  
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'plugins_loaded', [ $this, 'load_fields' ] );

     }

    private function boot(): void {
        // 1. Core systems (always).
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'init', [ SWFK_Field_Registry::class, 'boot' ], 6 );
        // add_action( 'init', [ SF_Location_Rule_Registry::class, 'boot_core' ], 5 );

        // 2. Auto-discover fields.
        add_action( 'plugins_loaded', [ $this, 'load_fields' ], 9 );

        // 3. Admin only.
        if ( is_admin() ) {
            new SWFK_Admin();  // Handles metaboxes, saves, assets.
        }

        // 4. Activation/deactivation.
        register_activation_hook( SWFK_PLUGIN_FILE, [ $this, 'activate' ] );
        register_deactivation_hook( SWFK_PLUGIN_FILE, [ $this, 'deactivate' ] );
    }

    /**
     * Load auto-discovered fields.
     */
    public function load_fields(): void {
 
        SWFK_Field_Loader::load( SWFK_PLUGIN_DIR . 'fields' );
  
    }

    /**
     * Field Group CPT.
     */
    public function register_cpt(): void {
        register_post_type( 'swfk_field_group', [
            'label'               => 'SwastiNexus Fields Studio Groups',
            'labels'              => [
                'name'          => 'SwastiNexus Fields Studio Groups',
                'singular_name' => 'Field Group',
                'menu_name'     => 'SwastiNexus Fields Studio',
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => false,
            'supports'            => [ 'title' ],
            'menu_icon'           => 'dashicons-feedback',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'rewrite' => array( 'slug' => 'swfk' )
        ] );

        flush_rewrite_rules(); // Only during activation ideally.
    }

    public static function activate(): void {
        $plugin = self::instance();
        $plugin->register_cpt();
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}

// Global instance.
function SWFK_Plugin(): SWFK_Plugin {
    return SWFK_Plugin::instance();
}
