<?php
/**
 * Term context. Wraps a WP_Term object and provides the correct term meta
 * storage driver for field value read/write operations.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Term_Context implements SWFK_Context_Interface {

    protected int    $term_id;
    protected string $taxonomy;

    public function __construct( int $term_id, string $taxonomy = '' ) {
        $this->term_id  = absint( $term_id );
        $this->taxonomy = $taxonomy;
    }

    public function get_id(): int {
        return $this->term_id;
    }

    public function get_type(): string {
        if ( $this->taxonomy ) {
            return $this->taxonomy;
        }
        if ( $this->term_id > 0 ) {
            $term = get_term( $this->term_id );
            if ( $term && ! is_wp_error( $term ) ) {
                return $term->taxonomy;
            }
        }
        return 'category'; // safe fallback
    }

    public function storage(): SWFK_Storage_Interface {
        return new SWFK_Term_Meta_Storage();
    }

}
