<?php
/**
 * Time field. Renders an HTML5 time input; stores a time string in H:i format.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Time field — <input type="time">.
 * Stores HH:MM string.
 */
class SWFK_Field_Time extends SWFK_Field_Base {

    protected string $type = 'time';

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['type']  = 'time';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $value );

        echo '<input ' . $this->attrs_to_string( $attrs ) . ' />';

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        $value = sanitize_text_field( $value );
        return preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $value ) ? $value : '';
    }
}
