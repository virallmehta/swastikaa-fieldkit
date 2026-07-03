<?php
/**
 * Term meta storage driver. Reads and writes field values using get_term_meta()
 * and update_term_meta() with the swfk_ key prefix.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Term_Meta_Storage implements SWFK_Storage_Interface {

    public function get( string $key, ?int $id = null ): mixed {
        if ( ! $id ) return null;
        return get_term_meta( $id, $key, true );
    }

    public function save( string $key, ?int $id, mixed $value ): bool {
        if ( ! $id ) return false;
        return (bool) update_term_meta( $id, $key, $value );
    }

    public function delete( string $key, ?int $id ): void {
        if ( $id ) delete_term_meta( $id, $key );
    }
}