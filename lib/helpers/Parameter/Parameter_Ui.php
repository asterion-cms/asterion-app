<?php
/**
 * @class ParameterUi
 *
 * This class manages the UI for the Parameter objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Parameter_Ui extends Ui
{

    public function label($canModify = false)
    {
        $information = ($this->object->get('information') != '') ? '<p>' . htmlentities($this->object->get('information')) . '</p>' : '';
        $information .= ($this->object->get('image') != '') ? '<div class="image">' . $this->object->getImageIcon('image') . '</div>' : '';
        $information .= ($this->object->get('file') != '') ? $this->object->getFileLink('file') : '';
        return '
			<div class="label_simple">
				<p class="accent"><strong>' . $this->object->get('name') . '</strong><span class="tiny gray"> ( ' . __('code') . ' : ' . $this->object->get('code') . ' )</span></p>
                <p>' . $information . '</p>
			</div>';
    }

}
