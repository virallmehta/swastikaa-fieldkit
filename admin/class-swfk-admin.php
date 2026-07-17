<?php
/**
 * Admin — Field Group Builder + Runtime Field Display.
 * Handles metaboxes, field group saves, asset enqueueing, and field rendering across
 * posts, taxonomy terms, users, and options screens.
 *
 * @package SwastikaaFieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_Admin {

    public function __construct() {

        // ── Assets ───────────────────────────────────────────────────────────
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_location_data' ] );

        // ── Field Group Builder ──────────────────────────────────────────────
        add_action( 'add_meta_boxes_swfk_field_group', [ $this, 'add_field_group_metaboxes' ] );
        add_action( 'save_post_swfk_field_group',      [ $this, 'save_field_group' ] );

        // ── Runtime: Posts ───────────────────────────────────────────────────
        add_action( 'add_meta_boxes', [ $this, 'add_post_metaboxes' ], 10, 2 );
        add_action( 'save_post',      [ $this, 'save_post_fields' ] );
        add_action( 'init', function() {
            foreach ( get_post_types( [ 'show_in_rest' => true ], 'names' ) as $pt ) {
                if ( $pt === 'swfk_field_group' ) continue;
                add_action( "rest_after_insert_{$pt}", [ $this, 'save_post_fields_rest' ], 10, 1 );
            }
        }, 100 );

        // ── Runtime: Taxonomy terms ──────────────────────────────────────────
        add_action( 'init', [ $this, 'register_taxonomy_hooks' ], 99 );

        // ── Runtime: User profiles ───────────────────────────────────────────
        add_action( 'show_user_profile',        [ $this, 'render_user_fields' ] );
        add_action( 'edit_user_profile',        [ $this, 'render_user_fields' ] );
        add_action( 'user_new_form',             [ $this, 'render_new_user_fields' ] );
        add_action( 'personal_options_update',  [ $this, 'save_user_fields' ] );
        add_action( 'edit_user_profile_update', [ $this, 'save_user_fields' ] );
        add_action( 'user_register',            [ $this, 'save_user_fields' ] );
    }

    // =========================================================================
    // ASSETS
    // =========================================================================

    public function enqueue_assets( string $hook ): void {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return;
        }

        if ( in_array( $hook, [ 'post.php', 'post-new.php', 'profile.php', 'user-edit.php' ], true )
            || $screen->post_type === 'swfk_field_group' ) {
            wp_enqueue_style(
                'swfk-admin-css',
                SWFK_PLUGIN_URL . 'assets/css/admin.css',
                [],
                SWFK_VERSION
            );
        }

        $is_runtime_screen = in_array( $hook, [
            'post.php', 'post-new.php',
            'profile.php', 'user-edit.php',
            'user-new.php',
            'term.php', 'edit-tags.php',
        ], true );

        if ( $is_runtime_screen ) {
            wp_enqueue_media();
            wp_enqueue_script(
                'swfk-admin-fields',
                SWFK_PLUGIN_URL . 'assets/js/swfk-admin-fields.js',
                [ 'jquery', 'media-editor' ],
                SWFK_VERSION,
                true
            );
            wp_localize_script( 'swfk-admin-fields', 'swfkAdmin', [
                'i18n' => [
                    'selectImage' => __( 'Select Image', 'swastikaa-fieldkit' ),
                    'useImage'    => __( 'Use Image', 'swastikaa-fieldkit' ),
                    'changeImage' => __( 'Change Image', 'swastikaa-fieldkit' ),
                    'selectFile'  => __( 'Select File', 'swastikaa-fieldkit' ),
                    'useFile'     => __( 'Use File', 'swastikaa-fieldkit' ),
                    'changeFile'  => __( 'Change File', 'swastikaa-fieldkit' ),
                    'addImages'   => __( 'Add Images', 'swastikaa-fieldkit' ),
                    'remove'      => __( 'Remove', 'swastikaa-fieldkit' ),
                ],
            ]);
        }

        if ( $screen->post_type !== 'swfk_field_group' ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script(
            'swfk-admin-js',
            SWFK_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery', 'jquery-ui-sortable' ],
            SWFK_VERSION,
            true
        );
    }

    public function enqueue_location_data(): void {
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'swfk_field_group' ) {
            return;
        }

        $post_types = [];
        foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $pt ) {
            $post_types[ $pt->name ] = $pt->label;
        }

        $taxonomies = [];
        foreach ( get_taxonomies( [ 'public' => true ], 'objects' ) as $tax ) {
            $taxonomies[ $tax->name ] = $tax->label;
        }

        wp_localize_script( 'swfk-admin-js', 'sfLocationDataSet', [
            'postTypes'  => $post_types,
            'taxonomies' => $taxonomies,
        ] );
    }

    // =========================================================================
    // FIELD GROUP BUILDER METABOXES
    // =========================================================================

    public function add_field_group_metaboxes(): void {
        add_meta_box(
            'swfk-field-builder',
            'SwastiNexus Field Builder',
            [ $this, 'render_field_builder' ],
            'swfk_field_group',
            'normal',
            'high'
        );

        add_meta_box(
            'swfk-location-rules',
            'Location Rules',
            [ $this, 'render_location_rules' ],
            'swfk_field_group',
            'side',
            'default'
        );
    }

    // =========================================================================
    // FIELD BUILDER RENDER
    // =========================================================================

    public function render_field_builder( WP_Post $post ): void {
        $fields      = get_post_meta( $post->ID, 'swfk_fields', true ) ?: [];
        $field_types = SWFK_Field_Registry::get_all();

        wp_nonce_field( 'swfk_save_field_group', 'swfk_fields_nonce' );
        ?>

        <input type="hidden" id="swfk-fields-json" name="swfk_fields"
            value="<?php echo esc_attr( wp_json_encode( $fields ) ); ?>">

        <button type="button" id="swfk-add-field" class="button button-primary">+ Add Field</button>

        <template id="swfk-field-row-template">
            <?php $this->render_field_row( '__INDEX__', [], $field_types ); ?>
        </template>

        <div id="swfk-fields-container" class="swfk-fields-grid">
            <?php if ( empty( $fields ) ) : ?>
                <div class="swfk-placeholder">No fields yet. Click "Add Field" to start.</div>
            <?php else : ?>
                <?php foreach ( $fields as $index => $field ) : ?>
                    <?php $this->render_field_row( (string) $index, $field, $field_types ); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_field_row( string $index, array $field, array $field_types ): void {
        $label        = $field['label']        ?? '';
        $name         = $field['name']         ?? '';
        $type         = $field['type']         ?? '';
        $required     = ! empty( $field['required'] );
        $default      = $field['default']      ?? '';
        $placeholder  = $field['placeholder']  ?? '';
        $instructions = $field['instructions'] ?? '';
        ?>
        <div class="swfk-field-row" data-index="<?php echo esc_attr( $index ); ?>">
            <div class="swfk-field-header">
                <span class="swfk-field-handle dashicons dashicons-move" title="Drag to reorder"></span>
                <span class="swfk-field-title" data-field-label="<?php echo esc_attr( $label ); ?>">
                    <?php echo esc_html( $label ?: 'New Field' ); ?>
                </span>
                <div class="swfk-field-controls">
                    <button type="button" class="swfk-field-toggle button-link" title="Expand/Collapse">
                        <span class="dashicons dashicons-arrow-down-alt2 toggle-expanded"></span>
                        <span class="dashicons dashicons-arrow-up-alt2 toggle-collapsed" style="display:none;"></span>
                    </button>
                    <button type="button" class="swfk-field-duplicate button-link" title="Duplicate">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                    <button type="button" class="swfk-field-remove button-link-delete" title="Remove">Remove</button>
                </div>
            </div>

            <div class="swfk-field-body">
                <div class="swfk-row-grid">
                    <div class="swfk-col">
                        <label>Field Label</label>
                        <input type="text"
                            class="swfk-field-label"
                            value="<?php echo esc_attr( $label ); ?>"
                            placeholder="Field Label" />
                    </div>

                    <div class="swfk-col">
                        <label>Field Name <small>(key)</small></label>
                        <input type="text"
                            class="swfk-field-name"
                            value="<?php echo esc_attr( $name ); ?>"
                            placeholder="field_name" />
                        <small>Lowercase, underscores only</small>
                    </div>

                    <div class="swfk-col">
                        <label>Field Type</label>
                        <select class="swfk-field-type">
                            <?php foreach ( $field_types as $type_key => $type_info ) : ?>
                                <option value="<?php echo esc_attr( $type_key ); ?>"
                                    <?php echo selected( $type, $type_key, false ); ?>>
                                    <?php echo esc_html( $type_info['label'] ?? $type_key ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="swfk-col swfk-col-required">
                        <label>Required</label>
                        <label class="swfk-toggle-switch">
                            <input type="checkbox"
                                class="swfk-field-required"
                                value="1"
                                <?php echo checked( $required, true, false ); ?> />
                            <span class="swfk-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <?php
                $choices_raw   = $field['choices_raw'] ?? '';
                $choices_types = [ 'checkbox', 'radio', 'select' ];
                $show_choices  = in_array( $type, $choices_types, true );
                $range_types   = [ 'range', 'number' ];
                $show_range    = in_array( $type, $range_types, true );
                $min           = $field['min']  ?? '';
                $max           = $field['max']  ?? '';
                $step          = $field['step'] ?? '';
                ?>

                <div class="swfk-choices-wrap" style="<?php echo $show_choices ? '' : 'display:none;'; ?> padding:12px 16px 0;">
                    <label style="font-weight:600;">
                        Choices
                        <small style="font-weight:normal;color:#666;"> — one per line, format: <code>value : Label</code></small>
                    </label>
                    <textarea
                        class="swfk-field-choices"
                        rows="5"
                        style="width:100%;font-family:monospace;font-size:12px;"
                        placeholder="Option 1 : Option 1&#10;Option 2 : Option 2&#10;Option 3 : Option 3"><?php echo esc_textarea( $choices_raw ); ?></textarea>
                </div>

                <div class="swfk-range-wrap" style="<?php echo $show_range ? '' : 'display:none;'; ?> padding:12px 16px 0;">
                    <div class="swfk-row-grid">
                        <div class="swfk-col">
                            <label>Min</label>
                            <input type="number" class="swfk-field-min"
                                value="<?php echo esc_attr( $min ); ?>" placeholder="e.g. 0" step="any" />
                        </div>
                        <div class="swfk-col">
                            <label>Max</label>
                            <input type="number" class="swfk-field-max"
                                value="<?php echo esc_attr( $max ); ?>" placeholder="e.g. 100" step="any" />
                        </div>
                        <div class="swfk-col">
                            <label>Step</label>
                            <input type="number" class="swfk-field-step"
                                value="<?php echo esc_attr( $step ); ?>" placeholder="e.g. 1" step="any" />
                        </div>
                    </div>
                </div>

                <div class="swfk-advanced-options">
                    <div class="swfk-row-grid">
                        <div class="swfk-col">
                            <label>Default Value</label>
                            <input type="text"
                                class="swfk-field-default"
                                value="<?php echo esc_attr( $default ); ?>"
                                placeholder="(optional)" />
                        </div>
                        <div class="swfk-col">
                            <label>Placeholder</label>
                            <input type="text"
                                class="swfk-field-placeholder"
                                value="<?php echo esc_attr( $placeholder ); ?>"
                                placeholder="(optional)" />
                        </div>
                        <div class="swfk-col">
                            <label>Instructions</label>
                            <input type="text"
                                class="swfk-field-instructions"
                                value="<?php echo esc_attr( $instructions ); ?>"
                                placeholder="Help text shown to editors" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // =========================================================================
    // LOCATION RULES RENDER
    // =========================================================================

    public function render_location_rules( WP_Post $post ): void {
        $rules      = get_post_meta( $post->ID, 'swfk_location_rules', true ) ?: [];
        $post_types = get_post_types( [ 'public' => true ], 'objects' );
        $taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
        ?>
        <div style="margin-bottom:15px;">
            <p class="description" style="margin-top:0;">
                Choose where this field group should appear.<br>
                <small>Rules use OR logic — any match shows the group.</small>
            </p>
        </div>

        <div id="swfk-location-rules-container"
            style="background:#f9f9f9; border:1px solid #ddd; padding:15px; border-radius:4px; min-height:80px;">
            <?php if ( empty( $rules ) ) : ?>
                <div class="swfk-rules-placeholder"
                    style="padding:20px; text-align:center; color:#8d9db3; font-style:italic;">
                    No location rules set. Field group will not show anywhere.
                </div>
            <?php else : ?>
                <?php foreach ( $rules as $index => $rule ) : ?>
                    <?php $this->render_location_rule( (string) $index, $rule, $post_types, $taxonomies ); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <p style="margin-top:15px;">
            <button type="button" id="swfk-add-location-rule"
                class="button button-secondary" style="width:100%;">
                + Add Location Rule
            </button>
        </p>

        <input type="hidden" id="swfk-location-rules-json" name="swfk_location_rules"
            value="<?php echo esc_attr( wp_json_encode( $rules ) ); ?>" />

        <script type="text/template" id="swfk-location-rule-template" style="display:none;">
            <?php $this->render_location_rule( '__RULE_INDEX__', [], $post_types, $taxonomies ); ?>
        </script>
        <?php
    }

    private function render_location_rule( string $index, array $rule, $post_types, $taxonomies ): void {
        $type     = $rule['type']     ?? 'post_type';
        $operator = $rule['operator'] ?? 'is';
        $value    = $rule['value']    ?? '';
        ?>
        <div class="swfk-location-rule" data-rule-index="<?php echo esc_attr( $index ); ?>">
            <div class="swfk-rule-row">
                <div class="swfk-col">
                    <label>Type</label>
                    <select class="swfk-rule-type" data-rule="<?php echo esc_attr( $index ); ?>[type]">
                        <option value="post_type"    <?php echo selected( $type, 'post_type',    false ); ?>>Post Type</option>
                        <option value="taxonomy"     <?php echo selected( $type, 'taxonomy',     false ); ?>>Taxonomy</option>
                        <option value="user_profile" <?php echo selected( $type, 'user_profile', false ); ?>>User Profile</option>
                    </select>
                </div>
                <div class="swfk-col">
                    <label>Show when</label>
                    <select class="swfk-rule-operator" data-rule="<?php echo esc_attr( $index ); ?>[operator]">
                        <option value="is"     <?php echo selected( $operator, 'is',     false ); ?>>is equal to</option>
                        <option value="is_not" <?php echo selected( $operator, 'is_not', false ); ?>>is NOT equal to</option>
                    </select>
                </div>
                <div class="swfk-col-auto">
                    <label>Value</label>
                    <select class="swfk-rule-value" data-rule="<?php echo esc_attr( $index ); ?>[value]"
                        style="width:100%;">
                        <!-- Populated by JS -->
                    </select>
                </div>
                <div>
                    <button type="button" class="swfk-rule-remove button-link-delete"
                        style="width:100%; margin-top:20px;">Remove Rule</button>
                </div>
            </div>
        </div>
        <?php
    }

    // =========================================================================
    // SAVE FIELD GROUP
    // =========================================================================

    public function save_field_group( int $post_id ): void {
        if ( ! isset( $_POST['swfk_fields_nonce'] ) ||
             ! wp_verify_nonce( $_POST['swfk_fields_nonce'], 'swfk_save_field_group' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = [];
        if ( ! empty( $_POST['swfk_fields'] ) ) {
            $decoded = json_decode( wp_unslash( $_POST['swfk_fields'] ), true );
            if ( is_array( $decoded ) ) {
                foreach ( $decoded as $field ) {
                    if ( empty( $field['name'] ) ) {
                        continue;
                    }
                    $fields[] = [
                        'name'         => sanitize_key( $field['name'] ),
                        'label'        => sanitize_text_field( $field['label']        ?? '' ),
                        'type'         => sanitize_key( $field['type']                ?? 'text' ),
                        'required'     => ! empty( $field['required'] ) ? 1 : 0,
                        'default'      => sanitize_text_field( $field['default']      ?? '' ),
                        'placeholder'  => sanitize_text_field( $field['placeholder']  ?? '' ),
                        'instructions' => sanitize_text_field( $field['instructions'] ?? '' ),
                        'choices_raw'  => sanitize_textarea_field( $field['choices_raw'] ?? '' ),
                        'min'          => sanitize_text_field( $field['min']          ?? '' ),
                        'max'          => sanitize_text_field( $field['max']          ?? '' ),
                        'step'         => sanitize_text_field( $field['step']         ?? '' ),
                    ];
                }
            }
        }
        update_post_meta( $post_id, 'swfk_fields', $fields );

        $rules = [];
        if ( ! empty( $_POST['swfk_location_rules'] ) ) {
            $decoded = json_decode( wp_unslash( $_POST['swfk_location_rules'] ), true );
            if ( is_array( $decoded ) ) {
                foreach ( $decoded as $rule ) {
                    if ( empty( $rule['type'] ) ) {
                        continue;
                    }
                    $rules[] = [
                        'type'     => sanitize_key( $rule['type']     ?? '' ),
                        'operator' => sanitize_key( $rule['operator'] ?? 'is' ),
                        'value'    => sanitize_key( $rule['value']    ?? '' ),
                    ];
                }
            }
        }
        update_post_meta( $post_id, 'swfk_location_rules', $rules );

        SWFK_Field_Group_Repository::clear_cache();
    }

    // =========================================================================
    // RUNTIME: POST FIELDS
    // =========================================================================

    public function add_post_metaboxes( string $post_type, WP_Post $post ): void {
        if ( $post_type === 'swfk_field_group' ) {
            return;
        }

        $context = new SWFK_Post_Context( $post->ID, get_post_type( $post->ID ) ?: $post->post_type );
        $groups  = SWFK_Field_Group_Repository::get_for_context( $context );

        foreach ( $groups as $group_id => $group_data ) {
            add_meta_box(
                'swfk-fields-' . $group_id,
                esc_html( $group_data['post']->post_title ),
                [ $this, 'render_post_metabox' ],
                $post_type,
                'normal',
                'high',
                [ 'fields' => $group_data['fields'] ]
            );
        }
    }

    public function render_post_metabox( WP_Post $post, array $metabox ): void {
        $fields  = $metabox['args']['fields'] ?? [];
        $context = new SWFK_Post_Context( $post->ID, get_post_type( $post->ID ) ?: $post->post_type );

        wp_nonce_field( 'swfk_save_post_fields', 'swfk_post_fields_nonce' );

        if ( empty( $fields ) ) {
            echo '<p>No fields in this group.</p>';
            return;
        }

        foreach ( array_unique( array_column( $fields, 'type' ) ) as $_type ) {
            SWFK_Assets_Manager::enqueue( $_type );
        }

        echo '<table class="form-table swfk-runtime-table">';
        foreach ( $fields as $field ) {
            $this->render_runtime_field_row( $field, $context );
        }
        echo '</table>';
    }

    public function save_post_fields( int $post_id ): void {
        if ( ! isset( $_POST['swfk_post_fields_nonce'] ) ||
             ! wp_verify_nonce( $_POST['swfk_post_fields_nonce'], 'swfk_save_post_fields' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( get_post_type( $post_id ) === 'swfk_field_group' ) {
            return;
        }

        $this->save_fields_for_context( new SWFK_Post_Context( $post_id ) );
    }

    public function save_post_fields_rest( WP_Post $post ): void {
        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return;
        }
        if ( $post->post_type === 'swfk_field_group' ) {
            return;
        }

        $request = json_decode( file_get_contents( 'php://input' ), true );
        if ( empty( $request['meta'] ) || ! is_array( $request['meta'] ) ) {
            return;
        }

        $context = new SWFK_Post_Context( $post->ID, $post->post_type );
        $groups  = SWFK_Field_Group_Repository::get_for_context( $context );

        foreach ( $groups as $group_data ) {
            foreach ( $group_data['fields'] as $field ) {
                $meta_key = 'swfk_' . $field['name'];
                if ( ! array_key_exists( $meta_key, $request['meta'] ) ) {
                    continue;
                }
                $raw      = $request['meta'][ $meta_key ];
                $instance = SWFK_Field_Registry::get_instance( $field['type'], $field, $context );
                $value    = $instance ? $instance->sanitize( $raw ) : sanitize_text_field( $raw );
                $context->storage()->save( $meta_key, $post->ID, $value );
            }
        }
    }

    // =========================================================================
    // RUNTIME: TAXONOMY FIELDS
    // =========================================================================

    public function register_taxonomy_hooks(): void {
        $all_taxonomies = get_taxonomies( [ 'public' => true ], 'names' );
        foreach ( $all_taxonomies as $taxonomy ) {
            add_action( "{$taxonomy}_add_form_fields",
                fn( $tax )             => $this->render_taxonomy_add_fields( $tax ),
                10
            );
            add_action( "{$taxonomy}_edit_form_fields",
                fn( $term, $tax )      => $this->render_taxonomy_edit_fields( $term, $tax ),
                10, 2
            );
            add_action( "created_{$taxonomy}",
                fn( $term_id, $tt_id ) => $this->save_taxonomy_fields( $term_id, $taxonomy ),
                10, 2
            );
            add_action( "edited_{$taxonomy}",
                fn( $term_id, $tt_id ) => $this->save_taxonomy_fields( $term_id, $taxonomy ),
                10, 2
            );
        }
    }

    private function render_taxonomy_add_fields( string $taxonomy ): void {
        $context = new SWFK_Term_Context( 0, $taxonomy );
        $groups  = SWFK_Field_Group_Repository::get_for_context( $context );

        if ( empty( $groups ) ) {
            return;
        }

        wp_nonce_field( 'swfk_save_tax_fields', 'swfk_tax_nonce' );

        foreach ( $groups as $group_data ) {
            echo '<div class="swfk-tax-group">';
            echo '<h3>' . esc_html( $group_data['post']->post_title ) . '</h3>';
            foreach ( $group_data['fields'] as $field ) {
                $meta_key = 'swfk_' . $field['name'];
                $instance = SWFK_Field_Registry::get_instance( $field['type'], $field, $context );
                echo '<div class="form-field' . ( ! empty( $field['required'] ) ? ' form-required' : '' ) . '">';
                echo '<label for="' . esc_attr( $meta_key ) . '">' . esc_html( $field['label'] );
                if ( ! empty( $field['required'] ) ) {
                    echo ' <span style="color:#d63638;">*</span>';
                }
                echo '</label>';
                if ( $instance ) {
                    $instance->set_value( $field['default'] ?? '' );
                    $instance->render( $meta_key, $field['default'] ?? '' );
                }
                if ( ! empty( $field['instructions'] ) ) {
                    echo '<p class="description">' . esc_html( $field['instructions'] ) . '</p>';
                }
                echo '</div>';
            }
            echo '</div>';
        }
    }

    private function render_taxonomy_edit_fields( WP_Term $term, string $taxonomy ): void {
        $context = new SWFK_Term_Context( $term->term_id, $taxonomy );
        $groups  = SWFK_Field_Group_Repository::get_for_context( $context );

        if ( empty( $groups ) ) {
            return;
        }

        wp_nonce_field( 'swfk_save_tax_fields', 'swfk_tax_nonce' );

        foreach ( $groups as $group_data ) {
            echo '<tr class="swfk-tax-group-header"><td colspan="2">';
            echo '<h3 style="margin:8px 0;">' . esc_html( $group_data['post']->post_title ) . '</h3>';
            echo '</td></tr>';

            foreach ( $group_data['fields'] as $field ) {
                $meta_key = 'swfk_' . $field['name'];
                $value    = get_term_meta( $term->term_id, $meta_key, true );
                $value    = ( $value !== '' && $value !== false ) ? $value : ( $field['default'] ?? '' );
                $instance = SWFK_Field_Registry::get_instance( $field['type'], $field, $context );

                echo '<tr class="form-field">';
                echo '<th scope="row"><label for="' . esc_attr( $meta_key ) . '">';
                echo esc_html( $field['label'] );
                if ( ! empty( $field['required'] ) ) {
                    echo ' <span style="color:#d63638;">*</span>';
                }
                echo '</label></th>';
                echo '<td>';
                if ( $instance ) {
                    $instance->set_value( $value );
                    $instance->render( $meta_key, $value );
                }
                if ( ! empty( $field['instructions'] ) ) {
                    echo '<p class="description">' . esc_html( $field['instructions'] ) . '</p>';
                }
                echo '</td></tr>';
            }
        }
    }

    private function save_taxonomy_fields( int $term_id, string $taxonomy ): void {
        if ( ! isset( $_POST['swfk_tax_nonce'] ) ||
             ! wp_verify_nonce( $_POST['swfk_tax_nonce'], 'swfk_save_tax_fields' ) ) {
            return;
        }
        SWFK_Field_Group_Repository::clear_cache();
        $this->save_fields_for_context( new SWFK_Term_Context( $term_id, $taxonomy ) );
    }

    // =========================================================================
    // RUNTIME: USER PROFILE FIELDS
    // =========================================================================

    public function render_user_fields( WP_User $user ): void {
        $context = new SWFK_User_Context( $user->ID );
        $groups  = SWFK_Field_Group_Repository::get_for_context( $context );

        if ( empty( $groups ) ) {
            return;
        }

        wp_nonce_field( 'swfk_save_user_fields', 'swfk_user_fields_nonce' );

        foreach ( $groups as $group_data ) {
            echo '<h2>' . esc_html( $group_data['post']->post_title ) . '</h2>';
            echo '<table class="form-table swfk-runtime-table">';
            foreach ( $group_data['fields'] as $field ) {
                $this->render_runtime_field_row( $field, new SWFK_User_Context( $user->ID ) );
            }
            echo '</table>';
        }
    }

    public function render_new_user_fields( string $type ): void {
        $context = new SWFK_User_Context( 0 );
        $groups  = SWFK_Field_Group_Repository::get_for_context( $context );

        if ( empty( $groups ) ) {
            return;
        }

        wp_nonce_field( 'swfk_save_user_fields', 'swfk_user_fields_nonce' );

        foreach ( $groups as $group_data ) {
            echo '<h3>' . esc_html( $group_data['post']->post_title ) . '</h3>';
            echo '<table class="form-table swfk-runtime-table">';
            foreach ( $group_data['fields'] as $field ) {
                $meta_key = 'swfk_' . $field['name'];
                $instance = SWFK_Field_Registry::get_instance( $field['type'], $field, $context );
                echo '<tr>';
                echo '<th><label for="' . esc_attr( $meta_key ) . '">' . esc_html( $field['label'] );
                if ( ! empty( $field['required'] ) ) {
                    echo ' <span style="color:#d63638;">*</span>';
                }
                echo '</label></th><td>';
                if ( $instance ) {
                    $instance->set_value( $field['default'] ?? '' );
                    $instance->render( $meta_key, $field['default'] ?? '' );
                }
                echo '</td></tr>';
            }
            echo '</table>';
        }
    }

    public function save_user_fields( int $user_id ): void {
        if ( ! isset( $_POST['swfk_user_fields_nonce'] ) ||
             ! wp_verify_nonce( $_POST['swfk_user_fields_nonce'], 'swfk_save_user_fields' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }
        $this->save_fields_for_context( new SWFK_User_Context( $user_id ) );
    }

    // =========================================================================
    // SHARED HELPERS
    // =========================================================================

    private function render_runtime_field_row( array $field, SWFK_Context_Interface $context ): void {
        $meta_key = 'swfk_' . $field['name'];
        $stored   = $context->storage()->get( $meta_key, $context->get_id() );
        $value    = ( $stored !== '' && $stored !== null && $stored !== false )
            ? $stored
            : ( $field['default'] ?? '' );

        $instance = SWFK_Field_Registry::get_instance( $field['type'], $field, $context );

        echo '<tr>';
        echo '<th scope="row">';
        echo '<label for="' . esc_attr( $meta_key ) . '">' . esc_html( $field['label'] );
        if ( ! empty( $field['required'] ) ) {
            echo ' <span style="color:#d63638;">*</span>';
        }
        echo '</label>';
        if ( ! empty( $field['instructions'] ) ) {
            echo '<p class="description">' . esc_html( $field['instructions'] ) . '</p>';
        }
        echo '</th><td>';
        if ( $instance ) {
            $instance->set_value( $value );
            $instance->render( $meta_key, $value );
        } else {
            echo '<p style="color:#d63638;">Unknown field type: <code>'
                . esc_html( $field['type'] ) . '</code></p>';
        }
        echo '</td></tr>';
    }

    private function save_fields_for_context( SWFK_Context_Interface $context ): void {
        $groups = SWFK_Field_Group_Repository::get_for_context( $context );

        foreach ( $groups as $group_data ) {
            foreach ( $group_data['fields'] as $field ) {
                $meta_key = 'swfk_' . $field['name'];
                $in_post  = isset( $_POST[ $meta_key ] );

                if ( ! $in_post ) {
                    continue;
                }

                $raw      = wp_unslash( $_POST[ $meta_key ] );
                $instance = SWFK_Field_Registry::get_instance( $field['type'], $field, $context );
                $value    = $instance
                    ? $instance->sanitize( $raw )
                    : sanitize_text_field( $raw );

                $context->storage()->save( $meta_key, $context->get_id(), $value );
            }
        }
    }
}

new SWFK_Admin();