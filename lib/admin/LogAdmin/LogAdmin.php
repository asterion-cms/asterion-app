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
        $login = UserAdmin_Login::getInstance();
        $log = new LogAdmin([
            'ip' => Ip::get(),
            'id_user_admin' => $login->id(),
            'type' => $type,
            'log' => json_encode($log),
        ]);
        return $log->persist();
    }

}
