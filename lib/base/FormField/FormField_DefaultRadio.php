<?php
/**
 * @class FormFieldDefaultRadio
 *
 * This is a helper class to generate a default radio form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_DefaultRadio extends FormField_Base
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = (isset($options['typeField'])) ? $options['typeField'] : 'radio';
        //Load the values
        $refObject = ($this->item) ? (string) $this->item->refObject : '';
        if (isset($options['value'])) {
            $this->options['value'] = $options['value'];
        } else if ($refObject != '') {
            $refObjectIns = new $refObject();
            $this->options['value'] = $refObjectIns->basicInfoAdminArray();
        } else {
            $this->options['value'] = [];
            $i = 0;
            foreach ($this->item->values->value as $itemIns) {
                $id = (isset($itemIns['id'])) ? (string) $itemIns['id'] : $i;
                $this->options['value'][$id] = (string) $itemIns;
                $i++;
            }
        }
        //Load the selected values
        $this->options['selected'] = (isset($options['selected'])) ? $options['selected'] : ((isset($this->values[$this->name])) ? $this->values[$this->name] : '');
        $this->options['selected'] = ($this->options['selected'] == '' && $this->options['default'] != '') ? $this->options['default'] : $this->options['selected'];
    }

    /**
     * Render a radio element with an static function.
     */
    public function show()
    {
        $options = $this->options;
        $typeField = (isset($options['typeField'])) ? 'type="' . $options['typeField'] . '" ' : 'type="text"';
        $name = (isset($options['name'])) ? 'name="' . $options['name'] . '" ' : '';
        $nameRadio = (isset($options['name'])) ? $options['name'] : '';
        $id = (isset($options['id'])) ? 'id="' . $options['id'] . '"' : '';
        $labelFor = (isset($options['id'])) ? ' for="' . $options['id'] . '"' : '';
        $label = (isset($options['label']) && $options['label'] != '') ? '<label' . $labelFor . '>' . __($options['label']) . '</label>' : '';
        $value = (isset($options['value'])) ? $options['value'] : '';
        $selected = (isset($options['selected'])) ? $options['selected'] : '';
        $disabled = (isset($options['disabled']) && $options['disabled'] != false) ? 'disabled="disabled"' : '';
        $multiple = (isset($options['multiple'])) ? 'multiple="multiple"' : '';
        $size = (isset($options['size'])) ? 'size="' . $options['size'] . '" ' : '';
        $error = (isset($options['error']) && $options['error'] != '') ? '<div class="error_message">' . $options['error'] . '</div>' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        $class .= (isset($options['nameSimple'])) ? ' form_field_' . Text::simpleUrl($options['nameSimple'], '_') : '';
        $errorClass = (isset($options['error']) && $options['error'] != '') ? 'error' : '';
        $placeholder = (isset($options['placeholder'])) ? 'placeholder="' . __($options['placeholder']) . '"' : '';
        $layout = (isset($options['layout'])) ? $options['layout'] : '';
        $htmlOptions = '';
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $isSelected = ($key == $selected || (is_array($selected) && in_array($key, $selected))) ? 'checked="checked"' : '';
                $labelId = $nameRadio . '_' . $key;
                $htmlOptions .= '<div class="radio_value">
                                    <input type="radio" ' . $name . ' class="radio_item radio_item_' . $key . '" value="' . $key . '" ' . $isSelected . ' id="' . $labelId . '"/>
                                    <label for="' . $labelId . '">' . __($item) . '</label>
                                </div>';
            }
        }
        switch ($layout) {
            default:
                return '<div class="radio form_field ' . $class . ' ' . $errorClass . '">
                            <div class="form_field_ins">
                                ' . $label . '
                                ' . $error . '
                                ' . $htmlOptions . '
                            </div>
                        </div>';
                break;
            case 'simple':
                return $htmlOptions;
                break;
        }
    }

}
