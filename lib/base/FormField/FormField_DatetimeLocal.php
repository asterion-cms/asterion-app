<?php
/**
 * @class FormFieldDatetimeLocal
 *
 * This is a helper class to generate a text-date field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_DatetimeLocal extends FormField_Default
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = 'datetime-local';
        $this->options['class'] = 'datetime_local';
        $this->options['value'] = (isset($this->options['value'])) ? substr($this->options['value'], 0, 10) . 'T' . substr($this->options['value'], 11, 5) : '';
        $this->options['value'] = ($this->options['value'] == '0000-00-00T00:00') ? '' : $this->options['value'];
    }

}
