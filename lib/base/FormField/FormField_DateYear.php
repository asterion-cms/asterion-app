<?php
/**
 * @class FormFieldDateYear
 *
 * This is a helper class to generate complete date fields with selectboxes.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_DateYear extends FormField_DefaultDate
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['view'] = 'year';
    }

}
