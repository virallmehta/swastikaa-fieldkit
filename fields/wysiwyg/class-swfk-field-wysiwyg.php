<?php
/**
 * WYSIWYG editor field. Renders the WordPress TinyMCE/block editor; stores HTML content.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WYSIWYG field — uses WordPress wp_editor().
 * Stores HTML content. Sanitized with wp_kses_post on save.
 */
class SWFK_Field_Wysiwyg extends SWFK_Field_Base {

    protected string $type = 'wysiwyg';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'media_buttons' => true,
            'teeny'         => false,
            'rows'          => 10,
        ]);
    }

    public function render( string $meta_key, $value ): void {
        // Generate a valid HTML4 ID for TinyMCE: must start with a letter, only letters/digits/underscores.
        // We strip the 'swfk_' prefix and prefix with 'swfkeditor_' to guarantee uniqueness.
        $editor_id = 'swfkeditor_' . sanitize_html_class( str_replace( [ '-', 'swfk_' ], [ '_', '' ], $meta_key ) );

        wp_editor( $value ?: '', $editor_id, [
            'textarea_name' => $meta_key,
            'media_buttons' => (bool) $this->args['media_buttons'],
            'teeny'         => (bool) $this->args['teeny'],
            'textarea_rows' => (int) $this->args['rows'],
        ]);

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        return wp_kses_post( $value );
    }
}
