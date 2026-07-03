<?php
/**
 * Options storage driver. Reads and writes field values using get_option()
 * and update_option() with the snfs_ key prefix.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SNFS_Options_Storage implements SNFS_Storage_Interface {

    /** @var string The wp_options key that stores all fields for this page. */
    protected string $option_name;

    public function __construct( string $option_name ) {
        $this->option_name = sanitize_key( $option_name );
    }

    public function get( string $key, ?int $id = null ):mixed {
        // $id is unused for options — all values live under $this->option_name
        $data = get_option( $this->option_name, [] );
        return is_array( $data ) ? ( $data[ $key ] ?? null ) : null;
    }

    protected function get_all(): array {
        return get_option( $this->option_name, [] );
    }

    protected function save_all( array $data ): void {
        update_option( $this->option_name, $data );
    }

    public function save( string $key, ?int $id, mixed $value ): bool {
        // $id is unused for options
        $data         = get_option( $this->option_name, [] );
        $data         = is_array( $data ) ? $data : [];
        $data[ $key ] = $value;
        return update_option( $this->option_name, $data );
    }

    public function delete( string $key, ?int $id ): void {
        // $id is unused for options
        $data = get_option( $this->option_name, [] );
        if ( is_array( $data ) && isset( $data[ $key ] ) ) {
            unset( $data[ $key ] );
            update_option( $this->option_name, $data );
        }
    }

}
