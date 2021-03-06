<?php
/**
 * @class HtmlSectionAdminUi
 *
 * This class manages the UI for the HtmlSectionAdmin objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class HtmlSectionAdmin_Ui extends Ui
{

    /**
     * Render an HTML section
     */
    public function renderPublic()
    {
        return '<div class="page-complete">' . $this->object->get('section') . '</div>';
    }

}
