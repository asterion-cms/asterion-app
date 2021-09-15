<?php
/**
 * @class FormFieldTextCode
 *
 * This is a helper class to generate a double text form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_TextCode extends FormField_Text
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['size'] = '30';
        $this->options['typeField'] = 'text';
        $this->options['pattern'] = '[a-zA-Z0-9_-]+';
        $this->options['title'] = __('title_pattern_code');
        $this->options['required'] = true;
        $this->options['messageAfter'] = __('title_pattern_code');
    }

}
