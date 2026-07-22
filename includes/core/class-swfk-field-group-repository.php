<?php
/**
 * Retrieves and caches published field groups that match a given context.
 * Provides static helpers to query field group CPT posts, their fields,
 * and location rules.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Field_Group_Repository {

    protected static array $group_cache = [];
    protected static array $meta_cache = [];

    /**
     * Return all published field groups whose location rules match $context.
     *
     * @param SWFK_Context_Interface $context
     * @return array  Keyed by group post ID: [ group_id => ['post'=>WP_Post, 'fields'=>[], 'rules'=>[]] ]
     */
    public static function get_for_context( SWFK_Context_Interface $context ): array {
        // Cache key.
        $cache_key = 'groups_' . $context->get_type() . '_' . $context->get_id();
        
        if ( isset( self::$group_cache[ $cache_key ] ) ) {
            return self::$group_cache[ $cache_key ];
        }

        $groups = self::get_all();

        $matched = [];
        $matcher = new SWFK_General_Rule();

        foreach ( self::get_all() as $group ) {
            $meta = self::get_group_meta( $group->ID );

            // Skip groups with no fields or no rules defined
            if ( empty( $meta['fields'] ) || empty( $meta['rules'] ) ) {
                continue;
            }

            if ( $matcher->match( $context, $meta['rules'] ) ) {
                $matched[ $group->ID ] = [
                    'post'   => $group,
                    'fields' => $meta['fields'],
                    'rules'  => $meta['rules'],
                ];
            }
        }
        
        self::$group_cache[ $cache_key ] = $matched;
        return $matched;
    }

    public static function clear_cache(): void {
        self::$group_cache = [];
        self::$meta_cache  = [];
    }

    public static function get( int $group_id ): ?object {
        $group = get_post( $group_id );
        return ( $group && $group->post_type === 'swfk_field_group' ) ? $group : null;
    }

    public static function get_all(): array {
        return get_posts( [
            'post_type'      => 'swfk_field_group',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        ] );
    }

    public static function get_context_type( int $group_id ): string {
        $type = get_post_meta( $group_id, 'swfk_location_rules', true );
        $context_type = "";
        foreach ( $type as $key ) {
            $context_type = $key['type'];
        }
        return $context_type ?: 'post_type';
    }

    private static function get_group_meta( int $group_id ): array {
        if ( isset( self::$meta_cache[ $group_id ] ) ) {
            return self::$meta_cache[ $group_id ];
        }

        $fields = get_post_meta( $group_id, 'swfk_fields', true );
        $rules  = get_post_meta( $group_id, 'swfk_location_rules', true );

        self::$meta_cache[ $group_id ] = [
            'fields' => is_array( $fields ) ? $fields : [],
            'rules'  => is_array( $rules )  ? $rules  : [],
        ];

        return self::$meta_cache[ $group_id ];
    }
}