<?php
/**
 * @class FormFieldDefaultSelect
 *
 * This is a helper class to generate a default select form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_DefaultSelect extends FormField_Base
{

    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = (isset($options['typeField'])) ? $options['typeField'] : 'select';
        //Load the values
        $refObject = ($this->item) ? (string) $this->item->refObject : '';
        $query = ($this->item) ? (string) $this->item->query : '';
        if (isset($options['value'])) {
            $this->options['value'] = $options['value'];
        } elseif ($refObject != '') {
            $refObjectIns = new $refObject();
            $this->options['value'] = $refObjectIns->basicInfoAdminArray();
        } elseif ($query != '') {
            $this->options['value'] = $this->loadQueryValues($query);
        } else {
            $this->options['value'] = [];
            $i = 0;
            foreach ($this->item->values->value as $itemIns) {
                $id = (isset($itemIns['id'])) ? (string) $itemIns['id'] : $i;
                $this->options['value'][$id] = (string) $itemIns;
                $i++;
            }
        }
        //Load the selected value
        $this->options['selected'] = (isset($options['selected'])) ? $options['selected'] : ((isset($this->values[$this->name])) ? $this->values[$this->name] : '');
        $this->options['selected'] = ($this->options['selected'] == '' && $this->options['default'] != '') ? $this->options['default'] : $this->options['selected'];
    }

    /**
     * Render a default input element.
     */
    public function show()
    {
        $options = $this->options;
        $typeField = (isset($options['typeField'])) ? 'type="' . $options['typeField'] . '" ' : 'type="text"';
        $name = (isset($options['name'])) ? 'name="' . $options['name'] . '" ' : '';
        $nameSelect = (isset($options['name'])) ? $options['name'] : '';
        $id = (isset($options['id'])) ? 'id="' . $options['id'] . '"' : '';
        $labelFor = (isset($options['id'])) ? ' for="' . $options['id'] . '"' : '';
        $label = (isset($options['label']) && $options['label'] != '') ? '<label' . $labelFor . '>' . __($options['label']) . '</label>' : '';
        $value = (isset($options['value'])) ? $options['value'] : '';
        $selected = (isset($options['selected'])) ? $options['selected'] : '';
        $selected = (isset($options['multiple']) && $options['multiple'] == true && isset($options['class']) && $options['class'] == 'select2' && $selected != '') ? json_decode($selected, true) : $selected;
        $disabled = (isset($options['disabled']) && $options['disabled'] != false) ? 'disabled="disabled"' : '';
        $required = (isset($options['required']) && $options['required']) ? 'required' : '';
        $multipleSelection = (isset($options['multiple_selection']) && $options['multiple_selection'] == true) ? 'multiple="multiple"' : '';
        $size = (isset($options['size'])) ? 'size="' . $options['size'] . '" ' : '';
        $error = (isset($options['error']) && $options['error'] != '') ? '<div class="error_message">' . $options['error'] . '</div>' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        $class .= (isset($options['nameSimple'])) ? ' form_field_' . Text::simpleUrl($options['nameSimple'], '_') : '';
        $errorClass = (isset($options['error']) && $options['error'] != '') ? 'error' : '';
        $placeholder = (isset($options['placeholder'])) ? 'placeholder="' . __($options['placeholder']) . '"' : '';
        $layout = (isset($options['layout'])) ? $options['layout'] : '';
        $htmlOptions = '';
        if (is_array($value) && count($value) > 0) {
            if (isset($options['firstSelect']) && $options['firstSelect'] != '') {
                $htmlOptions .= '<option value="">' . __($options['firstSelect']) . '</option>';
            }
            foreach ($value as $key => $item) {
                if (is_array($item)) {
                    $itemsOptions = '';
                    foreach ($item['items'] as $keyIns => $itemIns) {
                        $isSelected = ($keyIns == $selected || (is_array($selected) && in_array($keyIns, $selected))) ? 'selected="selected"' : '';
                        $itemsOptions .= '<option value="' . $keyIns . '" ' . $isSelected . '>' . __($itemIns) . '</option>';
                    }
                    $htmlOptions .= '<optgroup label="' . $item['label'] . '">' . $itemsOptions . '</optgroup>';
                } else {
                    $isSelected = ($key == $selected || (is_array($selected) && in_array($key, $selected))) ? 'selected="selected"' : '';
                    $htmlOptions .= '<option value="' . $key . '" ' . $isSelected . '>' . __($item) . '</option>';
                }
            }
            switch ($layout) {
                default:
                    return '
                        <div class="select form_field ' . $class . ' ' . $errorClass . ' ' . $required . '">
                            <div class="form_field_ins">
                                ' . $label . '
                                ' . $error . '
                                <div class="select_ins">
                                    <select ' . $name . ' ' . $id . ' ' . $disabled . ' ' . $multipleSelection . ' ' . $size . ' ' . $required . '>' . $htmlOptions . '</select>
                                    <input type="hidden" name="select_' . $nameSelect . '" value="true"/>
                                </div>
                            </div>
                        </div>';
                    break;
                case 'simple':
                    return '
                        <select ' . $name . ' ' . $id . ' ' . $disabled . ' ' . $multipleSelection . ' ' . $size . ' ' . $required . '>' . $htmlOptions . '</select>';
                    break;
            }
        }
    }

    public function loadQueryValues($query)
    {
        $result = [];
        $items = Db::returnAll($query);
        foreach ($items as $item) {
            $result[$item['id']] = $item['value'];
        }
        return $result;
    }

}
