<?php
/**
 * Color picker field. Renders an HTML5 color input; stores a hex colour string.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Color Picker field — uses native <input type="color">.
 * Stores a hex color string e.g. '#ff6600'.
 */
class SNFS_Field_Color extends SNFS_Field_Base {

    protected string $type = 'color';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'default' => '#000000',
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $val = $value ?: $this->args['default'];
        $attrs = $this->build_attributes();
        $attrs['type']  = 'color';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $val );

        echo '<input ' . $this->attrs_to_string( $attrs ) . ' />';

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        $value = sanitize_hex_color( $value );
        return $value ?: $this->args['default'];
    }
}
