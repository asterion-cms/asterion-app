<?php
/**
 * @class UserAdminLogin
 *
 * This class manages the login UserAdmin objects.
 * It is a singleton, so it can only be instantiated one object using a function.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class UserAdmin_Login extends User_Login_Interface
{

    public $userClassName = 'UserAdmin';

    public function autoLogin($user)
    {
        $this->info['id'] = $user->id();
        $this->info['email'] = $user->get('email');
        $this->info['label'] = $user->getBasicInfo();
        $this->info['id_user_admin_type'] = $user->get('id_user_admin_type');
        $this->sessionAdjust($this->info);
    }

}
