<?php
/**
 * Auto-discovers and loads field types from the /fields directory.
 * Scans each field subdirectory for a field.json manifest and loads the
 * corresponding class file, registering it with SNFS_Field_Registry.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SNFS_Field_Loader {

    public static function load( string $base_path ): void {
        foreach ( glob( $base_path . '/*/field.json' ) as $json ) { 
            $config = json_decode( file_get_contents( $json ), true );
             
            if ( ! $config ) {
                continue;
            }
            $dir = dirname( $json );

            $file_name = '/class-' . strtolower( str_replace( '_', '-', $config['class'] )) . '.php';

            if ( file_exists( $dir . $file_name ) ) {
                require_once $dir . $file_name;
            } else {
            }
             
            SNFS_Field_Registry::register(
                $config['type'],
                $config['class'],
                $config['label']
            );

            SNFS_Template_Registry::register(
                $config['type'],
                $dir . '/template.php'
            );

            SNFS_Assets_Manager::register(
                $config['type'],
                $dir
            );
        }

    }
 
}