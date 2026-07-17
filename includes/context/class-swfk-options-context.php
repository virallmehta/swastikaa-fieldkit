<?php
/**
 * Options context. Represents an options page and provides the correct
 * wp_options storage driver for field value read/write operations.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Options_Context implements SWFK_Context_Interface {

    protected string $page_slug;

    /**
     * @param string $page_slug  The admin page slug (same as used in add_menu_page / add_submenu_page).
     */
    public function __construct( string $page_slug ) {
        $this->page_slug = sanitize_key( $page_slug );
    }

    public function get_id(): int {
        return 0; // Options are not record-based.
    }

    /**
     * Returns the options page slug.
     * Location rules match their value against this.
     * e.g. rule: type=options_page, value='my-theme-settings'
     */
    public function get_type(): string {
        return $this->page_slug;
    }

    public function storage(): SWFK_Storage_Interface {
        // All fields for this page share one option key: 'swfk_opts_{slug}'
        return new SWFK_Options_Storage( 'swfk_opts_' . $this->page_slug );
    }

    /**
     * Convenience: get a single field value without instantiating a field object.
     *
     * @param string $field_name  The field's name (without 'swfk_' prefix).
     * @return mixed
     */
    public function get( string $field_name ) {
        return $this->storage()->get( 'swfk_' . sanitize_key( $field_name ), 0 );
    }
}
