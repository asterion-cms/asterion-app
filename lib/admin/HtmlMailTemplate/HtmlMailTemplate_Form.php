<?php
/**
 * @class HtmlMailTemplateForm
 *
 * This class manages the forms for the HtmlMailTemplate objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class HtmlMailTemplate_Form extends Form
{

    public function createFormModifyAdministrator()
    {
        $information = (string) $this->object->info->info->form->information;
        return '
			<div class="information">' . __($information) . '</div>
			' . parent::createFormModifyAdministrator();
    }

}
