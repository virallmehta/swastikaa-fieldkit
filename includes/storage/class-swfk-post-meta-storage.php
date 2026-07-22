<?php
/**
 * Post meta storage driver. Reads and writes field values using get_post_meta()
 * and update_post_meta() with the swfk_ key prefix.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Post_Meta_Storage implements SWFK_Storage_Interface {

    public function get( string $key, ?int $id = null ):mixed {
        $post_id = $id ?? get_the_ID();  // Fallback to current post.
        if ( ! $post_id ) {
            return null;
        }
        $value = get_post_meta( $post_id, $key, true );
        return get_post_meta( $post_id, $key, true );
    }

    public function save( string $key, ?int $id, mixed $value ): bool {
        $post_id = $id ?? get_the_ID();
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }
        return (bool) update_post_meta( $post_id, $key, $value );
    }

    public function delete( string $key, ?int $id ): void {
        $post_id = $id ?? get_the_ID();
        delete_post_meta( $post_id, $key );
    }
}