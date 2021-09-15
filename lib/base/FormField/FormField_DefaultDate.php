<?php
/**
 * @class FormFieldDefaultDate
 *
 * This is a helper class to generate a default date form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_DefaultDate extends FormField_Base
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = (isset($options['typeField'])) ? $options['typeField'] : 'date';
        $this->options['checkboxDate'] = (isset($options['checkboxDate'])) ? $options['checkboxDate'] : (($this->item && (string) $this->item->checkboxDate == 'true') ? true : false);
        $this->options['value'] = (isset($this->values[$this->name]) && $this->values[$this->name] != '') ? $this->values[$this->name] : date('Y-m-d h:i');
    }

    /**
     * Render a date element using selectboxes.
     */
    public function show()
    {
        $options = $this->options;
        $label = (isset($options['label']) && $options['label']!='') ? '<label>' . __($options['label']) . '</label>' : '';
        $value = (isset($options['value'])) ? $options['value'] : '';
        $disabled = (isset($options['disabled'])) ? $options['disabled'] : '';
        $error = (isset($options['error']) && $options['error'] != '') ? '<div class="error_message">' . $options['error'] . '</div>' : '';
        $errorClass = (isset($options['error']) && $options['error'] != '') ? 'error' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        $class .= (isset($options['nameSimple'])) ? ' form_field_' . Text::simpleUrl($options['nameSimple'], '_') : '';
        $layout = (isset($options['layout'])) ? $options['layout'] : '';
        $checkboxVal = ($value != '') ? "1" : "0";
        $checkbox = ($options['checkboxDate']) ? FormField::show('checkbox', ['name' => 'check_' . $options['name'], 'value' => $checkboxVal, 'class' => 'checkBoxInlineDate']) : '';
        $checkboxHidden = ($options['checkboxDate']) ? FormField::show('hidden', ['name' => 'checkhidden_' . $options['name'], 'value' => "1"]) : '';
        $checkboxClass = ($options['checkboxDate']) ? 'select_checkbox' : '';
        return '<div class="select select_date form_field ' . $class . ' ' . $errorClass . ' ' . $checkboxClass . '">
                    ' . $label . '
                    ' . $error . '
                    <div class="select_ins">
                        ' . $checkbox . '
                        ' . $checkboxHidden . '
                        ' . $this->createDate($options) . '
                    </div>
                </div>';
    }

    public function createDate($options)
    {
        $options['value'] = isset($options['value']) ? $options['value'] : date('Y-m-d h:i');
        $date = Date::sqlArray($options['value']);
        unset($options['label']);
        $view = (isset($options['view'])) ? $options['view'] : '';
        $options['layout'] = 'simple';
        $result = '';
        switch ($view) {
            default:
                $options['selected'] = $date['day'];
                $result .= $this->createDay($options);
                $options['selected'] = $date['month'];
                $result .= $this->createMonth($options);
                $options['selected'] = $date['year'];
                $result .= $this->createYear($options);
                break;
            case 'hour':
                $options['selected'] = $date['hour'];
                $result .= $this->createHour($options);
                $options['selected'] = $date['minutes'];
                $result .= $this->createMinutes($options);
                break;
            case 'complete':
                $options['selected'] = $date['day'];
                $result = $this->createDay($options);
                $options['selected'] = $date['month'];
                $result .= $this->createMonth($options);
                $options['selected'] = $date['year'];
                $result .= $this->createYear($options);
                $options['selected'] = $date['hour'];
                $result .= $this->createHour($options);
                $options['selected'] = $date['minutes'];
                $result .= $this->createMinutes($options);
                break;
            case 'year':
                $options['selected'] = $date['year'];
                $result .= $this->createYear($options);
                break;
        }
        return $result;
    }

    public function createTime($options)
    {
        $date = Date::sqlArray($options['value']);
        $options['selected'] = $date['hour'];
        $result = $this->createHour($options);
        $options['selected'] = $date['minutes'];
        $result .= $this->createMinutes($options);
        return $result;
    }

    public function createDay($options)
    {
        $options['value'] = array_fillkeys(range(1, 31), range(1, 31));
        if (isset($options['nameMultiple']) && $options['nameMultiple']) {
            $options['name'] = (isset($options['name'])) ? substr($options['name'], 0, -1) . 'day]' : 'day';
        } else {
            $options['name'] = (isset($options['name'])) ? $options['name'] . 'day' : 'day';
            $options['name'] = str_replace('[]day', 'day[]', $options['name']);
        }
        return FormField::show('select', $options);
    }

    public function createMonth($options)
    {
        $options['value'] = array_fillkeys(range(1, 12), range(1, 12));
        $options['value'] = Date::textMonthArray();
        if (isset($options['nameMultiple']) && $options['nameMultiple']) {
            $options['name'] = (isset($options['name'])) ? substr($options['name'], 0, -1) . 'mon]' : 'mon';
        } else {
            $options['name'] = (isset($options['name'])) ? $options['name'] . 'mon' : 'mon';
            $options['name'] = str_replace('[]mon', 'mon[]', $options['name']);
        }
        return FormField::show('select', $options);
    }

    public function createMonthSimple($options)
    {
        $options['value'] = array_fillkeys(range(1, 12), range(1, 12));
        $options['value'] = Date::textMonthArraySimple();
        if (isset($options['nameMultiple']) && $options['nameMultiple']) {
            $options['name'] = (isset($options['name'])) ? substr($options['name'], 0, -1) . 'mon]' : 'mon';
        } else {
            $options['name'] = (isset($options['name'])) ? $options['name'] . 'mon' : 'mon';
            $options['name'] = str_replace('[]mon', 'mon[]', $options['name']);
        }
        return FormField::show('select', $options);
    }

    public function createYear($options)
    {
        $fromYear = isset($options['fromYear']) ? $options['fromYear'] : date('Y') - 90;
        $toYear = isset($options['toYear']) ? $options['toYear'] : date('Y') + 20;
        $options['value'] = array_fillkeys(range($fromYear, $toYear), range($fromYear, $toYear));
        if (isset($options['nameMultiple']) && $options['nameMultiple']) {
            $options['name'] = (isset($options['name'])) ? substr($options['name'], 0, -1) . 'yea]' : 'yea';
        } else {
            $options['name'] = (isset($options['name'])) ? $options['name'] . 'yea' : 'yea';
            $options['name'] = str_replace('[]yea', 'yea[]', $options['name']);
        }
        return FormField::show('select', $options);
    }

    public function createHour($options)
    {
        $options['value'] = array_fillkeys(range(0, 23), range(0, 23));
        foreach ($options['value'] as $key => $value) {
            $options['value'][$key] = str_pad((string) $value, 2, "0", STR_PAD_LEFT);
        }
        if (isset($options['nameMultiple']) && $options['nameMultiple']) {
            $options['name'] = (isset($options['name'])) ? substr($options['name'], 0, -1) . 'hou]' : 'hou';
        } else {
            $options['name'] = (isset($options['name'])) ? $options['name'] . 'hou' : 'hou';
            $options['name'] = str_replace('[]hou', 'hou[]', $options['name']);
        }
        return FormField::show('select', $options);
    }

    public function createMinutes($options)
    {
        $options['value'] = array_fillkeys(range(0, 59), range(0, 59));
        foreach ($options['value'] as $key => $value) {
            $options['value'][$key] = str_pad((string) $value, 2, "0", STR_PAD_LEFT);
        }
        if (isset($options['nameMultiple']) && $options['nameMultiple']) {
            $options['name'] = (isset($options['name'])) ? substr($options['name'], 0, -1) . 'min]' : 'min';
        } else {
            $options['name'] = (isset($options['name'])) ? $options['name'] . 'min' : 'min';
            $options['name'] = str_replace('[]min', 'min[]', $options['name']);
        }
        return FormField::show('select', $options);
    }

}
