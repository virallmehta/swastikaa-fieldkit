<?php
/**
 * Select dropdown field. Renders an HTML select element from a user-defined choices list; stores the selected value.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Select dropdown field.
 * Choices stored as 'value : Label' one per line in field config,
 * or as ['value' => 'Label'] array when used programmatically.
 */
class SNFS_Field_Select extends SNFS_Field_Base {

    protected string $type = 'select';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'choices'      => [],    // ['val' => 'Label', ...]
            'choices_raw'  => '',    // "val : Label\nval2 : Label2"
            'multiple'     => false,
            'placeholder'  => '— Select —',
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $choices  = $this->parse_choices();
        $multiple = ! empty( $this->args['multiple'] );
        $name     = $multiple ? $meta_key . '[]' : $meta_key;
        $selected = is_array( $value ) ? $value : [ $value ];

        $attrs = $this->build_attributes();
        $attrs['id']   = $meta_key;
        $attrs['name'] = $name;
        unset( $attrs['placeholder'] ); // not valid on <select>
        if ( $multiple ) $attrs['multiple'] = true;

        echo '<select ' . $this->attrs_to_string( $attrs ) . '>';

        if ( ! $multiple ) {
            echo '<option value="">' . esc_html( $this->args['placeholder'] ) . '</option>';
        }

        foreach ( $choices as $val => $lbl ) {
            $sel = in_array( (string) $val, array_map( 'strval', $selected ), true ) ? ' selected' : '';
            echo '<option value="' . esc_attr( $val ) . '"' . $sel . '>' . esc_html( $lbl ) . '</option>';
        }

        echo '</select>';

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', $value );
        }
        return sanitize_text_field( $value );
    }

    protected function parse_choices(): array {
        // Programmatic choices array takes priority
        if ( ! empty( $this->args['choices'] ) && is_array( $this->args['choices'] ) ) {
            return $this->args['choices'];
        }

        $choices = [];
        $raw = $this->args['choices_raw'] ?? '';

        foreach ( explode( "\n", $raw ) as $line ) {
            $line = trim( $line );
            if ( $line === '' ) continue;
            if ( strpos( $line, ':' ) !== false ) {
                [ $val, $lbl ] = explode( ':', $line, 2 );
                $choices[ trim( $val ) ] = trim( $lbl );
            } else {
                $choices[ $line ] = $line;
            }
        }

        return $choices;
    }
}
