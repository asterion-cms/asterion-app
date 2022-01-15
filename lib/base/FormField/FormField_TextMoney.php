<?php
/**
 * @class FormFieldTextNumber
 *
 * This is a helper class to generate a number text form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_TextMoney extends FormField_Default
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['size'] = '6';
        $this->options['typeField'] = 'number';
        $this->options['step'] = '0.01';
        if ($this->options['currency']!='') {
            $this->options['messageBefore'] = str_replace('#CURRENCY', $this->options['currency'], __('money_field_disclaimer'));
        }
    }

}
