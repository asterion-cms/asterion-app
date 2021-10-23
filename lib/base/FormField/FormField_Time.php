<?php
/**
 * @class FormFieldTime
 *
 * This is a helper class to generate a text-date field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_Time extends FormField_Default
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = 'time';
        $this->options['class'] = 'time';
        $this->options['value'] = (isset($this->options['value'])) ? $this->options['value'] : '';
        $this->options['value'] = ($this->options['value'] == '00:00') ? '' : $this->options['value'];
    }

}
