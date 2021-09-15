<?php
/**
 * @class FormFieldText
 *
 * This is a helper class to generate a text form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_FileDrag extends FormField_Base
{
    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['maxDimensions'] = (isset($options['maxDimensions'])) ? $options['maxDimensions'] : (($this->item && (string) $this->item->maxDimensions == 'true') ? true : false);
    }

    /**
     * Render a default input element.
     */
    public function show()
    {
        return $this->create($this->options);
    }

    /**
     * Render the element with an static function.
     */
    public function create($options)
    {
        $type = (isset($options['typeField'])) ? $options['typeField'] : 'image';
        $name = (isset($options['name'])) ? $options['name'] : '';
        $id = (isset($options['id'])) ? $options['id'] : rand(0, 9999);
        $valueFile = (isset($options['value'])) ? $options['value'] : '';
        $labelLanguage = (isset($options['labelLanguage']) && $options['labelLanguage'] != '') ? ' <span>(' . $options['labelLanguage'] . ')</span>' : '';
        $label = (isset($options['label']) && $options['label'] != '') ? '<label>' . __($options['label']) . $labelLanguage . ' <em>' . __('maximum_size') . ': ' . ini_get('post_max_size') . '</em></label>' : '';
        $messageBefore = (isset($options['messageBefore']) && $options['messageBefore'] != '') ? '<div class="formfield_message_before">' . __($options['messageBefore']) . '</div>' : '';
        $messageAfter = (isset($options['messageAfter']) && $options['messageAfter'] != '') ? '<div class="formfield_message_after">' . __($options['messageAfter']) . '</div>' : '';
        $error = (isset($options['error']) && $options['error'] != '') ? '<div class="error_message">' . $options['error'] . '</div>' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        $class .= (isset($options['nameSimple'])) ? ' form_field_' . Text::simpleUrl($options['nameSimple'], '_') : '';
        $classError = (isset($options['error']) && $options['error'] != '') ? 'error' : '';
        $required = (isset($options['required']) && $options['required']) ? 'required' : '';
        $layout = (isset($options['layout'])) ? $options['layout'] : '';
        $maxDimensions = (isset($options['maxDimensions'])) ? $options['maxDimensions'] : false;
        $loader = '
            <div class="drag_field_loader">
                <div class="drag_field_loader_message" data-messageloading="' . __('file_loading') . '" data-messagesaving="' . __('file_saving') . '" data-messagesavedas="' . __('file_saved_as') . '"></div>
                <div class="drag_field_loader_bar_wrapper">
                    <div class="drag_field_loader_bar"></div>
                </div>
            </div>';
        if ($layout == 'image') {
            $renderField = $this->renderImage($valueFile);
            $field = '
                <div class="drag_field_wrapper ' . (($renderField != '') ? 'drag_field_wrapper_has_image' : '') . '" data-maxdimensions="' . $maxDimensions . '" data-maxwidth="' . ASTERION_WIDTH_HUGE . '" data-maxheight="' . ASTERION_HEIGHT_MAX_HUGE . '">
                    <div class="drag_field_wrapper_image">' . $renderField . '</div>
                    <div class="drag_field_wrapper_input">
                        <div class="drag_field_input">
                            ' . FormField::show('hidden', ['name' => $name, 'id' => $id, 'class' => 'filevalue']) . '
                            ' . FormField::show('hidden', ['name' => $name . '_filename', 'id' => $id . '_filename', 'class' => 'filename']) . '
                            ' . FormField::show('hidden', ['name' => $name . '_uploaded', 'id' => $id . '_uploaded', 'class' => 'filename_uploaded']) . '
                        </div>
                        <div class="drag_field_file">
                            ' . FormField::show('file', ['name' => $name . '_file', 'id' => $id . '_file', 'layout' => 'simple', 'class' => 'filename_input', 'accept' => 'image/png, image/jpeg']) . '
                        </div>
                        <div class="drag_field_image"><img src=""/></div>
                        <div class="drag_field_drag">
                            <div class="drag_field_drag_message">
                                <i class="fa fa-image"></i>
                                <span>' . __('select_drag_image_here') . '</span>
                                ' . (($maxDimensions) ? '<span class="drag_field_drag_message_small">' . str_replace('#HEIGHT', ASTERION_HEIGHT_MAX_HUGE, str_replace('#WIDTH', ASTERION_WIDTH_HUGE, __('image_reduced_dimensions'))) . '</span>' : '') . '
                            </div>
                        </div>
                        ' . $loader . '
                    </div>
                </div>';
            $urlUploadTemp = $this->object->urlUploadTempImage();
        } else {
            $field = '
                <div class="drag_field_wrapper_all">
                    <div class="drag_field_file_name">
                        <span>' . __('file_to_upload') . ' : </span>
                        <em></em>
                    </div>
                    <div class="drag_field_wrapper_file">' . $this->renderFile($valueFile) . '</div>
                    <div class="drag_field_wrapper">
                        <div class="drag_field_wrapper_input">
                            <div class="drag_field_input">
                                ' . FormField::show('hidden', ['name' => $name, 'id' => $id, 'class' => 'filevalue']) . '
                                ' . FormField::show('hidden', ['name' => $name . '_filename', 'id' => $id . '_filename', 'class' => 'filename']) . '
                                ' . FormField::show('hidden', ['name' => $name . '_uploaded', 'id' => $id . '_uploaded', 'class' => 'filename_uploaded']) . '
                            </div>
                            <div class="drag_field_file">
                                ' . FormField::show('file', ['name' => $name . '_file', 'id' => $id . '_file', 'layout' => 'simple', 'class' => 'filename_input']) . '
                            </div>
                            <div class="drag_field_drag">
                                <div class="drag_field_drag_message">
                                    <i class="fa fa-file"></i>
                                    <span>' . __('select_drag_file_here') . '</span>
                                </div>
                            </div>
                        </div>
                        ' . $loader . '
                    </div>
                </div>';
            $urlUploadTemp = $this->object->urlUploadTempFile();
        }
        return '<div class="' . $type . ' form_field ' . $class . ' ' . $required . ' ' . $classError . '" data-urluploadtemp="' . $urlUploadTemp . '">
                    <div class="form_field_ins">
                        ' . $label . '
                        ' . $error . '
                        ' . $messageBefore . '
                        ' . $field . '
                        ' . $messageAfter . '
                    </div>
                </div>';
    }

    public function renderImage($valueFile)
    {
        $file = ASTERION_STOCK_FILE . $this->object->className . '/' . $valueFile . '/' . $valueFile . '_thumb.jpg';
        $file = (!is_file($file)) ? ASTERION_STOCK_FILE . $this->object->className . '/' . $valueFile . '/' . $valueFile . '_small.jpg' : $file;
        $file = (!is_file($file)) ? ASTERION_STOCK_FILE . $this->object->className . '/' . $valueFile . '/' . $valueFile . '_web.jpg' : $file;
        if (is_file($file)) {
            $objectUiClassname = $this->object->className . '_Ui';
            $objectUi = new $objectUiClassname($this->object);
            return '<div class="form_fields_image">
                        <div class="form_fields_image_ins">
                            <div class="form_fields_image_delete" data-confirm="' . __('are_you_sure_delete') . '" data-url="' . $objectUi->object->urlDeleteImage($valueFile) . '">
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
            $objectUiClassname = $this->object->className . '_Ui';
            $objectUi = new $objectUiClassname($this->object);
            return '
                <div class="form_fields_file">
                    <div class="form_fields_file_ins">
                        <a href="' . str_replace(ASTERION_STOCK_FILE, ASTERION_STOCK_URL, $file) . '" target="_blank">
                            <i class="fa fa-file"></i>
                            <span>' . __('view_file') . ' ' . substr($valueFile, 0, 30) . '</span>
                        </a>
                        <span class="form_fields_file_delete" data-confirm="' . __('are_you_sure_delete') . '" data-url="' . $objectUi->object->urlDeleteFile($valueFile) . '">
                            <i class="fa fa-times"></i>
                            <span>' . __('delete_file') . '</span>
                        </span>
                    </div>
                </div>';
        }
    }

}
