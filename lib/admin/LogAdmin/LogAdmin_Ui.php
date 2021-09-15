<?php
/**
 * @class LogAdminUi
 *
 * This class manages the UI for the LogAdmin objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class LogAdmin_Ui extends Ui
{

    public function renderPublic($options = [])
    {
        $users = (isset($options['users'])) ? $options['users'] : [];
        return '
			<div class="simple_grid_line">
				<div class="simple_grid_item simple_grid_item_2">' . $this->object->get('created') . '</div>
				<div class="simple_grid_item simple_grid_item_2">
					' . (isset($users[$this->object->get('id_user_admin')]) ? $users[$this->object->get('id_user_admin')] : '&nbsp;') . '
				</div>
				<div class="simple_grid_item simple_grid_item_2">' . $this->object->get('type') . '</div>
				<div class="simple_grid_item simple_grid_item_6 log_detail">' . htmlspecialchars($this->object->get('log')) . '</div>
			</div>';
    }

    public static function intro()
    {
        $items = new ListObjects('LogAdmin', ['order' => 'created DESC', 'limit' => 500]);
        $userAdmin = new UserAdmin;
        return (!$items->isEmpty()) ? '
			<div class="logs simple_grid simple_grid_border simple_grid_zebra">
				' . $items->showList(['function' => 'Public'], ['users' => $userAdmin->basicInfoArray()]) . '
			</div>
			' : '';
    }

}
