<?php
/**
 * @class HtmlMailTemplateController
 *
 * This class is the controller for the HtmlMailTemplate objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class HtmlMailTemplate_Controller extends Controller
{

    /**
     * Overwrite the list_items function of this controller.
     */
    public function listAdmin()
    {
        $item = (new HtmlMailTemplate)->readFirst();
        if ($item->id() != '') {
            header('Location: ' . url('html_mail_template/modify_view/' . $item->id(), true));
        } else {
            return parent::getContent();
        }
    }

    /**
     * Overwrite the menuInside function of this controller.
     */
    public function menuInside()
    {
        if ($this->action != 'modify_view' && $this->action != 'modify_view_check') {
            return parent::menuInside();
        }
    }

}
