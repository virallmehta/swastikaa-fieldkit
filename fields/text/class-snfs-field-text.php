<?php
/**
 * Text field. Renders a standard HTML text input; stores a plain text string.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SNFS_Field_Text extends SNFS_Field_Base {

    protected  string $type = 'text';

    public function sanitize( $value ): mixed {
        return sanitize_text_field( $value );
    }
}

//SNFS_Field_Registry::register( 'text', SNFS_Field_Text::class,'Text' );