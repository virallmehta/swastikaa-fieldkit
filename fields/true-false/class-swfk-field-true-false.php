<?php
/**
 * True/False toggle field. Renders a styled checkbox toggle; stores 1 (true) or 0 (false).
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * True / False field — styled toggle switch.
 * Stores '1' (true) or '0' (false).
 */
class SWFK_Field_True_False extends SWFK_Field_Base {

    protected string $type = 'true-false';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'message' => '', // Optional label shown beside the toggle
            'default' => '0',
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $checked = ! empty( $value ) && $value !== '0' ? 'checked' : '';
        ?>
        <div class="snfs-true-false">
            <input type="hidden" name="<?php echo esc_attr( $meta_key ); ?>" value="0" />
            <label class="snfs-toggle">
                <input
                    type="checkbox"
                    id="<?php echo esc_attr( $meta_key ); ?>"
                    name="<?php echo esc_attr( $meta_key ); ?>"
                    value="1"
                    class="snfs-input snfs-input-true-false"
                    <?php echo $checked; ?>
                />
                <span class="snfs-toggle-slider"></span>
                <?php if ( ! empty( $this->args['message'] ) ) : ?>
                    <span class="snfs-toggle-label"><?php echo esc_html( $this->args['message'] ); ?></span>
                <?php endif; ?>
            </label>
        </div>
        <?php
        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        return $value ? '1' : '0';
    }
}
