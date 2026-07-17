<?php
/**
 * Text field. Renders a standard HTML text input; stores a plain text string.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Field_Text extends SWFK_Field_Base {

    protected  string $type = 'text';

    public function sanitize( $value ): mixed {
        return sanitize_text_field( $value );
    }
}

//SWFK_Field_Registry::register( 'text', SWFK_Field_Text::class,'Text' );