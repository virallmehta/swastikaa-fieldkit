<?php
/**
 * User context. Wraps a WP_User object and provides the correct user meta
 * storage driver for field value read/write operations.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_User_Context implements SWFK_Context_Interface {

    protected int $user_id;

    public function __construct( int $user_id ) {
        $this->user_id = absint( $user_id );
    }

    public function get_id(): int {
        return $this->user_id;
    }

    /**
     * Returns 'user_profile' to match location rule type values.
     */
    public function get_type(): string {
        return 'user_profile';
    }

    public function storage(): SWFK_Storage_Interface {
        return new SWFK_User_Meta_Storage();
    }
}