<?php
/**
 * @class FormFieldFile
 *
 * This is a helper class to generate a file form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_File extends FormField_Base
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = (isset($options['typeField'])) ? $options['typeField'] : 'file';
    }

    /**
     * Render a default file input element
     */
    public function show()
    {
        if ($this->options['language']) {
            $fields = '';
            $optionsName = $this->options['name'];
            foreach (Language::languages() as $language) {
                $nameLanguage = $this->name . '_' . $language;
                $this->options['name'] = str_replace($this->name, $nameLanguage, $optionsName);
                $this->options['labelLanguage'] = $language['name'];
                $this->options['value'] = (isset($this->values[$nameLanguage])) ? $this->values[$nameLanguage] : '';
                $this->options['class'] = 'form_field_' . $nameLanguage;
                $fields .= $this->create($this->options);
            }
            return $fields;
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
        $typeField = (isset($options['typeField'])) ? 'type="' . $options['typeField'] . '"' : 'type="file"';
        $name = (isset($options['name'])) ? 'name="' . $options['name'] . '" ' : '';
        $name = (isset($options['name']) && isset($options['multiple']) && $options['multiple']) ? 'name="' . $options['name'] . '[]" ' : $name;
        $id = (isset($options['id'])) ? 'id="' . $options['id'] . '"' : '';
        $labelLanguage = (isset($options['labelLanguage']) && $options['labelLanguage'] != '') ? ' <span>(' . $options['labelLanguage'] . ')</span>' : '';
        $label = (isset($options['label']) && $options['label'] != '') ? '<label>' . __($options['label']) . $labelLanguage . ' <em>' . __('maximum_size') . ': ' . ini_get('post_max_size') . '</em></label>' : '';
        $value = (isset($options['value'])) ? 'value="' . $options['value'] . '" ' : '';
        $valueFile = (isset($options['value'])) ? $options['value'] : '';
        $disabled = (isset($options['disabled']) && $options['disabled'] != false) ? 'disabled="disabled"' : '';
        $size = (isset($options['size'])) ? 'size="' . $options['size'] . '" ' : '';
        $error = (isset($options['error']) && $options['error'] != '') ? '<div class="error_message">' . $options['error'] . '</div>' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        $class .= (isset($options['nameSimple'])) ? ' form_field_' . Text::simpleUrl($options['nameSimple'], '_') : '';
        $classError = (isset($options['error']) && $options['error'] != '') ? 'error' : '';
        $placeholder = (isset($options['placeholder'])) ? 'placeholder="' . __($options['placeholder']) . '"' : '';
        $required = (isset($options['required']) && $options['required']) ? 'required' : '';
        $multiple = (isset($options['multiple']) && $options['multiple']) ? 'multiple' : '';
        $accept = (isset($options['accept']) && $options['accept'] != '') ? 'accept="' . $options['accept'] . '"' : '';
        $layout = (isset($options['layout'])) ? $options['layout'] : '';
        $mode = (isset($options['mode'])) ? $options['mode'] : '';
        $htmlShowImage = '';
        $htmlShowFile = '';
        switch ($mode) {
            default:
                $htmlShowFile = $this->renderFile($valueFile);
                break;
            case 'image':
                $htmlShowImage = $this->renderImage($valueFile);
                $images = explode(':', $valueFile);
                $accept = 'accept="image/x-png,image/jpeg"';
                if (count($images) > 1) {
                    $htmlShowImage = '';
                    foreach ($images as $image) {
                        $htmlShowImage .= $this->renderImage($image, $options);
                    }
                }
                break;
            case 'adaptable':
                $htmlShowImage = $this->renderImage($valueFile);
                if ($htmlShowImage == '') {
                    $htmlShowFile = $this->renderFile($valueFile);
                }
                break;
        }
        $htmlShowImage = ($htmlShowImage != '') ? '<div class="form_fields_images">' . $htmlShowImage . '</div>' : '';
        switch ($layout) {
            default:
                return '<div class="' . $type . ' form_field ' . $class . ' ' . $classError . '">
                            <div class="form_field_ins">
                                ' . $label . '
                                ' . $error . '
                                ' . $htmlShowImage . '
                                <input ' . $typeField . ' ' . $name . ' ' . $size . ' ' . $value . ' ' . $id . ' ' . $disabled . ' ' . $placeholder . ' ' . $required . ' ' . $multiple . ' ' . $accept . '/>
                                ' . $htmlShowFile . '
                            </div>
                        </div>';
                break;
            case 'url':
                return '<div class="' . $type . ' form_field ' . $class . ' ' . $classError . '">
                            <div class="form_field_ins">
                                ' . $label . '
                                ' . $error . '
                                ' . $htmlShowImage . '
                                <input type="text" ' . $name . ' ' . $size . ' ' . $id . ' ' . $disabled . ' ' . $placeholder . ' ' . $required . ' ' . $multiple . ' ' . $accept . '/>
                                ' . $htmlShowFile . '
                            </div>
                        </div>';
                break;
            case 'simple':
                return '<input ' . $typeField . ' ' . $name . ' ' . $size . ' ' . $value . ' ' . $id . ' ' . $disabled . ' ' . $placeholder . ' ' . $required . ' ' . $multiple . ' ' . $accept . ' class="' . $class . '"/>';
                break;
        }
    }

    public function renderImage($valueFile)
    {
        $file = ASTERION_STOCK_FILE . $this->object->className . '/' . $valueFile . '/' . $valueFile . '_thumb.jpg';
        $file = (!is_file($file)) ? ASTERION_STOCK_FILE . $this->object->className . '/' . $valueFile . '/' . $valueFile . '_small.jpg' : $file;
        $file = (!is_file($file)) ? ASTERION_STOCK_FILE . $this->object->className . '/' . $valueFile . '/' . $valueFile . '_web.jpg' : $file;
        if (is_file($file)) {
            $objectUiClassname = $this->object->className.'_Ui';
            $objectUi = new $objectUiClassname($this->object);
            return '
                <div class="form_fields_image">
                    <div class="form_fields_image_ins">
                        <div class="form_fields_image_delete" data-confirm="'.__('are_you_sure_delete').'" data-url="' . $objectUi->object->urlDeleteImage($valueFile) . '">
                            <i class="fa fa-times"></i>
                        </div>
                        <img src="' . str_replace(ASTERION_STOCK_FILE, ASTERION_STOCK_URL, $file) . '?v=' . substr(md5(rand() * rand()), 0, 5) . '" alt=""/>
                    </div>
                </div>';
        }
    }

    public function renderFile($valueFile)
    {
        $file = ASTERION_STOCK_FILE . (isset($this->object->className) ? $this->object->className : 'Asterion') . 'Files/' . $valueFile;
        if (is_file($file)) {
            return '
                <div class="form_fields_file">
                    <div class="form_fields_file_ins">
                        <a href="' . str_replace(ASTERION_STOCK_FILE, ASTERION_STOCK_URL, $file) . '" target="_blank">
                            <i class="fa fa-download"></i>
                            <span>' . __('download_file') . '</span>
                        </a>
                        <span class="form_fields_file_delete" data-confirm="'.__('are_you_sure_delete').'" data-url="' . url($this->object->snakeName . '/delete_file/' . $this->object->id() . '/' . $valueFile, true) . '">
                            <i class="fa fa-times"></i>
                            <span>'.__('delete_file').'</span>
                        </span>
                    </div>
                </div>';
        }
    }

}
