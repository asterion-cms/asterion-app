<?php
/**
 * @class UserAdminForm
 *
 * This class manages the forms for the UserAdmin objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class UserAdmin_Form extends User_Form_Interface
{

    public $userClassName = 'UserAdmin';

    public function createFormFields($options = []) {
        return '
            '.$this->field('id_user_admin_type').'
            '.parent::createFormFields($options);
    }

    public function login($options = [])
    {
        return parent::login(['class' => 'form_admin', 'no_register' => true]);
    }

    public function register($options = [])
    {
        header('Location: ' . (new $this->userClassName)->urlLogin);
        exit();
    }

    public function forgot($options = [])
    {
        return parent::forgot(['class' => 'form_admin']);
    }

    public function changePassword($options = [])
    {
        return parent::changePassword(['class' => 'form_admin']);
    }

    public function changeEmail($options = [])
    {
        return parent::changeEmail(['class' => 'form_admin']);
    }

    public function changeDefaultPassword($options = [])
    {
        return parent::changeDefaultPassword(['class' => 'form_admin']);
    }

    public function confirmChangeEmail($options = [])
    {
        return parent::confirmChangeEmail(['class' => 'form_admin']);
    }

    public function profile($options = [])
    {
        return parent::profile(['class' => 'form_admin']);
    }

}
