<?php
/**
 * Email field. Renders an HTML5 email input with basic validation; stores the email address string.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Email field — <input type="email">.
 */
class SWFK_Field_Email extends SWFK_Field_Base {

    protected string $type = 'email';

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['type']  = 'email';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $value );

        $this->render_input( $attrs );

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        return sanitize_email( $value );
    }
}
