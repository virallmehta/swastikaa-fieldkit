<?php
/**
 * Date field. Renders an HTML5 date picker; stores date as Y-m-d.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Date field — <input type="date">.
 * Stores value as YYYY-MM-DD string.
 * The browser's native date picker (calendar) is used.
 *
 * @since 1.0.0
 */
class SWFK_Field_Date extends SWFK_Field_Base {

    protected string $type = 'date';

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['type']  = 'date';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $value );

        $this->render_input( $attrs );

        // Show a human-readable hint when a value is already set.
        if ( $value ) {
            $ts = strtotime( $value );
            if ( $ts ) {
                echo '<small class="swfk-field-hint" style="display:block;margin-top:4px;color:#646970;">'
                    . esc_html( date_i18n( get_option( 'date_format' ), $ts ) )
                    . '</small>';
            }
        } else {
            echo '<small class="swfk-field-hint" style="display:block;margin-top:4px;color:#646970;">'
                . esc_html__( 'Click the field to open the calendar picker.', 'swastikaa-fieldkit' )
                . '</small>';
        }

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        $value = sanitize_text_field( $value );
        return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
    }
}
