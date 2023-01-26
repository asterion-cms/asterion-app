<?php
/**
 * @class Permission
 *
 * This class represents the permissions for objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Permission extends Db_Object
{

    /**
     * Function to check if the logged user has an specific permission on an object.
     */
    public static function getPermission($permissionCheck, $objectName)
    {
        $login = UserAdmin_Login::getInstance();
        if ($login->isConnected()) {
            $user = $login->user();
            if ($user->managesPermissions()) {
                return true;
            }
            $userAdminType = (new UserAdminType)->read($user->get('id_user_admin_type'));
            $permission = (new Permission)->readFirst(['where' => 'object_name="' . $objectName . '" AND id_user_admin_type="' . $userAdminType->id() . '" AND ' . $permissionCheck . '="1"']);
            return ($permission->id() != '');
        }
        return false;
    }

    /**
     * Function to check if the logged user can list items.
     */
    public static function canListAdmin($objectName)
    {
        return Permission::getPermission('permission_list_items', $objectName);
    }

    /**
     * Function to check if the logged user can list items.
     */
    public static function canInsert($objectName)
    {
        return Permission::getPermission('permission_insert', $objectName);
    }

    /**
     * Function to check if the logged user can list items.
     */
    public static function canModify($objectName)
    {
        return Permission::getPermission('permission_modify', $objectName);
    }

    /**
     * Function to check if the logged user can list items.
     */
    public static function canDelete($objectName)
    {
        return Permission::getPermission('permission_delete', $objectName);
    }

    /**
     * Function to check all the permissions for the logged user on an object.
     */
    public static function getAll($objectName)
    {
        $login = UserAdmin_Login::getInstance();
        if ($login->isConnected()) {
            $user = $login->user();
            if ($user->managesPermissions()) {
                return [
                    'permission_list_items' => 1,
                    'permission_insert' => 1,
                    'permission_modify' => 1,
                    'permission_delete' => 1
                ];
            }
            $userAdminType = (new UserAdminType)->read($user->get('id_user_admin_type'));
            $permission = (new Permission)->readFirst(['where' => 'object_name="' . $objectName . '" AND id_user_admin_type="' . $userAdminType->id() . '"']);
            return [
                'permission_list_items' => $permission->get('permission_list_items'),
                'permission_insert' => $permission->get('permission_insert'),
                'permission_modify' => $permission->get('permission_modify'),
                'permission_delete' => $permission->get('permission_delete')
            ];
        }
        return [
            'permission_list_items' => 0,
            'permission_insert' => 0,
            'permission_modify' => 0,
            'permission_delete' => 0
        ];
    }

}
