<?php
/**
 * @class CacheController
 *
 * This is the controller for managing the cache in the administration area.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class Cache_Controller extends Controller
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
                File::createDirectory(ASTERION_BASE_FILE . 'cache', false);
                if (!is_writable(ASTERION_BASE_FILE . 'cache')) {
                    $this->message_error = str_replace('#DIRECTORY', ASTERION_BASE_FILE . 'cache', __('directory_not_writable'));
                } else {
                    $this->content = Cache_Ui::intro();
                }
                return $ui->render();
                break;
            case 'all':
                $this->checkLoginAdmin();
                File::createDirectory(ASTERION_BASE_FILE . 'cache', false);
                if (!is_writable(ASTERION_BASE_FILE . 'cache')) {
                    $this->message_error = str_replace('#DIRECTORY', ASTERION_BASE_FILE . 'cache', __('directory_not_writable'));
                    return $ui->render();
                } else {
                    Cache::cacheAll();
                }
                header('Location: ' . url('cache', true));
                exit();
                break;
            case 'object':
                $this->checkLoginAdmin();
                if (class_exists($this->id . '_Ui')) {
                    File::createDirectory(ASTERION_BASE_FILE . 'cache', false);
                    File::createDirectory(ASTERION_BASE_FILE . 'cache/' . $this->id, false);
                    if (!is_writable(ASTERION_BASE_FILE . 'cache/' . $this->id)) {
                        $this->message_error = str_replace('#DIRECTORY', ASTERION_BASE_FILE . 'cache', __('directory_not_writable'));
                        return $ui->render();
                    } else {
                        Cache::cacheObject($this->id);
                    }
                }
                header('Location: ' . url('cache', true));
                exit();
                break;
        }
    }

}
