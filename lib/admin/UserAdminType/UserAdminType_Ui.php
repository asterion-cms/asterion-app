<?php
/**
 * @class UserAdminTypeUi
 *
 * This class manages the UI for the UserAdminType objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class UserAdminType_Ui extends Ui
{

    public function renderAdmin($options = [])
    {
        if ($this->object->get('code') == 'superadmin') {
            return parent::renderAdmin(array_merge($options, ['cannotModify' => true, 'cannotDelete' => true]));
        }
        return parent::renderAdmin($options);
    }

}
