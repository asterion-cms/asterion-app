<?php
/**
 * @class UserAdminController
 *
 * This class is the controller for the UserAdmin objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class UserAdmin_Controller extends User_Controller_Interface
{

    public $userClassName = 'UserAdmin';
    public $mode = 'admin';
    public $layout_page = 'simple';

    public function __construct($GET, $POST, $FILES)
    {
        parent::__construct($GET, $POST, $FILES);
        $this->ui = new NavigationAdmin_Ui($this);
    }

    public function getContent()
    {
        switch ($this->action) {
            default:
                return parent::getContent();
                break;
            case 'profile':
            case 'updated_profile':
            case 'update_password':
            case 'password_updated':
            case 'update_email':
            case 'update_email_confirm':
            case 'email_updated':
                $this->layout_page = '';
                return parent::getContent();
                break;
        }
    }

}
