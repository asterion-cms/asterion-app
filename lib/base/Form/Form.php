<?php
/**
 * @class Form
 *
 * This is a helper class to create and format forms.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Form
{

    /**
     * A form is created using an XML model, it uses values and errors with the same names as the object properties.
     */
    public function __construct($values = [], $errors = [], $object = '')
    {
        if (!is_object($object)) {
            $this->className = str_replace('_Form', '', get_class($this));
            $this->object = new $this->className($values);
        } else {
            $this->object = $object;
            $this->className = $object->className;
        }
        $this->values = $values;
        $this->errors = $errors;
    }

    /**
     * Return an object using new values and errors.
     */
    public function fromArray($values = [], $errors = [])
    {
        $formClass = get_class($this);
        return new $formClass($values, $errors);
    }

    /**
     * Create a form from an object.
     */
    public static function fromObject($object)
    {
        $formClass = get_class($object) . '_Form';
        return new $formClass($object->values, [], $object);
    }

    /**
     * Get a form value.
     */
    public function get($name)
    {
        return (isset($this->$name)) ? $this->$name : '';
    }

    /**
     * Get all the form values.
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set a form value.
     */
    public function setValue($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * Add values to the form.
     */
    public function addValues($values, $errors = [])
    {
        $this->values = array_merge($this->values, $values);
        $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * Create the form fields.
     */
    public function createFormFields($options = [])
    {
        $html = '';
        $options['multiple'] = (isset($options['multiple']) && $options['multiple']) ? true : false;
        $options['idMultiple'] = ($options['multiple']) ? md5(rand() * rand() * rand()) : '';
        $options['idMultiple'] = (isset($options['idMultipleJs']) && $options['idMultipleJs'] != '') ? $options['idMultipleJs'] : $options['idMultiple'];
        $options['nameMultiple'] = (isset($options['nameMultiple'])) ? $options['nameMultiple'] : '';
        $html .= ($this->object->hasOrd()) ? FormField::show('hidden', array_merge(['name' => 'ord', 'value' => $this->object->get('ord'), 'class' => 'field_ord'], $options)) : '';
        foreach ($this->object->getAttributes() as $item) {
            if (!((string) $item->type == 'password' && $this->object->get('password') != '' && $this->object->id() != '')) {
                $html .= $this->createFormField($item, $options);
            }
        }
        return $html;
    }

    /**
     * Create the form field.
     */
    public function createFormField($item, $options = [])
    {
        $name = (string) $item->name;
        $label = (string) $item->label;
        $type = (string) $item->type;
        $class = (string) $item->class;
        $options = array_merge($options,
            [
                'item' => $item,
                'values' => $this->values,
                'errors' => $this->errors,
                'typeField' => $type,
                'object' => $this->object,
            ]);
        switch (Db_ObjectType::baseType($type)) {
            default:
                return FormField::show($type, $options);
                break;
            case 'id':
            case 'linkid':
            case 'hidden':
                switch ($type) {
                    default:
                        return FormField::show('hidden', $options);
                        break;
                    case 'hidden_login':
                        $login = UserAdmin_Login::getInstance();
                        $options['values'][$name] = $login->id();
                        return FormField::show('hidden', $options);
                        break;
                    case 'id_varchar':
                        return FormField::show('textSmall', $options);
                        break;
                }
                break;
            case 'autocomplete':
                $refObject = (string) $item->refObject;
                $refObjectInstance = new $refObject();
                return '
                    <div class="autocomplete_item autocomplete_item-' . $name . '"
                        data-url="' . url($refObjectInstance->snakeName . '/autocomplete/' . $refAttribute, true) . '">
                        <div class="autocompleteItemIns">
                            ' . $autocomplete . '
                        </div>
                    </div>';
                break;
            case 'multiple':
                switch ($type) {
                    case 'multiple_object':
                        switch ((string) $item->mode) {
                            default:
                                $this->object->loadMultipleValues();
                                $refObject = (string) $item->refObject;
                                $addMultipleImages = (string) $item->addMultipleImages;
                                $refObjectForm = $refObject . '_Form';
                                $nestedFormField = '';
                                $refObjectFormInstance = new $refObjectForm();
                                $label = ((string) $item->label != '') ? '<label>' . __((string) $item->label) . '</label>' : '';
                                $nestedFormFieldEmpty = '
                                    <div class="nested_form_field_empty">
                                        <div class="nested_form_field_options">
                                            <div class="nested_form_field_delete" data-confirm="' . __('are_you_sure_delete') . '">
                                                <i class="fa fa-times"></i>
                                            </div>
                                            ' . (($refObjectFormInstance->object->hasOrd()) ? '<div class="nested_form_field_order"><i class="fa fa-arrows-alt"></i></div>' : '') . '
                                        </div>
                                        <div class="nested_form_field_content">
                                            ' . $refObjectFormInstance->createFormFields(['multiple' => true, 'nameMultiple' => $name, 'idMultipleJs' => '#ID_MULTIPLE#']) . '
                                        </div>
                                    </div>';
                                foreach ($this->object->get($name) as $object) {
                                    $refObjectFormInstance = new $refObjectForm($object->values);
                                    $nestedFormField .= '
                                        <div class="nested_form_field_object" data-id="' . $object->id() . '">
                                            <div class="nested_form_field_options">
                                                <div class="nested_form_field_delete" data-url="' . url($refObject . '/delete_item/' . $object->id(), true) . '" data-confirm="' . __('are_you_sure_delete') . '">
                                                    <i class="fa fa-times"></i>
                                                </div>
                                                ' . (($refObjectFormInstance->object->hasOrd()) ? '<div class="nested_form_field_order"><i class="fa fa-arrows-alt"></i></div>' : '') . '
                                            </div>
                                            <div class="nested_form_field_content">
                                                ' . $refObjectFormInstance->createFormFields(['multiple' => true, 'nameMultiple' => $name]) . '
                                            </div>
                                        </div>';
                                }
                                return '
                                    <div class="nested_form_field nested_form_field_' . $class . '">
                                        ' . $label . '
                                        <div class="nested_form_field_ins ' . (($refObjectFormInstance->object->hasOrd()) ? 'nested_form_field_sortable' : '') . '">
                                            ' . $nestedFormField . '
                                        </div>
                                        <div class="nested_form_fieldNew">
                                            ' . $nestedFormFieldEmpty . '
                                            <div class="nested_form_field_add">' . __('add_new_register') . '</div>
                                            ' . (($addMultipleImages != '') ? '
                                            <div class="nested_form_field_add_multiple_wrapper" data-field="' . $addMultipleImages . '">
                                                ' . FormField::show('file', ['name' => $name . '_multiple_images', 'accept' => 'image/x-png,image/jpeg', 'multiple' => true, 'layout' => 'simple', 'class' => 'nested_form_field_add_multiple_file']) . '
                                                <div class="nested_form_field_add_multiple">' . __('add_multiple_images') . '</div>
                                            </div>
                                            ' : '') . '
                                        </div>
                                    </div>';
                                break;
                            case 'count':
                                $label = ((string) $item->label != '') ? '<label>' . __((string) $item->label) . '</label>' : '';
                                $refObject = (string) $item->refObject;
                                $linkAttribute = (string) $item->linkAttribute;
                                $refObjectInstance = new $refObject();
                                $count = $refObjectInstance->countResults(['where' => $linkAttribute . '=:id'], ['id' => $this->object->id()]);
                                return '
                                    <div class="nested_count">
                                        ' . $label . '
                                        <p>' . str_replace('#RESULTS', '<strong>' . $count . '</strong>', __('list_total')) . '</p>
                                    </div>';
                                break;
                            case 'hidden':
                                break;
                        }
                        break;
                    case 'multiple_autocomplete':
                        $this->object->loadMultipleValues();
                        $autoCompleteObject = (string) $item->autoCompleteObject;
                        $autoCompleteObjectInstance = new $autoCompleteObject();
                        $autoCompleteAttribute = (string) $item->autoCompleteAttribute;
                        $autocompleteItems = [];
                        foreach ($this->object->get($name) as $item) {
                            $autocompleteItems[] = $item->getBasicInfoAutocomplete();
                        }
                        $options['value'] = implode(', ', $autocompleteItems);
                        return '
                            <div class="autocomplete_item autocomplete_item_' . $class . ' autocomplete_item_' . $name . '"
                                data-url="' . url($autoCompleteObjectInstance->snakeName . '/autocomplete/' . $autoCompleteAttribute, true) . '">
                                <div class="autocompleteItemIns">
                                    ' . FormField::show('text', $options) . '
                                </div>
                            </div>';
                        break;
                    case 'multiple_checkbox':
                        $this->object->loadMultipleValues();
                        $refObject = (string) $item->refObject;
                        $checkboxObject = (string) $item->checkboxObject;
                        $refObjectInstance = new $refObject();
                        $checkboxObjectInstance = new $checkboxObject();
                        $checkboxOptions = '';
                        $linkAttributeCheckbox = $refObjectInstance->findLinkAttributeName($checkboxObject);
                        foreach ($checkboxObjectInstance->basicInfoAdminArray() as $keyCheckbox => $labelCheckbox) {
                            $value = 0;
                            foreach ($this->object->get($name) as $checkboxItem) {
                                $value = ($keyCheckbox == $checkboxItem->get($linkAttributeCheckbox)) ? 1 : $value;
                            }
                            $checkboxOptions .= FormField::show('checkbox', ['name' => $name . '[' . $keyCheckbox . ']', 'label' => $labelCheckbox, 'value' => $value]);
                        }
                        return ($checkboxOptions != '') ? '
                            <div class="form_field multiple_checkboxes multiple_checkboxes_' . $class . ' multiple_checkboxes_' . $name . '">
                                ' . (((string) $item->label != '') ? '
                                <label>' . __((string) $item->label) . '</label>
                                ' : '') . '
                                <div class="multiple_checkboxes_ins">' . $checkboxOptions . '</div>
                                ' . FormField::show('hidden', ['name' => $name . '_checkboxes', 'value' => true]) . '
                            </div>' : '';
                        break;
                }
                break;
        }
    }

    /**
     * Return a form field.
     */
    public function field($attribute, $options = [])
    {
        return $this->createFormField($this->object->attributeInfo($attribute), $options);
    }

    /**
     * Create a form.
     */
    public static function createForm($fields, $options = [])
    {
        $action = (isset($options['action'])) ? $options['action'] : '';
        $actionXhr = (isset($options['action-xhr'])) ? $options['action-xhr'] : '';
        $method = (isset($options['method'])) ? $options['method'] : 'post';
        $class = (isset($options['class'])) ? $options['class'] : 'form_admin';
        $recaptchav3 = (isset($options['recaptchav3'])) ? $options['recaptchav3'] : false;
        $recaptchav3Amp = (isset($options['recaptchav3Amp'])) ? $options['recaptchav3Amp'] : false;
        $submit = (isset($options['submit'])) ? $options['submit'] : __('send');
        $submitName = (isset($options['submitName'])) ? $options['submitName'] : 'submit';
        $id = (isset($options['id'])) ? 'id="' . $options['id'] . '"' : '';
        $class .= ($recaptchav3) ? ' recaptchav3_form ' : '';
        $recaptchaKey = (defined('ASTERION_RECAPTCHAV3_SITE_KEY') && ASTERION_RECAPTCHAV3_SITE_KEY != '') ? ASTERION_RECAPTCHAV3_SITE_KEY : Parameter::code('recaptchav3_site_key');
        if ($recaptchav3) {
            $submitButton = '
                <div class="form_submit_wrapper">
                    <button class="g-recaptcha button form_submit" data-sitekey="' . $recaptchaKey . '" data-callback="onSubmitRecaptchaV3" data-action="submit">' . $submit . '</button>
                </div>';
        } else if ($submit == 'ajax') {
            $submitButton = '
                <div class="submit_button_ajax">
                    <div class="submit_button_ajax_ins"></div>
                </div>';
        } else if (is_array($submit)) {
            $submitButton = '';
            foreach ($submit as $keySubmit => $submitIns) {
                $submitButton .= '<input type="submit" name="submit-' . $keySubmit . '" class="button form_submit form_submit' . ucwords($keySubmit) . '" value="' . $submitIns . '"/>';
            }
            $submitButton = '<div class="submit_buttons">' . $submitButton . '</div>';
        } else {
            $submitButton = FormField::show('submit', ['name' => $submitName, 'class' => 'form_submit', 'value' => $submit]);
            if ($recaptchav3Amp) {
                $submitButton = '
                    <amp-recaptcha-input layout="nodisplay" name="g-recaptcha-response" data-sitekey="' . $recaptchaKey . '" data-action="onSubmitRecaptchaV3"></amp-recaptcha-input>
                    ' . $submitButton;
            }
        }
        $submitButton = ($submit == 'none') ? '' : $submitButton;
        $action = ($actionXhr == '') ? 'action="' . $action . '"' : '';
        $actionXhr = ($actionXhr == '') ? '' : 'action-xhr="' . $actionXhr . '"';
        return '
            <form ' . $id . ' ' . $action . ' ' . $actionXhr . ' method="' . $method . '" enctype="multipart/form-data" class="' . $class . '" accept-charset="UTF-8">
                <fieldset>
                    ' . $fields . '
                    ' . $submitButton . '
                </fieldset>
            </form>';
    }

    /**
     * Create an insertion form for the administration area.
     */
    public function createFormInsertAdministrator()
    {
        $layout = (string) $this->object->info->info->form->layout;
        return $this->createForm($this->createFormFields(), [
            'action' => url(camelToSnake($this->object->className) . '/' . (($layout == 'modal') ? 'insert_item_ajax' : 'insert_item'), true),
            'submit' => __('save'),
            'class' => 'form_admin form_admin_insert',
        ]);
    }

    /**
     * Create a modification form for the administration area.
     */
    public function createFormModifyAdministrator()
    {
        $layout = (string) $this->object->info->info->form->layout;
        $submitOptions = [
            'action' => url(camelToSnake($this->object->className) . '/' . (($layout == 'modal') ? 'modify_item_ajax' : 'modify_item'), true),
            'submit' => __('save'),
            'class' => 'form_admin form_admin_modify',
        ];
        return '
            <div class="form_admin_modify_wrapper">
                ' . $this->formModifyAdministratorActionsTop() . '
                ' . $this->createForm($this->createFormFields(), $submitOptions) . '
                ' . (($this->object->id() != '' && $layout != 'modal') ? '
                ' . $this->formModifyAdministratorActionsBottom() . '
                ' : '') . '
            </div>';
    }

    /**
     * Function to overridde in order to add more actions to the bottom part of the edit form.
     */
    public function formModifyAdministratorActionsTop()
    {
    }

    /**
     * Function to overridde in order to add more actions to the bottom part of the edit form.
     */
    public function formModifyAdministratorActionsBottom()
    {
        return '
            <div class="form_admin_modify_actions_bottom">
                <a href="' . url(camelToSnake($this->object->className) . '/delete_item/' . $this->object->id(), true) . '"
                    data-confirm="' . __('are_you_sure_delete') . '" class="button button_delete">
                    <i class="fa fa-times"></i>
                    <span>' . __('delete') . '</span>
                </a>
            </div>';
    }

}
