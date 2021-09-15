<?php
/**
 * @class FormFieldDefault
 *
 * This is a helper class to generate a default form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_Default extends FormField_Base
{

    /**
     * Render a default input element.
     */
    public function show()
    {
        if ($this->options['language']) {
            $fields = '';
            $optionsName = $this->options['name'];
            foreach (Language::languages() as $language) {
                $nameLanguage = $this->name . '_' . $language['id'];
                $this->options['name'] = str_replace($this->name, $nameLanguage, $optionsName);
                $this->options['labelLanguage'] = $language['name'];
                $this->options['value'] = (isset($this->values[$nameLanguage])) ? $this->values[$nameLanguage] : '';
                $this->options['error'] = (isset($this->errors[$nameLanguage])) ? $this->errors[$nameLanguage] : '';
                $this->options['class'] = 'form_field_' . $nameLanguage . ' form_field_' . $this->name;
                $fields .= $this->create($this->options);
            }
            return '<div class="form_field_langs form_field_langs_' . count(Language::languages()) . '">' . $fields . '</div>';
        } else {
            return $this->create($this->options);
        }
    }

    /**
     * Render the element with an static function.
     */
    public function create($options)
    {
        $type = (isset($options['typeField'])) ? $options['typeField'] : 'text';
        $type = ($options['hidden']) ? 'hidden' : $type;
        $typeField = 'type="' . $type . '"';
        $name = (isset($options['name'])) ? 'name="' . $options['name'] . '" ' : '';
        $id = (isset($options['id'])) ? 'id="' . $options['id'] . '"' : '';
        $labelLanguage = (isset($options['labelLanguage']) && $options['labelLanguage'] != '') ? ' <span>(' . $options['labelLanguage'] . ')</span>' : '';
        $label = (isset($options['label']) && $options['label'] != '') ? '<label>' . __($options['label']) . $labelLanguage . '</label>' : '';
        $messageBefore = (isset($options['messageBefore']) && $options['messageBefore'] != '') ? '<div class="formfield_message_before">' . __($options['messageBefore']) . '</div>' : '';
        $messageAfter = (isset($options['messageAfter']) && $options['messageAfter'] != '') ? '<div class="formfield_message_after">' . __($options['messageAfter']) . '</div>' : '';
        $value = (isset($options['value'])) ? 'value="' . $options['value'] . '" ' : '';
        $disabled = (isset($options['disabled']) && $options['disabled'] != false) ? 'disabled="disabled"' : '';
        $size = (isset($options['size'])) ? 'size="' . $options['size'] . '" ' : '';
        $error = (isset($options['error']) && $options['error'] != '') ? '<div class="error_message">' . $options['error'] . '</div>' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        $class .= (isset($options['nameSimple'])) ? ' form_field_' . Text::simpleUrl($options['nameSimple'], '_') : '';
        $classError = (isset($options['error']) && $options['error'] != '') ? 'error' : '';
        $placeholder = (isset($options['placeholder'])) ? 'placeholder="' . __($options['placeholder']) . '"' : '';
        $required = (isset($options['required']) && $options['required']) ? 'required' : '';
        $pattern = (isset($options['pattern']) && $options['pattern'] != '') ? 'pattern="' . $options['pattern'] . '"' : '';
        $title = (isset($options['title']) && $options['title'] != '') ? 'title="' . $options['title'] . '"' : '';
        $layout = (isset($options['layout'])) ? $options['layout'] : '';
        $autocomplete = (isset($options['autocomplete'])) ? 'autocomplete="' . $options['autocomplete'] . '" ' : '';
        switch ($layout) {
            default:
            case 'publish_date':
                $messagePublishDate = '';
                if ($layout == 'publish_date' && isset($options['value']) && $options['value'] != '') {
                    $difference = Date::difference($options['value'], date('Y-m-d'));
                    if ($difference['difference'] < 0) {
                        $messagePublishDate = '
                            <div class="formfield_message_after">
                                <i class="fa fa-exclamation-triangle"></i>
                                 ' . str_replace('#DAYS', $difference['days'], __('will_be_published_in_days')) . '
                            </div>';
                    }
                }
                return '
                    <div class="' . $type . ' form_field ' . $class . ' ' . $required . ' ' . $classError . '">
                        <div class="form_field_ins">
                            ' . $label . '
                            ' . $error . '
                            ' . $messageBefore . '
                            <input ' . $typeField . ' ' . $name . ' ' . $size . ' ' . $value . ' ' . $id . ' ' . $disabled . ' ' . $placeholder . ' ' . $required . ' ' . $pattern . ' ' . $title . ' ' . $autocomplete . '/>
                            ' . $messageAfter . '
                            ' . $messagePublishDate . '
                        </div>
                    </div>';
                break;
            case 'color':
                return '
                    <div class="' . $type . ' form_field ' . $class . ' ' . $required . ' ' . $classError . '">
                        <div class="form_field_ins">
                            ' . $label . '
                            ' . $error . '
                            ' . $messageBefore . '
                            <input class="color" ' . $typeField . ' ' . $name . ' ' . $size . ' ' . $value . ' ' . $id . ' ' . $disabled . ' ' . $placeholder . ' ' . $required . ' ' . $pattern . ' ' . $title . ' ' . $autocomplete . '/>
                            ' . $messageAfter . '
                        </div>
                    </div>';
                break;
            case 'simple':
                return '
                    <input ' . $typeField . ' ' . $name . ' ' . $size . ' ' . $value . ' ' . $id . ' ' . $disabled . ' ' . $placeholder . '' . $required . ' ' . $pattern . ' ' . $title . ' ' . $autocomplete . ' class="' . $class . '"/>';
                break;
            case 'label':
                $labelField = ($this->object->label($options['name']) == '') ? '' : '
                    <div class="' . $type . ' form_field form_field_label ' . $class . ' ' . $required . ' ' . $classError . '">
                        <div class="form_field_ins">
                            ' . $label . '
                            ' . $error . '
                            <p>' . $this->object->label($options['name']) . '</p>
                            ' . $messageBefore . '
                            ' . $messageAfter . '
                        </div>
                    </div>';
                return '
                    ' . $labelField . '
                    <input ' . $typeField . ' ' . $name . ' ' . $size . ' ' . $value . ' ' . $id . ' ' . $disabled . ' ' . $placeholder . '' . $required . ' ' . $pattern . ' ' . $title . ' ' . $autocomplete . ' class="' . $class . '"/>';
                break;
        }
    }

}
