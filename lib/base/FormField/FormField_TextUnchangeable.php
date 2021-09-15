<?php
/**
 * @class FormFieldTextUnchangeable
 *
 * This is a helper class to generate an unchangeable text form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_TextUnchangeable extends FormField_Text
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['size'] = '30';
        if (isset($this->options['value']) && $this->options['value'] != '') {
            $this->options['disabled'] = true;
        }
    }

}
