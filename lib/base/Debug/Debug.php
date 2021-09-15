<?php
/**
 * @class Db
 *
 * This class has all the static methods used when debbuging the application.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Debug
{

    /**
     * Initialize the session.
     */
    public static function intializeSession()
    {
        Session::delete('debugCurrentQuery');
        Session::delete('debugQueries');
        Session::delete('debugCurrentMainFunction');
        Session::delete('debugMainFunctions');
    }

    /**
     * Start recording a query.
     */
    public static function startRecordingQuery($query, $values = [])
    {
        if (ASTERION_DEBUG) {
            $debugCurrentQuery = intval(Session::get('debugCurrentQuery'));
            $debugQueries = Session::get('debugQueries');
            $debugQueries = (is_array($debugQueries)) ? $debugQueries : [];
            $debugQueries[$debugCurrentQuery] = [
                'query' => $query,
                'values' => $values,
                'microtime_start' => microtime(true),
                'microtime' => microtime(true),
            ];
            Session::set('debugCurrentQuery', $debugCurrentQuery);
            Session::set('debugQueries', $debugQueries);
        }
    }

    /**
     * Stop recording a query.
     */
    public static function stopRecordingQuery()
    {
        if (ASTERION_DEBUG) {
            $debugCurrentQuery = intval(Session::get('debugCurrentQuery'));
            $debugQueries = Session::get('debugQueries');
            if (isset($debugQueries[$debugCurrentQuery])) {
                $debugQueries[$debugCurrentQuery]['microtime'] = microtime(true) - $debugQueries[$debugCurrentQuery]['microtime'];
            }
            Session::set('debugCurrentQuery', $debugCurrentQuery + 1);
            Session::set('debugQueries', $debugQueries);
        }
    }

    /**
     * Start recording a function.
     */
    public static function startRecordingMainFunction($function)
    {
        if (ASTERION_DEBUG) {
            Session::set('debug' . $function, ['microtime_start' => microtime(true), 'microtime' => microtime(true)]);
        }
    }

    /**
     * Stop recording a function.
     */
    public static function stopRecordingMainFunction($function)
    {
        if (ASTERION_DEBUG) {
            $debug = Session::get('debug' . $function);
            $debug = (is_array($debug)) ? $debug : [];
            $debug['microtime'] = microtime(true) - $debug['microtime'];
            Session::set('debug' . $function, $debug);
        }
    }

    /**
     * Save the results of a full session.
     */
    public static function saveSession($memory, $totalTime, $totalTimeQueries)
    {
        $debugSession = Session::get('debugSession');
        $debugSession = (is_array($debugSession)) ? $debugSession : [];
        $debugSession[] = [
            'memory' => $memory,
            'total_time' => $totalTime,
            'total_time_queries' => $totalTimeQueries,
        ];
        Session::set('debugSession', $debugSession);
    }

    /**
     * Get the session average information.
     */
    public static function getSessionAverages()
    {
        $debugSession = Session::get('debugSession');
        $debugSession = (is_array($debugSession)) ? $debugSession : [];
        $memorySum = 0;
        $totalTimeSum = 0;
        $totalTimeQueriesSum = 0;
        $totalSessions = count($debugSession);
        foreach ($debugSession as $debugSessionItem) {
            $memorySum += (isset($debugSessionItem['memory'])) ? floatval($debugSessionItem['memory']) : 0;
            $totalTimeSum += (isset($debugSessionItem['total_time'])) ? floatval($debugSessionItem['total_time']) : 0;
            $totalTimeQueriesSum += (isset($debugSessionItem['total_time_queries'])) ? floatval($debugSessionItem['total_time_queries']) : 0;
        }
        return [
            'memory' => $memorySum / $totalSessions,
            'total_time' => $totalTimeSum / $totalSessions,
            'total_time_queries' => $totalTimeQueriesSum / $totalSessions,
        ];
    }

}
