<?php
/**
 * @class Session
 *
 * This is a helper class to manage the session in an easier way.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Session
{

    /**
     * Convert values to session-safe values.
     */
    private static function sanitize($value)
    {
        if (is_null($value) || is_scalar($value)) {
            return $value;
        }
        if ($value instanceof SimpleXMLElement) {
            return (string) $value;
        }
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $item) {
                $sanitized[$key] = self::sanitize($item);
            }
            return $sanitized;
        }
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
            return '[object ' . get_class($value) . ']';
        }
        return '[resource]';
    }

    /**
     * Get a session element.
     */
    public static function get($name)
    {
        return (isset($_SESSION[ASTERION_SESSION_NAME][$name])) ? $_SESSION[ASTERION_SESSION_NAME][$name] : '';
    }

    /**
     * Get the session login information.
     */
    public static function getLogin($name)
    {
        return (isset($_SESSION[ASTERION_SESSION_NAME]['info'][$name])) ? $_SESSION[ASTERION_SESSION_NAME]['info'][$name] : '';
    }

    /**
     * Set a session element.
     */
    public static function set($name, $value)
    {
        $_SESSION[ASTERION_SESSION_NAME][$name] = self::sanitize($value);
    }

    /**
     * Delete a session element.
     */
    public static function delete($name)
    {
        if (isset($_SESSION[ASTERION_SESSION_NAME][$name])) {
            $_SESSION[ASTERION_SESSION_NAME][$name] = '';
            unset($_SESSION[ASTERION_SESSION_NAME][$name]);
        }
    }

    /**
     * Get an info flash message.
     */
    public static function getFlashInfo()
    {
        $message = Session::get('flash_info');
        Session::delete('flash_info');
        return $message;
    }

    /**
     * Get an alert flash message.
     */
    public static function getFlashAlert()
    {
        $message = Session::get('flash_alert');
        Session::delete('flash_alert');
        return $message;
    }

    /**
     * Get an error flash message.
     */
    public static function getFlashError()
    {
        $message = Session::get('flash_error');
        Session::delete('flash_error');
        return $message;
    }

    /**
     * Create an info flash message.
     */
    public static function flashInfo($message)
    {
        Session::set('flash_info', $message);
    }

    /**
     * Create an alert flash message.
     */
    public static function flashAlert($message)
    {
        Session::set('flash_alert', $message);
    }

    /**
     * Create an error flash message.
     */
    public static function flashError($message)
    {
        Session::set('flash_error', $message);
    }

}
