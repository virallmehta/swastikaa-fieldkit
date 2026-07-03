<?php
/**
 * Storage interface. All storage drivers (post meta, term meta, user meta, options)
 * must implement this interface to ensure a consistent read/write API.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface SNFS_Storage_Interface {
   /**
     * Read a stored value.
     *
     * @param string   $key  The meta key (already prefixed, e.g. 'snfs_hero_title').
     * @param int|null $id   Record ID (post_id / term_id / user_id). Null = not applicable (options).
     * @return mixed         Stored value, or null / '' if not found.
     */
    public function get( string $key, ?int $id = null ): mixed;

    /**
     * Write a value.
     *
     * @param string   $key
     * @param int|null $id
     * @param mixed    $value  Already sanitized by the field before this is called.
     * @return bool            True on success.
     */
    public function save( string $key, ?int $id, mixed $value ): bool;

    /**
     * Remove a stored value.
     *
     * @param string   $key
     * @param int|null $id
     */
    public function delete( string $key, ?int $id ): void;
}