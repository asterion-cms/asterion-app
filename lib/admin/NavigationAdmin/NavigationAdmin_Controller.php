<?php
/**
 * @class NavigationAdminController
 *
 * This is the controller for all the actions in the administration area.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class NavigationAdmin_Controller extends Controller
{

    /**
     * Main function to control the administration system.
     */
    public function getContent()
    {
        $ui = new NavigationAdmin_Ui($this);
        $this->login = UserAdmin_Login::getInstance();
        $this->login->checkLoginRedirect();
        $this->mode = 'admin';
        switch ($this->action) {
            default:
                $this->content = '
                    <h1>' . Parameter::code('meta_title_page') . '</h1>
                    <div class="information">' . HtmlSectionAdmin::show('intro') . '</div>
                    <div class="menu_home">' . $ui->renderMenu() . '</div>';
                return $ui->render();
                break;
            case 'permissions':
                $this->title_page = ASTERION_TITLE;
                $this->message_error = __('permissions_error');
                return $ui->render();
                break;
        }
    }

}
