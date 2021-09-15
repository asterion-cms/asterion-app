<?php
/**
 * @class FormFieldPoint
 *
 * This is a helper class to generate a point form field.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class FormField_Point extends FormField_Base
{

    /**
     * The constructor of the object.
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->options['typeField'] = (isset($options['typeField'])) ? $options['typeField'] : 'point';
        $this->options['latitude'] = (isset($options['latitude'])) ? $options['latitude'] : ((isset($this->values[$this->name . '_lat'])) ? $this->values[$this->name . '_lat'] : 0);
        $this->options['longitude'] = (isset($options['longitude'])) ? $options['longitude'] : ((isset($this->values[$this->name . '_lng'])) ? $this->values[$this->name . '_lng'] : 0);
    }

    /**
     * Render a default input element.
     */
    public function show()
    {
        $options = $this->options;
        $id = (isset($options['id'])) ? $options['id'] : '';
        $label = (isset($options['label']) && $options['label']!='') ? '<label>' . __($options['label']) . '</label>' : '';
        $value = (isset($options['value'])) ? $options['value'] : '';
        $latitude = (isset($options['latitude'])) ? $options['latitude'] : '';
        $longitude = (isset($options['longitude'])) ? $options['longitude'] : '';
        $name = (isset($options['name'])) ? $options['name'] : '';
        $error = (isset($options['error']) && $options['error'] != '') ? '<div class="error_message">' . $options['error'] . '</div>' : '';
        $errorClass = (isset($options['error']) && $options['error'] != '') ? 'error' : '';
        $class = (isset($options['class'])) ? $options['class'] : '';
        $class .= (isset($options['nameSimple'])) ? ' form_field_' . Text::simpleUrl($options['nameSimple'], '_') : '';
        $layout = (isset($options['layout'])) ? $options['layout'] : '';
        switch ($layout) {
            default:
                return '<div class="point form_field ' . $class . ' ' . $errorClass . '">
                            ' . $label . '
                            ' . $error . '
                            <label><span>' . __('latitude') . '</span></label>
                            '.FormField::show('text', ['name'=>$name.'_lat', 'value'=>$latitude, 'class'=>'input_latitude']).'
                            '.FormField::show('text', ['name'=>$name.'_lng', 'value'=>$longitude, 'class'=>'input_longitude']).'
                        </div>';
                break;
            case 'map':
                $latitude = ($latitude != '' && $latitude != '0') ? $latitude : '';
                $longitude = ($longitude != '' && $longitude != '0') ? $longitude : '';
                return '
                    <div class="point point_map form_field ' . $class . ' ' . $errorClass . '">
                        ' . $label . '
                        <div class="map map_'.(($latitude=='') ? 'show' : 'hide').'"
                            data-latitude="' . $latitude . '"
                            data-longitude="' . $longitude . '"
                            data-zoom="' . Parameter::code('map_init_zoom') . '"
                            data-initlatitude="' . Parameter::code('map_init_latitude') . '"
                            data-initlongitude="' . Parameter::code('map_init_longitude') . '">
                            <div class="map_options">
                                <div class="map_option map_option_show">
                                    <div class="map_option_ins">
                                        <i class="fa fa-eye"></i>
                                        <span>'.__('show_map').'</span>
                                    </div>
                                </div>
                                <div class="map_option map_option_hide">
                                    <div class="map_option_ins">
                                        <i class="fa fa-eye-slash"></i>
                                        <span>'.__('hide_map').'</span>
                                    </div>
                                </div>
                            </div>
                            <div class="map_wrapper" id="' . substr(md5(rand() * rand() * rand()), 0, 6) . '"></div>
                            '.FormField::show('hidden', ['name'=>$name.'_lat', 'value'=>$latitude, 'class'=>'input_latitude']).'
                            '.FormField::show('hidden', ['name'=>$name.'_lng', 'value'=>$longitude, 'class'=>'input_longitude']).'
                        </div>
                    </div>';
                break;
        }
    }

}
