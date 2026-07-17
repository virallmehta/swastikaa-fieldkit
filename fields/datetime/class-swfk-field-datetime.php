<?php
/**
 * Date-time field. Renders an HTML5 datetime-local input; stores a datetime string.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * DateTime field — <input type="datetime-local">.
 * Stores value as YYYY-MM-DDTHH:MM string.
 */
class SWFK_Field_Datetime extends SWFK_Field_Base {

    protected string $type = 'datetime';

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['type']  = 'datetime-local';
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
        return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value ) ? $value : '';
    }
}
