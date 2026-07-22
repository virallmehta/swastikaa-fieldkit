<?php
/**
 * Radio field. Renders a group of radio button inputs from a user-defined choices list; stores the selected value.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Radio button field.
 * Same choices format as Select.
 */
class SWFK_Field_Radio extends SWFK_Field_Base {

    protected string $type = 'radio';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'choices'     => [],
            'choices_raw' => '',
            'layout'      => 'vertical', // 'vertical' | 'horizontal'
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $choices = $this->parse_choices();
        $layout  = $this->args['layout'] === 'horizontal' ? 'display:inline-flex;gap:16px;' : '';

        echo '<ul class="swfk-radio-list" style="margin:0;padding:0;list-style:none;' . esc_attr( $layout ). '">';

        foreach ( $choices as $val => $lbl ) {
            $checked = (string) $value === (string) $val ? 'checked' : '';
            $uid     = esc_attr( $meta_key . '_' . sanitize_key( $val ) );
            echo '<li style="margin-bottom:4px;">';
            echo '<label for="' . esc_attr( $uid ) . '">';
            echo '<input type="radio"'
                . ' id="' . esc_attr( $uid ) . '"'
                . ' name="' . esc_attr( $meta_key ) . '"'
                . ' value="' . esc_attr( $val ) . '"'
                . ' class="swfk-input swfk-input-radio"'
                . ' ' . esc_attr( $checked ) . ' />';
            echo ' ' . esc_html( $lbl );
            echo '</label></li>';
        }

        echo '</ul>';

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        return sanitize_text_field( $value );
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
