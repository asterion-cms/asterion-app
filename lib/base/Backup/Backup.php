<?php
/**
 * @class Backup
 *
 * This is a helper class to manage the backups.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Backup
{

    /**
     * Backup the information of an object in JSON format.
     */
    public static function backupJson($className = '')
    {
        if ($className == '') {
            $objectNames = File::scanDirectoryObjects();
            File::createDirectory(ASTERION_BASE_FILE . 'data', false);
            File::createDirectory(ASTERION_BASE_FILE . 'data/backup', false);
            foreach ($objectNames as $objectName) {
                $object = new $objectName();
                $fileJson = ASTERION_BASE_FILE . 'data/backup/' . $objectName . '.json';
                $query = 'SELECT * FROM ' . $object->tableName;
                $items = Db::returnAll($query);
                File::saveFile($fileJson, json_encode($items, JSON_PRETTY_PRINT));
            }
            $zipname = 'backup.zip';
            $zip = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);
            foreach (glob(ASTERION_BASE_FILE . 'data/backup/*.json') as $file) {
                $zip->addFile($file, 'backup/' . basename($file));
            }
            $zip->close();
            header('Content-disposition: attachment; filename=' . $zipname);
            header('Content-Length: ' . filesize($zipname));
            readfile($zipname);
            @unlink($zipname);
        } else {
            try {
                $object = new $className;
                if (Db::tableExists($object->tableName)) {
                    $query = 'SELECT * FROM ' . $object->tableName;
                    $items = Db::returnAll($query);
                    File::download($className . '.json', ['content' => json_encode($items, JSON_PRETTY_PRINT), 'contentType' => 'application/json']);
                }
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Backup the information of an object in SQL format.
     */
    public static function backupSql($className = '')
    {
        if ($className == '') {
            $content = shell_exec('mysqldump --user=' . ASTERION_DB_USER . ' --password=' . ASTERION_DB_PASSWORD . ' --host=' . ASTERION_DB_SERVER . ' --port=' . ASTERION_DB_PORT . ' ' . ASTERION_DB_NAME);
            File::download('backup.sql', ['content' => $content, 'contentType' => 'text/plain']);
        } else {
            try {
                $object = new $className;
                $content = shell_exec('mysqldump --user=' . ASTERION_DB_USER . ' --password=' . ASTERION_DB_PASSWORD . ' --host=' . ASTERION_DB_SERVER . ' --port=' . ASTERION_DB_PORT . ' ' . ASTERION_DB_NAME . ' ' . $object->tableName);
                File::download($className . '.sql', ['content' => $content, 'contentType' => 'text/plain']);
            } catch (Exception $e) {
            }
        }
    }

}
