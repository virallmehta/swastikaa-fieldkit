<?php
/**
 * Checkbox field. Renders a set of checkbox inputs; stores selected values as a comma-separated string or array.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Field_Checkbox extends SWFK_Field_Base {

    protected string $type = 'checkbox';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'choices'     => [],
            'choices_raw' => '',
            'message'     => '',
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $choices = $this->parse_choices();

        if ( empty( $choices ) ) {
            // Single toggle checkbox
            $checked = ! empty( $value ) && $value !== '0' ? 'checked' : '';
            echo '<label>';
            echo '<input type="hidden" name="' . esc_attr( $meta_key ) . '" value="0" />';
            echo '<input type="checkbox"'
                . ' id="' . esc_attr( $meta_key ) . '"'
                . ' name="' . esc_attr( $meta_key ) . '"'
                . ' class="swfk-input swfk-input-checkbox"'
                . ' value="1" ' . esc_attr( $checked ) . ' />';
            if ( ! empty( $this->args['message'] ) ) {
                echo ' ' . esc_html( $this->args['message'] );
            }
            echo '</label>';
        } else {
            // Multi-choice checkboxes
            $selected = is_array( $value ) ? $value : ( $value ? explode( ',', $value ) : [] );
            echo '<ul class="swfk-checkbox-list" style="margin:0;padding:0;list-style:none;">';
            foreach ( $choices as $val => $lbl ) {
                $checked = in_array( (string) $val, array_map( 'strval', $selected ), true ) ? 'checked' : '';
                $uid     = esc_attr( $meta_key . '_' . sanitize_key( $val ) );
                echo '<li style="margin-bottom:4px;"><label for="' . esc_attr( $uid ) . '">';
                echo '<input type="checkbox"'
                    . ' id="' . esc_attr( $uid ) . '"'
                    . ' name="' . esc_attr( $meta_key ) . '[]"'
                    . ' class="swfk-input swfk-input-checkbox"'
                    . ' value="' . esc_attr( $val ) . '" ' . esc_attr( $checked ) . ' />';
                echo ' ' . esc_html( $lbl );
                echo '</label></li>';
            }
            echo '</ul>';
        }

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', $value );
        }
        return $value ? '1' : '0';
    }

    protected function parse_choices(): array {
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