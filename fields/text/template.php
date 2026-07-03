<?php
/**
 * Text Field Template
 *
 * Available:
 * - $this : SNFS_Field_Text instance (extends SNFS_Field_Base)
 */
?>
<input
    type="text"
    <?php echo $field->render_attributes(); ?>
    value="<?php echo esc_attr( $field->get_value() ); ?>"
/>