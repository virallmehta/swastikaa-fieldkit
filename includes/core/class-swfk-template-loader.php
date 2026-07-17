<?php
/**
 * Loads field templates from field directories.
 * Supports theme overrides and falls back to the plugin field directory.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Template_Loader {
    const TEMPLATE_DIR = SWFK_PLUGIN_DIR . 'fields/';  // Root fields dir

    /**
     * Load field template + data (theme overrides supported).
     *
     * @param string $field_type e.g. 'text', 'color_picker'
     * @param array  $data       ['field' => $field_obj, 'config' => [], 'context' => $context]
     * @return bool Success.
     */
    public static function load_field_template( string $field_type, array $data = [] ): bool {
        $template_path = self::locate_field_template( $field_type );

        if ( ! $template_path ) {
            return false;
        }

        ob_start();
        extract($data, EXTR_SKIP);
        include $template_path;
        echo ob_get_clean();

        return true;
    }

    /**
     * Locate: theme → plugin field dir → core fallback.
     */
    public static function locate_field_template( string $field_type ): ?string {
        $field_type = sanitize_file_name( $field_type );
        $core_path = self::TEMPLATE_DIR . $field_type . '/' . $field_type . '.php';

        return self::TEMPLATE_DIR . $field_type . '/template.php';

    }

    /**
     * Get template data safely.
     */
    public function get_data( string $key, $default = null ) {
        return $this->data[ $key ] ?? $default;
    }

    /** @var array */
    public array $data = [];
}
