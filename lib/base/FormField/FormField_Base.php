<?php
/**
 * @class FormFieldDefault
 *
 * This is a helper class to generate a default form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_Base
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        $this->options = [];
        $this->item = (isset($options['item'])) ? $options['item'] : false;
        $this->object = (isset($options['object'])) ? $options['object'] : false;
        $this->values = isset($options['values']) ? $options['values'] : [];
        $this->errors = isset($options['errors']) ? $options['errors'] : [];
        $this->name = (isset($options['name'])) ? $options['name'] : (($this->item) ? (string) $this->item->name : '');
        $this->options['hasNameMultiple'] = (isset($options['nameMultiple']) && isset($options['idMultiple']) && $options['nameMultiple'] != '' && $options['idMultiple'] != '');
        $this->options['nameSimple'] = $this->name;
        $this->options['nameMultiple'] = (isset($this->options['nameMultiple'])) ? $this->options['nameMultiple'] : '';;
        $this->options['idMultiple'] = (isset($this->options['idMultiple'])) ? $this->options['idMultiple'] : '';;
        $this->options['name'] = ($this->options['hasNameMultiple']) ? $options['nameMultiple'] . '[' . $options['idMultiple'] . '][' . $this->name . ']' : $this->name;
        $this->options['value'] = (isset($options['value'])) ? $options['value'] : ((isset($this->values[$this->name])) ? $this->values[$this->name] : '');
        $this->options['error'] = (isset($options['error'])) ? $options['error'] : ((isset($this->errors[$this->name])) ? $this->errors[$this->name] : '');
        $this->options['label'] = (isset($options['label'])) ? $options['label'] : (($this->item) ? (string) $this->item->label : '');
        $this->options['placeholder'] = (isset($options['placeholder'])) ? $options['placeholder'] : (($this->item) ? (string) $this->item->placeholder : '');
        $this->options['layout'] = (isset($options['layout'])) ? $options['layout'] : (($this->item) ? (string) $this->item->layout : '');
        $this->options['mode'] = (isset($options['mode'])) ? $options['mode'] : (($this->item) ? (string) $this->item->mode : '');
        $this->options['step'] = (isset($options['step'])) ? $options['step'] : (($this->item) ? (string) $this->item->step : '');
        $this->options['currency'] = (isset($options['currency'])) ? $options['currency'] : (($this->item) ? (string) $this->item->currency : '');
        $this->options['autocomplete'] = (isset($options['autocomplete'])) ? $options['autocomplete'] : (($this->item) ? (string) $this->item->autocomplete : '');
        $this->options['required'] = (isset($options['required'])) ? $options['required'] : (($this->item && (string) $this->item->required != '') ? true : false);
        $this->options['language'] = (isset($options['language'])) ? $options['language'] : (($this->item && (string) $this->item->language == 'true') ? true : false);
        $this->options['hidden'] = (isset($options['hidden'])) ? $options['hidden'] : (($this->item && (string) $this->item->hidden == 'true') ? true : false);
        $this->options['multiple'] = (isset($options['multiple'])) ? $options['multiple'] : (($this->item && (string) $this->item->multiple == 'true') ? true : false);
        $this->options['default'] = (isset($options['default'])) ? $options['default'] : (($this->item) ? (string) $this->item->default : '');
        $this->options['accept'] = (isset($options['accept'])) ? $options['accept'] : (($this->item) ? (string) $this->item->accept : '');
        $this->options['typeField'] = (isset($options['typeField'])) ? $options['typeField'] : 'text';
        $this->options['class'] = (isset($options['class'])) ? $options['class'] : '';
        $this->options['messageBefore'] = (isset($options['messageBefore'])) ? $options['messageBefore'] : (($this->item) ? (string) $this->item->messageBefore : '');
        $this->options['messageAfter'] = (isset($options['messageAfter'])) ? $options['messageAfter'] : (($this->item) ? (string) $this->item->messageAfter : '');
    }

    /**
     * Change an option value.
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

}
