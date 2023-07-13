<?php
/**
 * @class LogAdmin
 *
 * This class represents the wrapup for the emails
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class LogAdmin extends Db_Object
{

    public static function log($type, $log)
    {
        // Reduce the size of the huge logs (images, files)
        if (is_array($log)) {
            foreach ($log as $key => $item) {
                if (is_string($item) && strlen($item) > 10240) {
                    $log[$key] = substr($item, 0, 10240) . ' [...]';
                }
            }
        }
        $login = UserAdmin_Login::getInstance();
        $logAdmin = new LogAdmin([
            'ip' => Ip::get(),
            'id_user_admin' => $login->id(),
            'type' => $type,
            'log' => json_encode($log),
        ]);
        return $logAdmin->persist();
    }

}
