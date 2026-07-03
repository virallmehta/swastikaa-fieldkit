<?php
/**
 * Caches field template paths for fast lookup.
 * Maintains a map of field type slugs to their template.php file paths.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SNFS_Template_Registry {
    /**
     * Field type → template path map.
     *
     * @var array<string, string>
     */
    protected static array $templates = [];

    /**
     * Register field template (called by Field Loader).
     *
     * @param string $field_type e.g. 'text', 'color_picker'
     * @param string $template_path Full path to template.php
     * @return bool Success.
     */
    public static function register( string $field_type, string $template_path ): bool {
        $field_type = sanitize_file_name( $field_type );
        
        if ( ! is_file( $template_path ) ) {
            return false;
        }

        static::$templates[ $field_type ] = $template_path;
        return true;
    }

    /**
     * Get cached template path.
     *
     * @param string $field_type
     * @return string|null Full path or null if not found.
     */
    public static function get( string $field_type ): ?string {
        $field_type = sanitize_file_name( $field_type );
        return static::$templates[ $field_type ] ?? null;
    }

    /**
     * Check if template exists.
     *
     * @param string $field_type
     * @return bool
     */
    public static function has( string $field_type ): bool {
        return self::get( $field_type ) !== null;
    }

    /**
     * Get all registered templates.
     *
     * @return array Field types.
     */
    public static function get_all(): array {
        return array_keys( static::$templates );
    }

    /**
     * Get all templates (type → path).
     *
     * @return array
     */
    public static function get_all_registered(): array {
        return static::$templates;
    }

    /**
     * Clear registry (for tests).
     */
    public static function reset(): void {
        static::$templates = [];
    }
}
