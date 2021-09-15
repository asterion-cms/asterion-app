<?php
/**
 * @class PermissionController
 *
 * This class is the controller for the Permission objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Permission_Controller extends Controller
{

    public function getContent()
    {
        switch ($this->action) {
            default:
                return parent::getContent();
                break;
            case 'insert':
                $this->checkLoginAdmin();
                $objectNames = File::scanDirectoryObjects();
                $user_admin_types = (new UserAdminType)->readList();
                Db::execute('TRUNCATE ' . (new Permission)->tableName);
                foreach ($objectNames as $objectName) {
                    foreach ($user_admin_types as $user_admin_type) {
                        $permission = new Permission(['id_user_admin_type' => $user_admin_type->id(), 'object_name' => $objectName]);
                        $permission->persist();
                        if (isset($this->values['permission_list_items_' . $user_admin_type->id() . '_' . $objectName])) {
                            $permission->persistSimple('permission_list_items', 1);
                        }
                        if (isset($this->values['permission_insert_' . $user_admin_type->id() . '_' . $objectName])) {
                            $permission->persistSimple('permission_insert', 1);
                        }
                        if (isset($this->values['permission_modify_' . $user_admin_type->id() . '_' . $objectName])) {
                            $permission->persistSimple('permission_modify', 1);
                        }
                        if (isset($this->values['permission_delete_' . $user_admin_type->id() . '_' . $objectName])) {
                            $permission->persistSimple('permission_delete', 1);
                        }
                    }
                }
                header('Location: ' . url('permission/list_items', true));
                break;
        }
    }

    /**
     * Overwrite the list_items function of this controller.
     */
    public function listAdmin()
    {
        $html = '';
        $this->menu_inside = '';
        $objectNames = File::scanDirectoryObjectsBase();
        $html .= $this->listObjects($objectNames, 'form_permissions_base');
        $objectNames = File::scanDirectoryObjectsApp();
        $html .= $this->listObjects($objectNames, 'form_permissionsApp');
        $information = (string) $this->object->info->info->form->information;
        if ($html == '') {
            return '<div class="message message_error">' . __('permissions_empty_administrators') . '</div>';
        } else {
            return '
                ' . (($information != '') ? '<div class="information">' . __($information) . '</div>' : '') . '
                <div class="form_permissionss">
                    ' . Form::createForm($html, ['action' => url('permission/insert', true), 'submit' => __('save')]) . '
                </div>';
        }
    }

    /**
     * List the objects and the permissions for each one.
     */
    public function listObjects($objectNames, $class = '')
    {
        $html = '';
        $user_admin_types = (new UserAdminType)->readList(['where' => 'manages_permissions!="1"']);
        if (count($user_admin_types) > 0) {
            foreach ($objectNames as $objectName) {
                $htmlPermissions = '';
                $object = new $objectName();
                foreach ($user_admin_types as $user_admin_type) {
                    $permission = (new Permission)->readFirst(['where' => 'id_user_admin_type="' . $user_admin_type->id() . '" AND object_name="' . $objectName . '"']);
                    $htmlPermissions .= '
                        <div class="form_permissions_option">
                            <div class="form_permissions_option_user">' . $user_admin_type->getBasicInfo() . '</div>
                            <div class="form_permissions_option_checkboxes">
                                ' . FormField::show('checkbox', ['name' => 'permission_list_items_' . $user_admin_type->id() . '_' . $objectName, 'label' => __('permission_list_items'), 'value' => $permission->get('permission_list_items')]) . '
                                ' . FormField::show('checkbox', ['name' => 'permission_insert_' . $user_admin_type->id() . '_' . $objectName, 'label' => __('permission_insert'), 'value' => $permission->get('permission_insert')]) . '
                                ' . FormField::show('checkbox', ['name' => 'permission_modify_' . $user_admin_type->id() . '_' . $objectName, 'label' => __('permission_modify'), 'value' => $permission->get('permission_modify')]) . '
                                ' . FormField::show('checkbox', ['name' => 'permission_delete_' . $user_admin_type->id() . '_' . $objectName, 'label' => __('permission_delete'), 'value' => $permission->get('permission_delete')]) . '
                            </div>
                        </div>';
                }
                $html .= '
                    <div class="form_permissions ' . $class . '">
                            <div class="form_permissions_object">' . $objectName . '</div>
                            <div class="form_permissions_options">
                                <div class="form_permissions_options_ins">
                                    ' . $htmlPermissions . '
                                </div>
                            </div>
                        </div>';
            }
        }
        return $html;
    }

}
