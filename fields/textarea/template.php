<?php
/**
 * Text Field Template
 *
 * Available:
 * - $this : SNFS_Field_Text instance (extends SNFS_Field_Base)
 */
?>
<label>
    <strong><?php echo esc_html( $field->get_label() ); ?></strong>
</label>

<input
    type="textarea"
    name="<?php echo esc_attr( $field->get_name() ); ?>"
    value="<?php echo esc_attr( $field->get_value() ); ?>"
    class="sf-field sf-field-textarea"
/>