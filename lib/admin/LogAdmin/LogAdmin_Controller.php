<?php
/**
 * @class LogAdminController
 *
 * This class is the controller for the LogAdmin objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class LogAdmin_Controller extends Controller
{

	public function listAdmin()
    {
        $information = (string) $this->object->info->info->form->information;
        return '
            ' . (($information != '') ? '<div class="information">' . __($information) . '</div>' : '') . '
            ' . LogAdmin_Ui::intro();
    }

    public function menuInside()
    {
        return '';
    }

}
