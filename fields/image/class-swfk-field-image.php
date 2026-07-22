<?php
/**
 * Image field. Renders a media library image picker; stores the attachment ID.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Image field — uses WordPress Media Library.
 * Stores attachment ID (integer).
 */
class SWFK_Field_Image extends SWFK_Field_Base {

    protected string $type = 'image';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'preview_size' => 'thumbnail',
            'return'       => 'id', // 'id' | 'url' | 'array'
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $attachment_id = absint( $value );
        $preview_url   = $attachment_id
            ? wp_get_attachment_image_url( $attachment_id, $this->args['preview_size'] )
            : '';
        $uid = 'swfk-image-' . sanitize_html_class( $meta_key );
        ?>
        <div class="swfk-image-field" id="<?php echo esc_attr( $uid ); ?>">
            <div class="swfk-image-preview" style="margin-bottom:8px;">
                <?php if ( $preview_url ) : ?>
                    <img src="<?php echo esc_url( $preview_url ); ?>"
                         style="max-width:200px;max-height:200px;display:block;" />
                <?php endif; ?>
            </div>
            <input type="hidden"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( $attachment_id ?: '' ); ?>" />
            <button type="button"
                    class="button swfk-image-upload-btn"
                    data-field="<?php echo esc_attr( $meta_key ); ?>"
                    data-preview="<?php echo esc_attr( $uid ); ?>">
                <?php echo $attachment_id ? esc_html__( 'Change Image', 'swastikaa-fieldkit' ) : esc_html__( 'Select Image', 'swastikaa-fieldkit' ); ?>
            </button>
            <?php if ( $attachment_id ) : ?>
                <button type="button"
                        class="button swfk-image-remove-btn"
                        data-field="<?php echo esc_attr( $meta_key ); ?>"
                        data-preview="<?php echo esc_attr( $uid ); ?>"
                        style="margin-left:4px;">
                    <?php esc_html_e( 'Remove', 'swastikaa-fieldkit' ); ?>
                </button>
            <?php endif; ?>
        </div>
        <?php
        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        return absint( $value ) ?: '';
    }
}
