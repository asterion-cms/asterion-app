<?php
/**
 * @class Backup_Ui
 *
 * This class manages the UI for the Backup object.
 * Here we render the template for the administration area.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class Backup_Ui extends Ui
{

    /**
     * Show the intro screen of the backup page.
     */
    public static function intro()
    {
        $info = '';
        $objectNames = File::scanDirectoryObjects();
        sort($objectNames);
        foreach ($objectNames as $objectName) {
            $object = new $objectName();
            $info .= '
                <div class="simple_grid_line">
                    <div class="simple_grid_item simple_grid_item_8">
                        ' . $objectName . ' <span class="gray tiny">( ' . $object->countResults() . ' ' . __('items') . ' )</span>
                    </div>
                    <div class="simple_grid_item simple_grid_item_right simple_grid_item_4">
                        <div class="buttons buttons_small">
                            <a href="' . url('backup/reset_object/' . $objectName, true) . '" class="button button_important">' . __('reset') . '</a>
                            <a href="' . url('backup/json/' . $objectName, true) . '" class="button" target="_blank">' . __('backup_json') . '</a>
                            <a href="' . url('backup/sql/' . $objectName, true) . '" class="button" target="_blank">' . __('backup_sql') . '</a>
                        </div>
                    </div>
                </div>';
        }
        return '
            <div class="information">' . __('backup_information') . '</div>
            <h2>' . __('backup') . '</h2>
            <div class="button_cards button_cards2">
                <div class="button_card">
                    <a href="' . url('backup/sql', true) . '" target="_blank">
                        <i class="fa fa-database"></i>
                        <p><strong>' . __('sql_format') . '</strong></p>
                        <p>' . __('sql_format_disclaimer') . '</p>
                    </a>
                </div>
                <div class="button_card">
                    <a href="' . url('backup/json', true) . '">
                        <i class="fa fa-file-code"></i>
                        <p><strong>' . __('json_format') . '</strong></p>
                        <p>' . __('json_format_disclaimer') . '</p>
                    </a>
                </div>
            </div>
            <h2>' . __('reset_objects') . '</h2>
            <div class="simple_grid simple_grid_zebra simple_grid_border">' . $info . '</div>';
    }

}
