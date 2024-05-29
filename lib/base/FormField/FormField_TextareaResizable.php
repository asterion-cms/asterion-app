<?php
/**
 * @class FormFieldTextareaResizable
 *
 * This is a helper class to generate a long textarea form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_TextareaResizable extends FormField_DefaultTextarea
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['cols'] = '80';
        $this->options['rows'] = '1';
    }

}
