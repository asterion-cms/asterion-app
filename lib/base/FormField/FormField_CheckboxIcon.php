<?php
/**
 * @class FormFieldCheckbox
 *
 * This is a helper class to generate checkboxes.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_CheckboxIcon extends FormField_Checkbox
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = (isset($options['typeField'])) ? $options['typeField'] : 'checkbox';
        $this->options['class'] = 'checkbox_icons_wrapper';
        $this->options['label'] = '
            <div class="checkbox_icons">
                <i class="far fa-square checkbox_icon_unchecked"></i>
                <i class="far fa-check-square checkbox_icon_checked"></i>
            </div>';
    }

}
