<?php
/**
 * @class FormFieldEmail
 *
 * This is a helper class to generate an email text form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_TextIcon extends FormField_Text
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['size'] = '20';
        $this->options['messageAfter'] = '<i class="' . $this->options['value'] . '"></i>';
    }

}
