<?php
/**
 * User meta storage driver. Reads and writes field values using get_user_meta()
 * and update_user_meta() with the snfs_ key prefix.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SNFS_User_Meta_Storage implements SNFS_Storage_Interface {

    public function get( string $key, ?int $id = null ): mixed {
        if ( ! $id ) return null;
        return get_user_meta( $id, $key, true );
    }

    public function save( string $key, ?int $id, mixed $value ): bool {
        if ( ! $id ) return false;
        return (bool) update_user_meta( $id, $key, $value );
    }

    public function delete( string $key, ?int $id ): void {
        if ( $id ) delete_user_meta( $id, $key );
    }
}
