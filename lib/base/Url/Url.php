<?php
/**
 * @class Url
 *
 * This is a helper class to manage URLs.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Url
{

    /**
     * Get a parameter from the URL.
     */
    public static function get($parameter)
    {
        return (isset($_GET[$parameter])) ? $_GET[$parameter] : '';
    }

    /**
     * Format a URL, adding the proper http, https or www if it's missing.
     */
    public static function format($url)
    {
        $url = strtolower($url);
        $url = str_replace(' ', '', $url);
        $url = trim($url);
        if ($url != '' && strpos($url, '.') !== false) {
            if (substr($url, 0, 8) == 'https://' || substr($url, 0, 7) == 'http://') {
                return $url;
            } else {
                if (substr($url, 0, 3) == 'www') {
                    return 'http://' . $url;
                } else {
                    return 'http://www.' . $url;
                }
            }
        }
    }

    /**
     * Return the current URL.
     */
    public static function currentUrl()
    {
        $url = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$url .= "s";}
        $url .= "://";
        if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
            $url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $url;
    }

    /**
     * Check if the url is a part of the administration area.
     */
    public static function isAdministration()
    {
        $url = (isset($_GET['url'])) ? $_GET['url'] : '';
        $info = explode('/', $url);
        return (isset($info[0]) && $info[0] == ASTERION_ADMIN_URL_STRING);
    }

    /**
     * Initialize the url when using multiple language.
     */
    public static function initLanguage()
    {
        $url = (isset($_GET['url'])) ? $_GET['url'] : '';
        $info = explode('/', $url);
        if (isset($info[0]) && $info[0] == ASTERION_ADMIN_URL_STRING) {
            //If the url points to the admin area
            $_GET['mode'] = 'admin';
            $_GET['language'] = (isset($info[1])) ? $info[1] : '';
            $_GET['type'] = (isset($info[2]) && $info[2] != '') ? $info[2] : 'navigation_admin';
            $_GET['action'] = (isset($info[3])) ? $info[3] : 'intro';
            $_GET['id'] = (isset($info[4])) ? $info[4] : '';
            $_GET['extraId'] = (isset($info[5])) ? $info[5] : '';
            $_GET['addId'] = (isset($info[6])) ? $info[6] : '';
        } else {
            //If the url points to the public area
            $_GET['language'] = (isset($info[0])) ? $info[0] : '';
            $_GET['type'] = 'navigation';
            $_GET['action'] = (isset($info[1]) && $info[1] != '') ? $info[1] : 'intro';
            $_GET['id'] = (isset($info[2])) ? $info[2] : '';
            $_GET['extraId'] = (isset($info[3])) ? $info[3] : '';
            $_GET['addId'] = (isset($info[4])) ? $info[4] : '';
            //Check if there are routes
            $routes = Url::routerControllers();
            if (isset($routes[$_GET['action']])) {
                $_GET['type'] = $routes[$_GET['action']];
                $_GET['action'] = (isset($info[2]) && $info[2] != '') ? $info[2] : 'intro';
                $_GET['id'] = (isset($info[3])) ? $info[3] : '';
                $_GET['extraId'] = (isset($info[4])) ? $info[3] : '';
                $_GET['addId'] = (isset($info[5])) ? $info[5] : '';
            }
        }
    }

    /**
     * Initialize the url when using only one language.
     */
    public static function init()
    {
        $url = (isset($_GET['url'])) ? $_GET['url'] : '';
        $info = explode('/', $url);
        $languages = Language::languages();
        if (count($languages) > 1 && ((isset($info[0]) && isset($languages[$info[0]])) || (isset($info[1]) && isset($languages[$info[1]])))) {
            return Url::initLanguage();
        }
        if (isset($info[0]) && $info[0] == ASTERION_ADMIN_URL_STRING) {
            //If the url points to the admin area
            $_GET['mode'] = 'admin';
            $_GET['type'] = (isset($info[1]) && $info[1] != '') ? $info[1] : 'navigation_admin';
            $_GET['action'] = (isset($info[2]) && $info[2] != '') ? $info[2] : 'intro';
            $_GET['id'] = (isset($info[3])) ? $info[3] : '';
            $_GET['extraId'] = (isset($info[4])) ? $info[4] : '';
            $_GET['addId'] = (isset($info[5])) ? $info[5] : '';
        } else {
            //If the url points to the public area
            $_GET['type'] = 'navigation';
            $_GET['action'] = (isset($info[0]) && $info[0] != '') ? $info[0] : 'intro';
            $_GET['id'] = (isset($info[1])) ? $info[1] : '';
            $_GET['extraId'] = (isset($info[2])) ? $info[2] : '';
            $_GET['addId'] = (isset($info[3])) ? $info[3] : '';
            //Check if there are routes
            $routes = Url::routerControllers();
            if (isset($routes[$_GET['action']])) {
                $_GET['type'] = $routes[$_GET['action']];
                $_GET['action'] = (isset($info[1]) && $info[1] != '') ? $info[1] : 'intro';
                $_GET['id'] = (isset($info[2])) ? $info[2] : '';
                $_GET['extraId'] = (isset($info[3])) ? $info[3] : '';
                $_GET['addId'] = (isset($info[4])) ? $info[4] : '';
            }
        }
    }

    /**
     * Create an URL using the language code.
     */
    public static function urlLanguageHome($newLanguage)
    {
        return ASTERION_LOCAL_URL . $newLanguage;
    }

    /**
     * Create an URL using the language code.
     */
    public static function urlLanguage($newLanguage)
    {
        $url = '';
        $url .= (isset($_GET['mode']) && $_GET['mode'] == 'admin') ? ASTERION_ADMIN_URL_STRING . '/' : '';
        $url .= $newLanguage . '/';
        $url .= (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] != 'navigation') ? $_GET['type'] . '/' : '';
        $url .= (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] . '/' : '';
        $url .= (isset($_GET['id']) && $_GET['id'] != '') ? $_GET['id'] . '/' : '';
        $url .= (isset($_GET['extraId']) && $_GET['extraId'] != '') ? $_GET['extraId'] . '/' : '';
        $url .= (isset($_GET['addId']) && $_GET['addId'] != '') ? $_GET['addId'] . '/' : '';
        return ASTERION_LOCAL_URL . $url;
    }

    /**
     * Create an URL using the actual information.
     */
    public static function urlActual()
    {
        $url = '';
        $url .= (isset($_GET['mode']) && $_GET['mode'] == 'admin') ? ASTERION_ADMIN_URL_STRING . '/' : '';
        $url .= (isset($_GET['language']) && count(Language::languages()) > 0) ? Language::active() . '/' : '';
        $url .= (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] != 'navigation') ? $_GET['type'] . '/' : '';
        $url .= (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] . '/' : '';
        $url .= (isset($_GET['id']) && $_GET['id'] != '') ? $_GET['id'] . '/' : '';
        $url .= (isset($_GET['extraId']) && $_GET['extraId'] != '') ? $_GET['extraId'] . '/' : '';
        $url .= (isset($_GET['addId']) && $_GET['addId'] != '') ? $_GET['addId'] . '/' : '';
        return ASTERION_LOCAL_URL . $url;
    }

    /**
     * Create an URL.
     */
    public static function urlPage($page)
    {
        $url = (isset($_GET['url'])) ? $_GET['url'] : '';
        $pageUrl = (__('page_url_string') != 'page_url_string') ? __('page_url_string') : ASTERION_PAGER_URL_STRING;
        return ($page>1) ? ASTERION_LOCAL_URL . $url . '?' . $pageUrl . '=' . $page : ASTERION_LOCAL_URL . $url;
    }

    /**
     * Format an URL using the language code.
     */
    public static function getUrlLanguage($url = '', $admin = false)
    {
        if ($admin) {
            return ASTERION_LOCAL_URL . ASTERION_ADMIN_URL_STRING . '/' . Language::active() . '/' . $url;
        } else {
            return ASTERION_LOCAL_URL . Language::active() . '/' . $url;
        }
    }

    /**
     * Format an URL.
     */
    public static function getUrl($url = '', $admin = false, $language = true)
    {
        if ($language && count(Language::languages()) > 1) {
            return Url::getUrlLanguage($url, $admin);
        }
        if ($admin) {
            return ASTERION_LOCAL_URL . ASTERION_ADMIN_URL_STRING . '/' . $url;
        } else {
            return ASTERION_LOCAL_URL . $url;
        }
    }

    /**
     * Format an array of URLs.
     */
    public static function getAllUrls($suffixes)
    {
        $urls = [];
        foreach (Language::languages() as $id => $language) {
            $suffix = (isset($suffixes[$id])) ? $suffixes[$id] : '';
            $urls[$id] = url($id . '/' . $suffix, false, false);
        }
        return $urls;
    }

    /**
     * Get the contents from an URL address using CURL.
     */
    public static function getContents($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /** 
     * Get header Authorization
     * */
    public static function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * Get access token from header
     * */
    public static function getBearerToken()
    {
        $headers = Url::getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Get the array for the route controllers
     */
    public static function routerControllers()
    {
        return (defined('ASTERION_ROUTER_CONTROLLERS')) ? unserialize(ASTERION_ROUTER_CONTROLLERS) : [];
    }

}
