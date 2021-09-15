<?php
/**
 * @class FormFieldText
 *
 * This is a helper class to generate a text form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_Text extends FormField_Default
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['size'] = '40';
    }

}
