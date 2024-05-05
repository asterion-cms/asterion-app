<?php
/**
 * @class Cookie
 *
 * This is a helper class to manage cookies in an easier way.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Cookie
{

    /**
     * Get a cookie value
     */
    public static function get($name)
    {
        return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : '';
    }

    /**
     * Get all cookies
     */
    public static function getAll()
    {
        return (isset($_COOKIE)) ? $_COOKIE : [];
    }

    /**
     * Set a cookie value
     */
    public static function set($name, $value)
    {
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost' && $_SERVER['HTTP_HOST'] != 'localhost:8888') ? $_SERVER['HTTP_HOST'] : false;
        setcookie($name, $value, time() + ASTERION_COOKIE_TIME, '/', $domain, false);
    }

    /**
     * Delete a cookie
     */
    public static function delete($name)
    {
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost' && $_SERVER['HTTP_HOST'] != 'localhost:8888') ? $_SERVER['HTTP_HOST'] : false;
        setcookie($name, '', time() - ASTERION_COOKIE_TIME, '/', $domain, false);
        unset($_COOKIE[$name]);
    }

}
