<?php
/**
 * Post context. Wraps a WP_Post object and provides the correct post meta
 * storage driver for field value read/write operations.
 *
 * @package SwastikaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Post_Context implements SWFK_Context_Interface {

    protected int    $post_id;
    protected string $post_type;

    public function __construct( int $post_id, string $post_type = '' ) {
        $this->post_id   = absint( $post_id );
        $this->post_type = $post_type ?: ( get_post_type( $this->post_id ) ?: '' );
    }

    public function get_id(): int {
        return $this->post_id;
    }

    public function get_type(): string {
        return $this->post_type;
    }

    public function storage(): SWFK_Storage_Interface {
        return new SWFK_Post_Meta_Storage();
    }

}

