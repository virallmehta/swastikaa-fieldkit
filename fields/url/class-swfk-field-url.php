<?php
/**
 * URL field. Renders an HTML5 url input; stores the URL string.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * URL field — <input type="url">.
 */
class SWFK_Field_Url extends SWFK_Field_Base {

    protected string $type = 'url';

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['type']  = 'url';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = esc_attr( $value );

        $this->render_input( $attrs );

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        return esc_url_raw( $value );
    }
}
