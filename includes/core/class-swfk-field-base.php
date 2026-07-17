<?php
/**
 * Abstract base class for every field type.
 * Encapsulates field identity, context, rendering, sanitisation, validation,
 * and storage. All concrete field types extend this class.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class SWFK_Field_Base {

    // ── Identity ──────────────────────────────────────────────────────────────

    /** @var string Field type slug. Set in each child class. e.g. 'text', 'color'. */
    protected string $type = '';

    /** @var string Field name / programmatic key. e.g. 'hero_title'. */
    protected string $name;

    /** @var string Human-readable label shown in admin. */
    protected string $label;

    /** @var string DB meta key derived from name. e.g. 'swfk_hero_title'. */
    protected string $meta_key;

    // ── Config ────────────────────────────────────────────────────────────────

    /**
     * Field configuration args (merged with defaults).
     * Keys: default, required, placeholder, instructions, class, …
     * Child classes can add extra keys by overriding get_default_args().
     *
     * @var array
     */
    protected array $args = [];

    // ── Context ───────────────────────────────────────────────────────────────

    /**
     * Where this field lives.
     * Provides get_id(), get_type(), and the correct storage driver.
     *
     * @var SWFK_Context_Interface|null
     */
    protected ?SWFK_Context_Interface $context = null;

    /**
     * The numeric record ID extracted from the context for convenience.
     * 0 for options contexts.
     *
     * @var int
     */
    protected int $post_id = 0;

    // ── Value ─────────────────────────────────────────────────────────────────

    /**
     * The current saved value for this field, loaded in __construct().
     * Falls back to $args['default'] if nothing is stored.
     *
     * @var mixed
     */
    protected $value = null;

    // ── Constructor ───────────────────────────────────────────────────────────

    /**
     * @param string                    $name     Field name / programmatic key.
     * @param string                    $label    Human-readable label.
     * @param array                     $args     Config overrides (merged with defaults).
     * @param SWFK_Context_Interface|null $context  Where this field is being rendered.
     */
    public function __construct(
        string $name,
        string $label,
        array $args = [],
        ?SWFK_Context_Interface $context = null
    ) {
        $this->name     = sanitize_key( $name );
        $this->label    = $label;
        $this->args     = wp_parse_args( $args, $this->get_default_args() );
        $this->context  = $context;
        $this->post_id  = $context ? $context->get_id() : 0;
        $this->meta_key = $this->build_meta_key();
        $this->value    = $this->load_value();
    }

    // ── Overrideable: defaults ────────────────────────────────────────────────

    /**
     * Default args for this field type.
     * Override to add field-type-specific config keys, e.g. 'min', 'max', 'choices'.
     */
    protected function get_default_args(): array {
        return [
            'default'      => '',
            'required'     => false,
            'placeholder'  => '',
            'instructions' => '',
            'class'        => '',
        ];
    }

    // ── Overrideable: internals ───────────────────────────────────────────────

    /**
     * Build the storage meta key.
     * Default: 'swfk_{name}'.  Override for a different prefix or format.
     */
    protected function build_meta_key(): string {
        return 'swfk_' . $this->name;
    }

    /**
     * Load the current stored value via the context's storage driver.
     * Falls back to $args['default'].
     *
     * Override if your field type needs special value loading
     * (e.g. a repeater that loads an array and decodes JSON).
     */
    protected function load_value(): mixed {
        if ( $this->context ) {
            $value = $this->context->storage()->get( $this->meta_key, $this->post_id );
            // Treat empty string and null as "no value" to trigger default
            if ( $value !== '' && $value !== null ) {
                return $value;
            }
        }
        return $this->args['default'];
    }

    /**
     * Build the base HTML attributes array for the field element.
     *
     * Returns an array (not a string) so child classes can easily
     * add, remove, or override individual attributes before rendering.
     *
     * Included by default: id, name, class, required, placeholder.
     * Value is intentionally NOT included — it's passed at render time.
     */
    protected function build_attributes(): array {
        $class = trim( 'swfk-input swfk-input-' . $this->type . ' ' . ( $this->args['class'] ?? '' ) );

        $attrs = [
            'id'    => $this->meta_key,
            'name'  => $this->meta_key,
            'class' => $class,
        ];

        if ( ! empty( $this->args['required'] ) ) {
            $attrs['required'] = true; // rendered as bare attribute
        }

        if ( ! empty( $this->args['placeholder'] ) ) {
            $attrs['placeholder'] = $this->args['placeholder'];
        }

        return $attrs;
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Output the field HTML.
     *
     * $meta_key and $value are passed in by SWFK_Admin at render time so the
     * admin layer can pass the correct resolved key and current saved value.
     * This allows one field instance to be re-used in different contexts.
     *
     * Child classes MUST override this — this base version is a plain text
     * input that serves as a safe fallback only.
     *
     * @param string $meta_key  The input name + id (e.g. 'swfk_hero_title').
     * @param mixed  $value     Current saved value.
     */
    public function render( string $meta_key, $value ): void {
        do_action( 'swfk_before_field_render', $meta_key, $value, $this );

        $attrs          = $this->build_attributes();
        $attrs['type']  = 'text';
        $attrs['id']    = $meta_key;
        $attrs['name']  = $meta_key;
        $attrs['value'] = $value;

        echo '<input ' . $this->attrs_to_string( $attrs ) . ' />';

        if ( ! empty( $this->args['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $this->args['instructions'] ) . '</p>';
        }

        do_action( 'swfk_after_field_render', $meta_key, $value, $this );
    }

    // ── Save / Delete ─────────────────────────────────────────────────────────

    /**
     * Sanitize, validate, then persist a raw submitted value.
     *
     * @param mixed $raw_value  The raw value from $_POST.
     * @return bool             True on success.
     */
    public function save_value( $raw_value ): bool {
        $value = $this->sanitize( $raw_value );
        $valid = $this->validate( $value );

        if ( is_wp_error( $valid ) ) {
            return false;
        }

        if ( $this->context ) {
            return $this->context->storage()->save( $this->meta_key, $this->post_id, $value );
        }

        return false;
    }

    /**
     * Delete the stored value via the context's storage driver.
     */
    public function delete_value(): bool {
        if ( $this->context ) {
            $this->context->storage()->delete( $this->meta_key, $this->post_id );
            return true;
        }
        return false;
    }

    // ── Sanitize / Validate ───────────────────────────────────────────────────

    /**
     * Sanitize raw input before saving.
     * Override in child classes for type-specific sanitization.
     * e.g. SWFK_Field_Number::sanitize() would cast to int/float.
     *
     * @param mixed $value
     * @return mixed
     */
    public function sanitize( $value ): mixed {
        return sanitize_text_field( $value );
    }

    /**
     * Validate a sanitized value.
     * Override for custom rules (e.g. min/max on a number field).
     *
     * @param mixed $value
     * @return true|WP_Error
     */
    public function validate( $value ): true|WP_Error {
        if ( ! empty( $this->args['required'] ) && ( $value === '' || $value === null ) ) {
            return new WP_Error(
                'swfk_field_required',
                sprintf( __( 'The field "%s" is required.', 'swfk' ), $this->label )
            );
        }
        return true;
    }

    // ── Getters ───────────────────────────────────────────────────────────────

    public function get_name(): string    { return $this->name; }
    public function get_label(): string   { return $this->label; }
    public function get_type(): string    { return $this->type; }
    public function get_meta_key(): string{ return $this->meta_key; }
    public function get_args(): array     { return $this->args; }
    public function get_context(): ?SWFK_Context_Interface { return $this->context; }

    /** Get the currently loaded value. */
    public function get_value(): mixed    { return $this->value; }

    /** Override the in-memory value (does not persist). */
    public function set_value( $value ): void { $this->value = $value; }

    // ── HTML Helpers (available to all child classes) ─────────────────────────

    /**
     * Convert an attributes array to a safe HTML attribute string.
     *
     * - Skips keys with null or empty-string values.
     * - Renders boolean true as a bare attribute (e.g. 'required').
     * - All keys and values are escaped.
     *
     * @param array $attrs
     * @return string
     */
    protected function attrs_to_string( array $attrs ): string {
        $html = '';
        foreach ( $attrs as $key => $val ) {
            if ( $val === null || $val === '' ) {
                continue;
            }
            if ( $val === true ) {
                $html .= ' ' . esc_attr( $key );
                continue;
            }
            $html .= ' ' . esc_attr( $key ) . '="' . esc_attr( $val ) . '"';
        }
        return trim( $html );
    }

    /**
     * Shortcut: render the default attributes as an HTML string.
     * Useful in child class render() methods.
     *
     * @return string
     */
    public function render_attributes(): string {
        return $this->attrs_to_string( $this->build_attributes() );
    }
}