<?php
/**
 * @class ParameterForm
 *
 * This class manages the forms for the Parameter objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Parameter_Form extends Form
{

	public function createFormFields($options = []) {
		$initialData = ($this->object->id()!='' && $this->object->get('image')!='') ? 'image' : 'information';
		$initialData = ($this->object->id()!='' && $this->object->get('file')!='') ? 'file' : $initialData;
		return '
			'.$this->field('id').'
			'.$this->field('code').'
			'.$this->field('name').'
			<div class="form_parameter_wrapper">
				<div class="form_parameter_options">
					<div class="form_parameter_option '.(($initialData=='information') ? 'form_parameter_option_selected' : '').'" data-type="information">
						<i class="fa fa-keyboard"></i>
						<span>'.__('text_information').'</span>
					</div>
					<div class="form_parameter_option '.(($initialData=='image') ? 'form_parameter_option_selected' : '').'" data-type="image">
						<i class="fa fa-image"></i>
						<span>'.__('image').'</span>
					</div>
					<div class="form_parameter_option '.(($initialData=='file') ? 'form_parameter_option_selected' : '').'" data-type="file">
						<i class="fa fa-file"></i>
						<span>'.__('file').'</span>
					</div>
				</div>
				<div class="form_parameter_items">
					<div class="form_parameter_item '.(($initialData=='information') ? 'form_parameter_item_selected' : '').' form_parameter_item_information">'.$this->field('information').'</div>
					<div class="form_parameter_item '.(($initialData=='image') ? 'form_parameter_item_selected' : '').' form_parameter_item_image">'.$this->field('image').'</div>
					<div class="form_parameter_item '.(($initialData=='file') ? 'form_parameter_item_selected' : '').' form_parameter_item_file">'.$this->field('file').'</div>
				</div>
			</div>';
	}

}
