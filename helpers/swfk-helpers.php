<?php
/**
 * Public template helper functions.
 * Provides a simple API for theme developers to retrieve and output field values
 * for posts, terms, users, and gallery/image/file attachments.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get a field value for a post.
 */
if ( ! function_exists( 'swfk_get_field' ) ) {
    function swfk_get_field( string $field_name, int $post_id = 0 ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        return get_post_meta( $post_id, 'swfk_' . $field_name, true );
    }
}

/**
 * Echo a field value for a post.
 */
if ( ! function_exists( 'swfk_the_field' ) ) {
    function swfk_the_field( string $field_name, int $post_id = 0 ): void {
        echo esc_html( swfk_get_field( $field_name, $post_id ) );
    }
}

/**
 * Get a field value for a term.
 */
if ( ! function_exists( 'swfk_get_term_field' ) ) {
    function swfk_get_term_field( string $field_name, $term = null, string $default = '' ) {
        if ( ! $term ) {
            $term = get_queried_object();
        }
        $term_id = is_object( $term ) ? $term->term_id : (int) $term;
        $value   = get_term_meta( $term_id, 'swfk_' . $field_name, true );
        return ( $value !== '' && $value !== false ) ? $value : $default;
    }
}

/**
 * Echo a field value for a term.
 */
if ( ! function_exists( 'swfk_the_term_field' ) ) {
    function swfk_the_term_field( string $field_name, $term = null, string $default = '' ): void {
        echo esc_html( swfk_get_term_field( $field_name, $term, $default ) );
    }
}

/**
 * Get a field value for a user.
 */
if ( ! function_exists( 'swfk_get_user_field' ) ) {
    function swfk_get_user_field( string $field_name, $user = null, string $default = '' ) {
        if ( ! $user ) {
            $user = get_current_user_id();
        }
        $user_id = is_object( $user ) ? $user->ID : (int) $user;
        $value   = get_user_meta( $user_id, 'swfk_' . $field_name, true );
        return ( $value !== '' && $value !== false ) ? $value : $default;
    }
}

/**
 * Echo a field value for a user.
 */
if ( ! function_exists( 'swfk_the_user_field' ) ) {
    function swfk_the_user_field( string $field_name, $user = null, string $default = '' ): void {
        echo esc_html( swfk_get_user_field( $field_name, $user, $default ) );
    }
}

/**
 * Get an image field — returns attachment data array.
 * Keys: id, url, alt, width, height, title
 */
if ( ! function_exists( 'swfk_get_image' ) ) {
    function swfk_get_image( string $field_name, int $post_id = 0, string $size = 'full' ): array {
        if ( ! $post_id ) $post_id = get_the_ID();
        $attachment_id = (int) get_post_meta( $post_id, 'swfk_' . $field_name, true );
        if ( ! $attachment_id ) return [];
        $src = wp_get_attachment_image_src( $attachment_id, $size );
        if ( ! $src ) return [];
        return [
            'id'     => $attachment_id,
            'url'    => $src[0],
            'width'  => $src[1],
            'height' => $src[2],
            'alt'    => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
            'title'  => get_the_title( $attachment_id ),
        ];
    }
}

/**
 * Get a gallery field — returns array of attachment data arrays.
 */
if ( ! function_exists( 'swfk_get_gallery' ) ) {
    function swfk_get_gallery( string $field_name, int $post_id = 0, string $size = 'full' ): array {
        if ( ! $post_id ) $post_id = get_the_ID();
        $raw = get_post_meta( $post_id, 'swfk_' . $field_name, true );
        if ( ! $raw ) return [];
        $ids    = array_filter( array_map( 'intval', explode( ',', $raw ) ) );
        $images = [];
        foreach ( $ids as $id ) {
            $src = wp_get_attachment_image_src( $id, $size );
            if ( ! $src ) continue;
            $images[] = [
                'id'     => $id,
                'url'    => $src[0],
                'width'  => $src[1],
                'height' => $src[2],
                'alt'    => get_post_meta( $id, '_wp_attachment_image_alt', true ),
                'title'  => get_the_title( $id ),
            ];
        }
        return $images;
    }
}

/**
 * Get a file field — returns file data array.
 * Keys: id, url, title, filename
 */
if ( ! function_exists( 'swfk_get_file' ) ) {
    function swfk_get_file( string $field_name, int $post_id = 0 ): array {
        if ( ! $post_id ) $post_id = get_the_ID();
        $attachment_id = (int) get_post_meta( $post_id, 'swfk_' . $field_name, true );
        if ( ! $attachment_id ) return [];
        return [
            'id'       => $attachment_id,
            'url'      => wp_get_attachment_url( $attachment_id ),
            'title'    => get_the_title( $attachment_id ),
            'filename' => basename( get_attached_file( $attachment_id ) ),
        ];
    }
}