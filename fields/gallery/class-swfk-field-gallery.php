<?php
/**
 * Gallery field. Renders a media library multi-picker; stores attachment IDs as a comma-separated string.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gallery field — multiple image selection via Media Library.
 * Stores comma-separated attachment IDs e.g. '12,45,67'.
 */
class SWFK_Field_Gallery extends SWFK_Field_Base {

    protected string $type = 'gallery';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'preview_size' => 'thumbnail',
            'min'          => '',
            'max'          => '',
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $ids = $value ? array_filter( array_map( 'absint', explode( ',', $value ) ) ) : [];
        $uid = 'snfs-gallery-' . sanitize_html_class( $meta_key );
        ?>
        <div class="snfs-gallery-field" id="<?php echo esc_attr( $uid ); ?>">
            <div class="snfs-gallery-images" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
                <?php foreach ( $ids as $id ) :
                    $url = wp_get_attachment_image_url( $id, $this->args['preview_size'] );
                    if ( ! $url ) continue;
                    ?>
                    <div class="snfs-gallery-item"
                         data-id="<?php echo esc_attr( $id ); ?>"
                         style="position:relative;">
                        <img src="<?php echo esc_url( $url ); ?>"
                             style="width:80px;height:80px;object-fit:cover;display:block;" />
                        <button type="button"
                                class="snfs-gallery-remove"
                                data-id="<?php echo esc_attr( $id ); ?>"
                                style="position:absolute;top:2px;right:2px;padding:0 4px;cursor:pointer;">
                            &times;
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" />
            <button type="button"
                    class="button snfs-gallery-add-btn"
                    data-field="<?php echo esc_attr( $meta_key ); ?>"
                    data-container="<?php echo esc_attr( $uid ); ?>">
                <?php esc_html_e( 'Add Images', 'snfs' ); ?>
            </button>
        </div>
        <?php
        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        if ( ! $value ) return '';
        $ids = array_filter( array_map( 'absint', explode( ',', $value ) ) );
        return implode( ',', $ids );
    }
}
