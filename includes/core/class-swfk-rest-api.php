<?php
/**
 * Exposes SwastiNexus Fields Studio custom fields via the WordPress REST API.
 * Registers REST fields for post types, taxonomies, and users based on
 * active field group location rules.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Rest_Api {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_fields' ] );
    }

    public function register_fields(): void {
        $groups = SWFK_Field_Group_Repository::get_all();

        foreach ( $groups as $group ) {
            $meta   = get_post_meta( $group->ID, 'swfk_fields', true );
            $rules  = get_post_meta( $group->ID, 'swfk_location_rules', true );
            $fields = is_array( $meta )  ? $meta  : [];
            $rules  = is_array( $rules ) ? $rules : [];

            if ( empty( $fields ) || empty( $rules ) ) {
                continue;
            }

            foreach ( $rules as $rule ) {
                $type  = $rule['type']  ?? '';
                $value = $rule['value'] ?? '';

                if ( $type === 'post_type' && $value ) {
                    $this->register_for_post_type( $value, $fields );
                } elseif ( $type === 'taxonomy' && $value ) {
                    $this->register_for_taxonomy( $value, $fields );
                } elseif ( $type === 'user_profile' ) {
                    $this->register_for_users( $fields );
                }
            }
        }
    }

    // ── Post type fields ──────────────────────────────────────────────────────

    private function register_for_post_type( string $post_type, array $fields ): void {
        foreach ( $fields as $field ) {
            $meta_key = 'swfk_' . ( $field['name'] ?? '' );
            if ( ! $meta_key || $meta_key === 'swfk_' ) continue;

            register_rest_field(
                $post_type,
                $meta_key,
                [
                    'get_callback'    => function( $post ) use ( $meta_key ) {
                        return $this->get_post_field( $post['id'], $meta_key );
                    },
                    'update_callback' => function( $value, $post ) use ( $meta_key ) {
                        return $this->update_post_field( $value, $post->ID, $meta_key );
                    },
                    'schema'          => $this->get_schema( $field ),
                ]
            );
        }
    }

    // ── Taxonomy fields ───────────────────────────────────────────────────────

    private function register_for_taxonomy( string $taxonomy, array $fields ): void {
        foreach ( $fields as $field ) {
            $meta_key = 'swfk_' . ( $field['name'] ?? '' );
            if ( ! $meta_key || $meta_key === 'swfk_' ) continue;

            register_term_meta(
                $taxonomy,
                $meta_key,
                [
                    'type'          => $this->get_meta_type( $field ),
                    'single'        => true,
                    'show_in_rest'  => true,
                    'auth_callback' => function() {
                        return current_user_can( 'edit_terms' );
                    },
                ]
            );
        }
    }

    // ── User fields ───────────────────────────────────────────────────────────

    private function register_for_users( array $fields ): void {
        foreach ( $fields as $field ) {
            $meta_key = 'swfk_' . ( $field['name'] ?? '' );
            if ( ! $meta_key || $meta_key === 'swfk_' ) continue;

            register_meta(
                'user',
                $meta_key,
                [
                    'type'          => $this->get_meta_type( $field ),
                    'single'        => true,
                    'show_in_rest'  => true,
                    'auth_callback' => function() {
                        return current_user_can( 'edit_users' );
                    },
                ]
            );
        }
    }

    // ── Callbacks ─────────────────────────────────────────────────────────────

    private function get_post_field( int $post_id, string $meta_key ) {
        return get_post_meta( $post_id, $meta_key, true );
    }

    private function update_post_field( $value, int $post_id, string $meta_key ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return new WP_Error( 'rest_cannot_edit', 'Sorry, you cannot edit this field.', [ 'status' => 403 ] );
        }
        return update_post_meta( $post_id, $meta_key, $value );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function get_meta_type( array $field ): string {
        switch ( $field['type'] ?? 'text' ) {
            case 'number':
            case 'range':
                return 'number';
            case 'true-false':
                return 'boolean';
            case 'checkbox':
                return 'array';
            default:
                return 'string';
        }
    }

    private function get_schema( array $field ): array {
        $type = $field['type'] ?? 'text';

        switch ( $type ) {
            case 'number':
            case 'range':
                $schema = [ 'type' => 'number' ];
                if ( $field['min'] !== '' ) $schema['minimum'] = (float) $field['min'];
                if ( $field['max'] !== '' ) $schema['maximum'] = (float) $field['max'];
                return $schema;

            case 'true-false':
                return [ 'type' => 'boolean' ];

            case 'checkbox':
                return [
                    'type'  => 'array',
                    'items' => [ 'type' => 'string' ],
                ];

            default:
                return [ 'type' => 'string' ];
        }
    }
}
new SWFK_Rest_Api();