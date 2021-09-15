<?php
/**
 * @class FormFieldDateHour
 *
 * This is a helper class to generate hour fields with selectboxes.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_DateHour extends FormField_DefaultDate
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['view'] = 'hour';
    }

}
