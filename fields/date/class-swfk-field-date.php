<?php
/**
 * Date field. Renders an HTML5 date input; stores a date string in Y-m-d format.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Date field — <input type="date">.
 * Stores value as YYYY-MM-DD string.
 */
class SWFK_Field_Date extends SWFK_Field_Base {

    protected string $type = 'date';

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['type']  = 'date';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $value );

        echo '<input ' . $this->attrs_to_string( $attrs ) . ' />';

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        // Validate YYYY-MM-DD format
        $value = sanitize_text_field( $value );
        return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
    }
}
