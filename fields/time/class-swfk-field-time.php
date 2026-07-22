<?php
/**
 * Time field. Renders an HTML5 time picker; stores HH:MM.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Time field — <input type="time">.
 * Stores HH:MM string.
 * Browser renders a native time picker with hour/minute spinner.
 *
 * @since 1.0.0
 */
class SWFK_Field_Time extends SWFK_Field_Base {

    protected string $type = 'time';

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['type']  = 'time';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $value );

        $this->render_input( $attrs );

        if ( $value ) {
            // Show 12-hour friendly time beside the 24-hour input.
            $ts = strtotime( 'today ' . $value );
            if ( $ts ) {
                echo '<small class="swfk-field-hint" style="display:block;margin-top:4px;color:#646970;">'
                    . esc_html( date_i18n( get_option( 'time_format' ), $ts ) )
                    . '</small>';
            }
        } else {
            echo '<small class="swfk-field-hint" style="display:block;margin-top:4px;color:#646970;">'
                . esc_html__( 'Click the field to open the time picker.', 'swastikaa-fieldkit' )
                . '</small>';
        }

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        $value = sanitize_text_field( $value );
        return preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $value ) ? $value : '';
    }
}
