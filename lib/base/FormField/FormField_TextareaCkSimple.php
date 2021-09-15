<?php
/**
 * @class FormFieldTextareaCkSimple
 *
 * This is a helper class to generate a simple CK textarea form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_TextareaCkSimple extends FormField_DefaultTextarea
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['cols'] = '70';
        $this->options['rows'] = '6';
        $this->options['class'] = 'ckeditorAreaSimple';
        $this->options['value'] = (isset($this->options['value'])) ? htmlspecialchars($this->options['value']) : '';
    }

}
