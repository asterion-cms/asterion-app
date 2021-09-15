<?php
/**
 * @class FormFieldHidden
 *
 * This is a helper class to generate a hidden form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_Hidden extends FormField_Default
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['layout'] = ($this->options['layout']!='') ? $this->options['layout'] : 'simple';
        $this->options['typeField'] = 'hidden';
    }

}
