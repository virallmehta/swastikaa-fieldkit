<?php
/**
 * Context interface. All context objects (post, term, user, options) must implement
 * this interface to provide a consistent API for field group matching and storage.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface SWFK_Context_Interface {

    /**
     * The numeric record ID.
     * Returns 0 for contexts that are not record-based (e.g. options pages).
     */
    public function get_id(): int;

    /**
     * A unique slug that identifies where we are.
     *
     * Used by location rule matching — the rule's "value" is compared against this.
     *
     * Examples:
     *   SWFK_Post_Context    → returns the post type slug  ('post', 'page', 'product', …)
     *   SWFK_Term_Context    → returns the taxonomy slug   ('category', 'post_tag', 'genre', …)
     *   SWFK_User_Context    → returns 'user'
     *   SWFK_Options_Context → returns the options page slug ('general', 'my-theme-settings', …)
     */
    public function get_type(): string;

    /**
     * The storage driver that knows how to read/write values for this context.
     * SWFK_Field_Base calls this — it never hard-codes get_post_meta() etc.
     */
    public function storage(): SWFK_Storage_Interface;
}