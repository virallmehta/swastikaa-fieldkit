<?php
/**
 * Number field. Renders an HTML5 number input with optional min/max/step; stores a numeric value.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Number field — <input type="number">.
 * Supports min, max, step args.
 */
class SNFS_Field_Number extends SNFS_Field_Base {

    protected string $type = 'number';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'min'  => '',
            'max'  => '',
            'step' => '',
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['type']  = 'number';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $value );

        if ( $this->args['min'] !== '' ) $attrs['min'] = $this->args['min'];
        if ( $this->args['max'] !== '' ) $attrs['max'] = $this->args['max'];
        if ( $this->args['step'] !== '' ) $attrs['step'] = $this->args['step'];

        echo '<input ' . $this->attrs_to_string( $attrs ) . ' />';

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        if ( $value === '' || $value === null ) return '';
        return is_numeric( $value ) ? $value + 0 : '';
    }
}
