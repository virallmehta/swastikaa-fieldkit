<?php
/**
 * Textarea field. Renders a multi-line textarea input; stores a plain text string.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Textarea field.
 */
class SNFS_Field_Textarea extends SNFS_Field_Base {

    protected string $type = 'textarea';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'rows'      => 4,
            'new_lines' => 'wpautop', // 'wpautop' | 'br' | 'none'
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $attrs = $this->build_attributes();
        $attrs['id']   = $meta_key;
        $attrs['name'] = $meta_key;
        $attrs['rows'] = $this->args['rows'];
        unset( $attrs['value'] );

        echo '<textarea ' . $this->attrs_to_string( $attrs ) . '>'
            . esc_textarea( $value )
            . '</textarea>';

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        return sanitize_textarea_field( $value );
    }
}
