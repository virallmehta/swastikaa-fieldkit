<?php
/**
 * Field type registry. Maps field type slugs to their PHP class names and labels.
 * All field types must be registered here before they can be used.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Field_Registry {
    /**
     * Registered field types → class map.
     *
     * @var array<string, string>
     */
    protected static array $fields = [];

    /**
     * Register a field type with type, class, and label
     * 
     * @param string $type  Field type key (text, textarea, select, etc.)
     * @param string $class Fully-qualified class name
     * @param string $label Human-readable field name
     * @return bool True if registered successfully
     */
    public static function register( string $type, string $class, string $label ): bool {
        $type = sanitize_key( strtolower( $type ) );

        if ( isset( self::$fields[ $type ] ) ) {
            return false;
        }

        // Store complete field definition
        self::$fields[ $type ] = [
            'class'  => $class,
            'label'  => sanitize_text_field( $label ),
            'type'   => $type
        ];

        /**
         * Fires after field registration.
         * 
         * @param string $type
         * @param string $class
         * @param string $label
         */
        do_action( 'swfk_field_registered', $type, $class, $label );

        return true;
    }

    public static function get( string $type ): mixed {
        $type = sanitize_key( strtolower( $type ) );
        return self::$fields[ $type ] ?? null;
    }

    public static function get_all(): array {
        //return array_keys( self::$fields );
        return self::$fields;
    }

    // Depreciate
    public static function boot(): void {
        static $booted = false;
        if ( $booted ) {
            return;
        }
        $booted = true;

        do_action( 'swfk_fields_booted' );
    }

    public static function get_instance( string $type, array $config, SWFK_Context_Interface $context ): ?SWFK_Field_Base {
        //$class = self::get( $type );
        $class = self::$fields[$type]['class'];

        if ( ! $class ) {
            return null;
        }

        if ( ! class_exists( $class ) || ! is_a( $class, SWFK_Field_Base::class, true ) ) {
            return null;
        }

        // Build args — do NOT use array_filter (it strips false and 0 values like required=false)
        $args = [
            'required'     => (bool) ( $config['required']     ?? false ),
            'default'      => $config['default']      ?? '',
            'placeholder'  => $config['placeholder']  ?? '',
            'instructions' => $config['instructions'] ?? '',
            'choices_raw'  => $config['choices_raw']  ?? '',
            'min'          => $config['min']          ?? '',
            'max'          => $config['max']          ?? '',
            'step'         => $config['step']         ?? '',
        ];

        return new $class( $config['name'], $config['label'], $args, $context );
    }

}
