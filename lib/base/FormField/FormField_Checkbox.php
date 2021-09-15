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
class FormField_Checkbox extends FormField_Base
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = (isset($options['typeField'])) ? $options['typeField'] : 'checkbox';
    }

    /**
     * Render a checkbox element.
     */
    public function show()
    {
        $options = $this->options;
        $name = (isset($options['name'])) ? 'name="' . $options['name'] . '"' : '';
        $nameHidden = (isset($options['name'])) ? 'name="hidden_verification_' . $options['name'] . '"' : '';
        $id = (isset($options['id'])) ? $options['id'] : substr(md5(rand()), 0, 5);
        $label = (isset($options['label'])) ? '<label for="' . $id . '">' . __($options['label']) . '</label>' : '';
        $value = (isset($options['value']) && $options['value'] == "1") ? 'checked="checked" ' : '';
        $disabled = (isset($options['disabled'])) ? 'disabled="disabled"' : '';
        $error = (isset($options['error']) && $options['error'] != '') ? '<div class="error_message">' . $options['error'] . '</div>' : '';
        $errorClass = (isset($options['error']) && $options['error'] != '') ? 'error' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        $class .= (isset($options['nameSimple'])) ? ' form_field_' . Text::simpleUrl($options['nameSimple'], '_') : '';
        $layout = (isset($options['layout'])) ? $options['layout'] : '';
        $object = (isset($options['object'])) ? $options['object'] : '';
        switch ($layout) {
            default:
                return '<div class="checkbox form_field ' . $class . '">
                            ' . $error . '
                            <div class="checkbox_ins">
                                <input type="checkbox" id="' . $id . '" ' . $name . ' ' . $value . ' ' . $disabled . '/>
                                <input type="hidden" ' . $nameHidden . ' value="true"/>
                                ' . $label . '
                            </div>
                        </div>';
                break;
            case 'simple':
                return '<input type="checkbox" id="' . $id . '" ' . $name . ' ' . $value . ' ' . $disabled . '/>
                        <input type="hidden" ' . $nameHidden . ' value="true"/>';
                break;
        }
    }

}
