<?php
/**
 * Range slider field. Renders an HTML5 range input with optional min/max/step; stores a numeric value.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Range slider field — <input type="range">.
 * Displays current value beside the slider.
 */
class SWFK_Field_Range extends SWFK_Field_Base {

    protected string $type = 'range';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'min'     => '0',
            'max'     => '100',
            'step'    => '1',
            'default' => '50',
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $val = ( $value !== '' && $value !== null ) ? $value : $this->args['default'];
        $uid = 'snfs-range-' . esc_attr( $meta_key );

        $attrs = $this->build_attributes();
        $attrs['type']  = 'range';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $val );
        $attrs['min']   = $this->args['min'];
        $attrs['max']   = $this->args['max'];
        $attrs['step']  = $this->args['step'];
        $attrs['oninput'] = 'document.getElementById("' . $uid . '").textContent=this.value';

        echo '<div style="display:flex;align-items:center;gap:10px;">';
        echo '<input ' . $this->attrs_to_string( $attrs ) . ' />';
        echo '<span id="' . esc_attr( $uid ) . '">' . esc_html( $val ) . '</span>';
        echo '</div>';

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        return is_numeric( $value ) ? $value + 0 : (int) $this->args['default'];
    }
}
