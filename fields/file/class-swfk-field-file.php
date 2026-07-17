<?php
/**
 * File upload field. Renders a media library picker; stores the attachment ID.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * File field — uses WordPress Media Library.
 * Stores attachment ID. Allows any file type.
 */
class SWFK_Field_File extends SWFK_Field_Base {

    protected string $type = 'file';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'allowed_types' => '', // e.g. 'pdf,doc,docx' — empty = all
            'return'        => 'id',
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $attachment_id = absint( $value );
        $filename      = '';
        $file_url      = '';
        if ( $attachment_id ) {
            $file_url = wp_get_attachment_url( $attachment_id );
            $filename = basename( get_attached_file( $attachment_id ) );
        }
        $uid = 'snfs-file-' . sanitize_html_class( $meta_key );
        ?>
        <div class="snfs-file-field" id="<?php echo esc_attr( $uid ); ?>">
            <div class="snfs-file-info" style="margin-bottom:8px;">
                <?php if ( $file_url ) : ?>
                    <a href="<?php echo esc_url( $file_url ); ?>" target="_blank">
                        <?php echo esc_html( $filename ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <input type="hidden"
                   id="<?php echo esc_attr( $meta_key ); ?>"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   value="<?php echo esc_attr( $attachment_id ?: '' ); ?>" />
            <button type="button"
                    class="button snfs-file-upload-btn"
                    data-field="<?php echo esc_attr( $meta_key ); ?>"
                    data-preview="<?php echo esc_attr( $uid ); ?>"
                    data-type="<?php echo esc_attr( $this->args['allowed_types'] ); ?>">
                <?php echo $attachment_id ? esc_html__( 'Change File', 'snfs' ) : esc_html__( 'Select File', 'snfs' ); ?>
            </button>
            <?php if ( $attachment_id ) : ?>
                <button type="button"
                        class="button snfs-file-remove-btn"
                        data-field="<?php echo esc_attr( $meta_key ); ?>"
                        data-preview="<?php echo esc_attr( $uid ); ?>"
                        style="margin-left:4px;">
                    <?php esc_html_e( 'Remove', 'snfs' ); ?>
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
