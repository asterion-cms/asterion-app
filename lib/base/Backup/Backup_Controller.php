<?php
/**
 * @class BackupController
 *
 * This is the controller for managing the cache in the administration area.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class Backup_Controller extends Controller
{

    /**
     * Main function to control the administration system.
     */
    public function getContent()
    {
        $ui = new NavigationAdmin_Ui($this);
        $this->mode = 'admin';
        $this->title_page = __('cache');
        switch ($this->action) {
            default:
                $this->checkLoginAdmin();
                $this->title_page = __('backup');
                File::createDirectory(ASTERION_BASE_FILE . 'data/backup');
                if (!is_writable(ASTERION_BASE_FILE . 'data/backup')) {
                    $this->message_error = str_replace('#DIRECTORY', ASTERION_BASE_FILE . 'data/backup', __('directory_not_writable'));
                } else {
                    $this->content = Backup_Ui::intro();
                }
                return $ui->render();
                break;
            case 'reset_object':
                $this->checkLoginAdmin();
                $this->mode = 'ajax';
                try {
                    $objectName = $this->id;
                    $object = new $objectName;
                    Db::execute('DROP TABLE IF EXISTS `' . $object->tableName . '`');
                } catch (Exception $e) {
                }
                header('Location: ' . url('backup', true));
                exit();
                break;
            case 'sql':
                $this->checkLoginAdmin();
                $this->mode = 'ajax';
                Backup::backupSql($this->id);
                return '';
                break;
            case 'json':
                $this->checkLoginAdmin();
                $this->mode = 'ajax';
                if ($this->id != '') {
                    Backup::backupJson($this->id);
                } else {
                    $this->mode = 'zip';
                    Backup::backupJson();
                }
                return '';
                break;
        }
    }

}
