<?php
/**
 * Range slider field. Renders an HTML5 range input with min/max/step and a live value display.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Range slider field — <input type="range">.
 * Displays the current numeric value live beside the slider.
 * Stores a numeric value.
 *
 * @since 1.0.0
 */
class SWFK_Field_Range extends SWFK_Field_Base {

    protected string $type = 'range';

    protected function get_default_args(): array {
        return array_merge( parent::get_default_args(), [
            'min'     => '0',
            'max'     => '100',
            'step'    => '1',
            'default' => '50',
        ] );
    }

    public function render( string $meta_key, $value ): void {
        $val      = ( $value !== '' && $value !== null ) ? $value : $this->args['default'];
        $uid      = 'swfk-range-output-' . sanitize_html_class( $meta_key );
        $min      = esc_attr( $this->args['min'] );
        $max      = esc_attr( $this->args['max'] );
        $step     = esc_attr( $this->args['step'] );
        $val_esc  = esc_attr( $val );
        $uid_esc  = esc_attr( $uid );

        // The oninput attribute is safe: $uid_esc is sanitized above.
        $attrs = $this->build_attributes();
        $attrs['type']  = 'range';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = $val_esc;
        $attrs['min']   = $min;
        $attrs['max']   = $max;
        $attrs['step']  = $step;
        // oninput is added separately in the HTML below to avoid attribute-value escaping issues.
        unset( $attrs['oninput'] );
        ?>
        <div class="swfk-range-wrap">
            <div class="swfk-range-track">
                <?php $this->render_input( $attrs ); ?>
            </div>
            <span class="swfk-range-value" id="<?php echo esc_attr( $uid ); ?>">
                <?php echo esc_html( $val ); ?>
            </span>
        </div>
        <script>
        (function() {
            var inp = document.getElementById( <?php echo wp_json_encode( $meta_key ); ?> );
            var out = document.getElementById( <?php echo wp_json_encode( $uid ); ?> );
            if ( inp && out ) {
                inp.addEventListener( 'input', function() { out.textContent = this.value; } );
            }
        })();
        </script>
        <?php
        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }
    }

    public function sanitize( $value ): mixed {
        return is_numeric( $value ) ? $value + 0 : (int) $this->args['default'];
    }
}
