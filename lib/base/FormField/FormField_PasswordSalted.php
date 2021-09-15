<?php
/**
 * @class FormFieldPassword
 *
 * This is a helper class to generate a password form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_PasswordSalted extends FormField_Password
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
    }

}
