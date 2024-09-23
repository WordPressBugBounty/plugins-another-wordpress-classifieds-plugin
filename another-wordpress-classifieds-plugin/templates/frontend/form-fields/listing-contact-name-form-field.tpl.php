<?php
/**
 * @package AWPCP\Templates\FormFields
 */

?><p class="awpcp-form-field awpcp-clearfix   awpcp-form-spacer">
    <label class="awpcp-form-field__label" for="<?php echo esc_attr( $html['id'] ); ?>"><?php echo esc_html( $label ); ?><?php echo $required ? '<span class="required">*</span>' : ''; ?></label>
    <input class="awpcp-textfield awpcp-has-value inputbox" id="<?php echo esc_attr( $html['id'] ); ?>" <?php echo $html['readonly'] ? 'readonly="readonly"' : ''; ?> type="text" size="50" name="<?php echo esc_attr( $html['name'] ); ?>" value="<?php echo awpcp_esc_attr( $value ); ?>" <?php echo $required ? 'required' : ''; ?>/>
    <?php awpcp_show_form_error( $html['name'], $errors ); ?>
</p>
