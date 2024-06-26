<?php
/**
 * @class Db
 *
 * This class has all the static methods to communicate and execute queries in the MySQL database.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Db
{

    /**
     * Execute a query
     */
    public static function execute($query, $values = [])
    {
        $query = str_replace('\"', '"', $query);
        $db = Db_Connection::getInstance();
        Debug::startRecordingQuery($query, $values);
        $db->execute($query, $values);
        Debug::stopRecordingQuery($query, $values);
    }

    /**
     * Execute multiple queries in an array
     */
    public static function executeMultiple($queries)
    {
        foreach ($queries as $query) {
            Debug::startRecordingQuery($query);
            Db::execute($query);
            Debug::stopRecordingQuery($query);
        }
    }

    /**
     * Execute a query in a direct way
     */
    public static function executeSimple($query, $values = [])
    {
        $db = Db_Connection::getInstance();
        Debug::startRecordingQuery($query, $values);
        $db->execute($query, $values);
        Debug::stopRecordingQuery($query, $values);
    }

    /**
     * Return a single element
     */
    public static function returnSingle($query, $values = [], $exception = true)
    {
        try {
            $query = str_replace('\"', '"', $query);
            $db = Db_Connection::getInstance();
            Debug::startRecordingQuery($query, $values);
            $prepare_execute = $db->getPDOStatement($query);
            $prepare_execute->execute($values);
            $fetch = $prepare_execute->fetch(PDO::FETCH_ASSOC);
            Debug::stopRecordingQuery($query, $values);
            return (is_array($fetch)) ? $fetch : [];
        } catch (Exception $error) {
            if (!$exception) {
                return [];
            }
            if (ASTERION_DEBUG) {
                throw new Exception('<pre>' . $error->getMessage() . '</pre>');
            }
        }
    }

    /**
     * Return a list of elements
     */
    public static function returnAll($query, $values = [], $exception = true)
    {
        try {
            $query = str_replace('\"', '"', $query);
            $db = Db_Connection::getInstance();
            Debug::startRecordingQuery($query, $values);
            $prepare_execute = $db->getPDOStatement($query);
            $prepare_execute->execute($values);
            $fetch = $prepare_execute->fetchAll(PDO::FETCH_ASSOC);
            Debug::stopRecordingQuery($query, $values);
            return (is_array($fetch)) ? $fetch : [];
        } catch (Exception $error) {
            if (!$exception) {
                return [];
            }
            if (ASTERION_DEBUG) {
                throw new Exception('<pre>' . $error->getMessage() . '</pre>');
            }
        }
    }

    /**
     * Return a list of columns
     */
    public static function returnAllColumn($query, $values = [], $exception = true)
    {
        try {
            $query = str_replace('\"', '"', $query);
            $db = Db_Connection::getInstance();
            Debug::startRecordingQuery($query, $values);
            $prepare_execute = $db->getPDOStatement($query);
            $prepare_execute->execute();
            $fetch = $prepare_execute->fetchAll(PDO::FETCH_COLUMN);
            Debug::stopRecordingQuery($query, $values);
            return (is_array($fetch)) ? $fetch : [];
        } catch (Exception $error) {
            if (!$exception) {
                return [];
            }
            if (ASTERION_DEBUG) {
                throw new Exception('<pre>' . $error->getMessage() . '</pre>');
            }
        }
    }

    /**
     * Describe a table
     */
    public static function describe($table)
    {
        $result = [];
        foreach (Db::returnAll('DESCRIBE ' . $table) as $item) {
            $result[$item['Field']] = $item;
        }
        return $result;
    }

    /**
     * Check if a table exists
     */
    public static function tableExists($table)
    {
        try {
            $description = Db::describe($table);
            if (count($description) == 0) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Prefix a set of tables
     */
    public static function prefixTable($table)
    {
        $result = [];
        foreach (explode(',', $table) as $objectName) {
            array_push($result, ASTERION_DB_PREFIX . trim($objectName));
        }
        return implode(',', $result);
    }

}
