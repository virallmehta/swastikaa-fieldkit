<?php
/**
 * Date-Time field. Renders an HTML5 datetime-local picker; stores YYYY-MM-DDTHH:MM.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * DateTime field — <input type="datetime-local">.
 * Stores value as YYYY-MM-DDTHH:MM string.
 * Browser renders a combined date + time picker.
 *
 * @since 1.0.0
 */
class SWFK_Field_Datetime extends SWFK_Field_Base {

    protected string $type = 'datetime';

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['type']  = 'datetime-local';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $value );

        $this->render_input( $attrs );

        if ( $value ) {
            $ts = strtotime( $value );
            if ( $ts ) {
                echo '<small class="swfk-field-hint" style="display:block;margin-top:4px;color:#646970;">'
                    . esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts ) )
                    . '</small>';
            }
        } else {
            echo '<small class="swfk-field-hint" style="display:block;margin-top:4px;color:#646970;">'
                . esc_html__( 'Click the field to open the date & time picker.', 'swastikaa-fieldkit' )
                . '</small>';
        }

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        $value = sanitize_text_field( $value );
        return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value ) ? $value : '';
    }
}
