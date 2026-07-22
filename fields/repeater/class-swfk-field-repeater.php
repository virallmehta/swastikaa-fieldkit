<?php
/**
 * Repeater field. Renders a repeatable group of sub-fields; stores data as a serialised array.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Repeater field — a dynamic list of row-based sub-fields.
 * Stores value as JSON-encoded array of rows.
 *
 * Each row is a flat associative array keyed by sub-field name.
 * Sub-fields are defined in the field config as:
 *   'sub_fields' => [
 *     ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
 *     ['name' => 'url',   'label' => 'URL',   'type' => 'url'],
 *   ]
 *
 * MVP: renders sub-fields as plain text inputs.
 * Full sub-field type rendering is a Pro enhancement.
 */
class SWFK_Field_Repeater extends SWFK_Field_Base {

    protected string $type = 'repeater';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'sub_fields'   => [],
            'button_label' => 'Add Row',
            'min'          => '',
            'max'          => '',
        ]);
    }

    public function render( string $meta_key, $value ): void {
        $sub_fields = $this->args['sub_fields'];
        $rows       = [];

        if ( is_string( $value ) && $value !== '' ) {
            $decoded = json_decode( $value, true );
            if ( is_array( $decoded ) ) $rows = $decoded;
        } elseif ( is_array( $value ) ) {
            $rows = $value;
        }

        $uid = 'swfk-repeater-' . sanitize_html_class( $meta_key );
        ?>
        <div class="swfk-repeater-field" id="<?php echo esc_attr( $uid ); ?>"
             data-field="<?php echo esc_attr( $meta_key ); ?>">

            <div class="swfk-repeater-rows">
                <?php if ( empty( $rows ) ) : ?>
                    <p class="swfk-repeater-empty" style="color:#999;">
                        <?php esc_html_e( 'No rows yet. Click the button below to add one.', 'swastikaa-fieldkit' ); ?>
                    </p>
                <?php else : ?>
                    <?php foreach ( $rows as $i => $row ) : ?>
                        <?php $this->render_row( $meta_key, $sub_fields, $i, $row ); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="button"
                    class="button swfk-repeater-add"
                    data-field="<?php echo esc_attr( $meta_key ); ?>"
                    style="margin-top:8px;">
                + <?php echo esc_html( $this->args['button_label'] ); ?>
            </button>

            <?php /* Hidden JSON field synced by JS on save */ ?>
            <input type="hidden"
                   name="<?php echo esc_attr( $meta_key ); ?>"
                   class="swfk-repeater-input"
                   value="<?php echo esc_attr( $value ?: '[]' ); ?>" />
        </div>
        <?php
        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    private function render_row( string $meta_key, array $sub_fields, int $index, array $row_data ): void {
        ?>
        <div class="swfk-repeater-row"
             style="border:1px solid #ddd;padding:12px;margin-bottom:8px;position:relative;">
            <div class="swfk-repeater-row-handle"
                 style="cursor:move;color:#999;margin-bottom:8px;">
                
                &#8597; <?php 
                /* translators: row number */
                printf( esc_html__( 'Row %d', 'swastikaa-fieldkit' ), esc_attr( $index ) + 1 ); 
                ?>
            </div>
            <button type="button"
                    class="swfk-repeater-remove button-link"
                    style="position:absolute;top:8px;right:8px;color:#b32d2e;">
                &times; <?php esc_html_e( 'Remove', 'swastikaa-fieldkit' ); ?>
            </button>
            <div class="swfk-repeater-row-fields">
                <?php foreach ( $sub_fields as $sub_field ) :
                    $swfk_name  = sanitize_key( $sub_field['name'] ?? '' );
                    $swfk_label = $sub_field['label'] ?? $swfk_name;
                    $swfk_val   = $row_data[ $swfk_name ] ?? '';
                    ?>
                    <div style="margin-bottom:8px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;">
                            <?php echo esc_html( $swfk_label ); ?>
                        </label>
                        <input type="text"
                               class="large-text swfk-repeater-subfield"
                               data-key="<?php echo esc_attr( $swfk_name ); ?>"
                               value="<?php echo esc_attr( $swfk_val ); ?>" />
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    public function sanitize( $value ): mixed {
        if ( is_string( $value ) ) {
            $decoded = json_decode( $value, true );
            if ( is_array( $decoded ) ) {
                return wp_json_encode( $this->sanitize_rows( $decoded ) );
            }
        }
        if ( is_array( $value ) ) {
            return wp_json_encode( $this->sanitize_rows( $value ) );
        }
        return '[]';
    }

    private function sanitize_rows( array $rows ): array {
        $clean = [];
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) continue;
            $clean_row = [];
            foreach ( $row as $k => $v ) {
                $clean_row[ sanitize_key( $k ) ] = sanitize_text_field( $v );
            }
            $clean[] = $clean_row;
        }
        return $clean;
    }
}
