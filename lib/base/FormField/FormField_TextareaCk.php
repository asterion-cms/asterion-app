<?php
/**
 * @class FormFieldTextareaCk
 *
 * This is a helper class to generate a CK textarea form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_TextareaCk extends FormField_DefaultTextarea
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['cols'] = '70';
        $this->options['rows'] = '10';
        $this->options['class'] = 'ckeditorArea';
        $this->options['value'] = (isset($this->options['value'])) ? Text::decodeText($this->options['value']) : '';
    }

}
