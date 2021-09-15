<?php
/**
 * @class FormFieldSubmit
 *
 * This is a helper class to generate a submit form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_Submit
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Render a submit input element.
     */
    public function show()
    {
        $options = $this->options;
        $name = (isset($options['name'])) ? 'name="' . $options['name'] . '" ' : '';
        $id = (isset($options['id'])) ? 'id="' . $options['id'] . '"' : '';
        $disabled = (isset($options['disabled'])) ? 'disabled="disabled"' : '';
        $value = (isset($options['value'])) ? 'value="' . $options['value'] . '" ' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        return '<div class="form_submit_wrapper">
                    <input type="submit" ' . $name . ' ' . $value . ' class="button ' . $class . '" ' . $id . ' ' . $disabled . '/>
                </div>';
    }

}
