<?php
/**
 * @class UserAdmin
 *
 * This class defines the users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class UserAdmin extends User_Interface
{

    public $userClassName = 'UserAdmin';
    public $userLoginClassName = 'UserAdmin_Login';
    public $userFormClassName = 'UserAdmin_Form';

    public function __construct($values = [])
    {
        parent::__construct($values);

        $this->urlLogin = url('user_admin/login', true);
        $this->urlLoginFacebook = url('user_admin/login_facebook', true);
        $this->urlRegister = url('user_admin/register', true);
        $this->urlActivate = url('user_admin/activate', true);
        $this->urlForgot = url('user_admin/forgot', true);
        $this->urlUpdateDefaultPassword = url('user_admin/update_default_password', true);
        $this->urlUpdatePassword = url('user_admin/update_password', true);
        $this->urlUpdateEmail = url('user_admin/update_email', true);
        $this->urlUpdateEmailConfirm = url('user_admin/update_email_confirm', true);
        $this->urlProfile = url('user_admin/profile', true);
        $this->urlLogout = url('user_admin/logout', true);
        $this->urlHome = url('', true);
        $this->urlConnected = url('', true);
    }

    public function managesPermissions()
    {
        $userAdminType = (new UserAdminType)->read($this->get('id_user_admin_type'));
        return ($userAdminType->get('manages_permissions') == '1') ? true : false;
    }

}
