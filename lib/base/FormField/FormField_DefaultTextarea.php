<?php
/**
 * @class FormFieldDefaultTextarea
 *
 * This is a helper class to generate a default textarea form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_DefaultTextarea extends FormField_Base
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = (isset($options['typeField'])) ? $options['typeField'] : 'textarea';
        $this->options['maxlength'] = ($this->item) ? (string) $this->item->maxlength : '';
    }

    /**
     * Render a default textarea element.
     */
    public function show()
    {
        if ($this->options['language']) {
            $fields = '';
            $optionsName = $this->options['name'];
            foreach (Language::languages() as $language) {
                $nameLanguage = $this->name . '_' . $language['id'];
                $this->options['name'] = str_replace($this->name, $nameLanguage, $optionsName);
                $this->options['labelLanguage'] = $language['name'];
                $this->options['value'] = (isset($this->values[$nameLanguage])) ? $this->values[$nameLanguage] : '';
                $this->options['error'] = (isset($this->errors[$nameLanguage])) ? $this->errors[$nameLanguage] : '';
                $fields .= $this->create($this->options);
            }
            return '<div class="form_field_langs form_field_langs_'.count(Language::languages()).'">' . $fields . '</div>';
        } else {
            return $this->create($this->options);
        }
    }

    /**
     * Render the element with an static function.
     */
    public function create($options)
    {
        $type = (isset($options['typeField'])) ? $options['typeField'] : 'textarea';
        $typeField = (isset($options['typeField'])) ? 'type="' . $options['typeField'] . '" ' : 'type="text"';
        $name = (isset($options['name'])) ? 'name="' . $options['name'] . '" ' : '';
        $id = (isset($options['id'])) ? 'id="' . $options['id'] . '"' : '';
        $labelLanguage = (isset($options['labelLanguage']) && $options['labelLanguage'] != '') ? ' <span>(' . $options['labelLanguage'] . ')</span>' : '';
        $label = (isset($options['label']) && $options['label']!='') ? '<label>' . __($options['label']) . $labelLanguage . '</label>' : '';
        $messageBefore = (isset($options['messageBefore']) && $options['messageBefore'] != '') ? '<div class="formfield_message_before">' . __($options['messageBefore']) . '</div>' : '';
        $messageAfter = (isset($options['messageAfter']) && $options['messageAfter'] != '') ? '<div class="formfield_message_after">' . __($options['messageAfter']) . '</div>' : '';
        $value = (isset($options['value'])) ? $options['value'] : '';
        $disabled = (isset($options['disabled']) && $options['disabled'] != false) ? 'disabled="disabled"' : '';
        $cols = (isset($options['cols'])) ? 'cols="' . $options['cols'] . '" ' : '';
        $rows = (isset($options['rows'])) ? 'rows="' . $options['rows'] . '" ' : '';
        $error = (isset($options['error']) && $options['error'] != '') ? '<div class="error_message">' . $options['error'] . '</div>' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        $class .= (isset($options['nameSimple'])) ? ' form_field_' . Text::simpleUrl($options['nameSimple'], '_') : '';
        $classError = (isset($options['error']) && $options['error'] != '') ? 'error' : '';
        $placeholder = (isset($options['placeholder'])) ? 'placeholder="' . __($options['placeholder']) . '"' : '';
        $required = (isset($options['required']) && $options['required']) ? 'required' : '';
        $layout = (isset($options['layout'])) ? $options['layout'] : '';
        $maxlength = (isset($options['maxlength']) && $options['maxlength'] != '') ? 'maxlength="' . $options['maxlength'] . '" ' : '';
        switch ($layout) {
            default:
                return '<div class="' . $type . ' form_field ' . $class . ' ' . $required . ' ' . $classError . '">
                            <div class="form_field_ins">
                                ' . $label . '
                                ' . $error . '
                                ' . $messageBefore . '
                                <textarea ' . $name . ' ' . $cols . ' ' . $rows . ' ' . $id . ' ' . $placeholder . ' ' . $required . ' ' . $maxlength . '>' . $value . '</textarea>
                                ' . $messageAfter . '
                            </div>
                        </div>';
                break;
            case 'simple':
                return '<textarea ' . $name . ' ' . $cols . ' ' . $rows . ' ' . $id . ' ' . $placeholder . ' ' . $maxlength . '>' . $value . '</textarea>';
                break;
        }
    }

}
