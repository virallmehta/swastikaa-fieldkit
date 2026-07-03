<?php
/**
 * Manages per-field-type CSS/JS asset registration and enqueueing.
 * Each field type may ship its own stylesheet and script inside its /assets directory.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SNFS_Assets_Manager {
    protected static array $assets = [];
    
    public static function register( string $type, string $dir ): void {
        self::$assets[ $type ] = $dir . '/assets';
    }

    public static function enqueue( string $type ): void {

        if ( empty( self::$assets[ $type ] ) ) {
            return;
        }
        $base = self::$assets[ $type ];

        if ( file_exists( "$base/$type.css" ) ) {
            wp_enqueue_style(
                "snfs-field-$type",
                plugins_url( "$base/$type.css" )
            );
        }

        if ( file_exists( "$base/$type.js" ) ) {
            wp_enqueue_script(
                "snfs-field-$type",
                plugins_url( "$base/$type.js" ),
                ['jquery'],
                false,
                true
            );
        }
    }

}
