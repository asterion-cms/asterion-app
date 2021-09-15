<?php
/**
 * @class UserAdminUi
 *
 * This class manages the UI for the UserAdmin objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class UserAdmin_Ui extends Ui
{

    public static function infoHtml()
    {
        $login = UserAdmin_Login::getInstance();
        if ($login->isConnected()) {
            return '
                <div class="info_user">
                    <div class="info_user_menu">
                        <div class="info_user_menu_title">
                            <i class="fa fa-user"></i>
                            <span>'.$login->get('label').'</span>
                        </div>
                        <div class="info_user_menu_items">
                            <a href="' . url('user_admin/profile', true) . '">
                                <i class="fa fa-user"></i>
                                <span>' . __('profile') . '</span>
                            </a>
                            <a href="' . url('user_admin/update_email', true) . '">
                                <i class="fa fa-envelope"></i>
                                <span>' . __('update_email') . '</span>
                            </a>
                            <a href="' . url('user_admin/update_password', true) . '">
                                <i class="fa fa-lock"></i>
                                <span>' . __('update_password') . '</span>
                            </a>
                        </div>
                    </div>
                    <a href="' . url('user_admin/logout', true) . '" class="info_user_logout">
                        <i class="fa fa-power-off"></i>
                    </a>
                </div>';
        }
    }

}
