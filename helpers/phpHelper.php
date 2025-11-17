<?php
/**
 * @file
 *
 * The phpHelper.php includes several functions that are not existing
 * in the basic PHP version. It also includes some functions that can be
 * used as shortcuts for common actions.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion
 * @version 3.0.1
 */

/**
 * Function to check if the "get_called_class" exists
 * and create it if it does not.
 */
if (!function_exists('get_called_class')) {
    function get_called_class($flag = false, $indexFlag = 1)
    {
        if (!$flag) {
            $flag = debug_backtrace();
        }
        if (!isset($flag[$indexFlag]) && ASTERION_DEBUG) {
            throw new Exception("Cannot find called class. Stack level too deep.");
        }
        if (!isset($flag[$indexFlag]['type']) && ASTERION_DEBUG) {
            throw new Exception('type not set');
        } else {
            switch ($flag[$indexFlag]['type']) {
                case '::':
                    $indexFlagines = file($flag[$indexFlag]['file']);
                    $i = 0;
                    $callerLine = '';
                    do {
                        $i++;
                        $callerLine = $indexFlagines[$flag[$indexFlag]['line'] - $i] . $callerLine;
                    } while (strpos($callerLine, $flag[$indexFlag]['function']) === false);
                    preg_match('/([a-zA-Z0-9\_]+)::' . $flag[$indexFlag]['function'] . '/',
                        $callerLine,
                        $matches);
                    if (!isset($matches[1]) && ASTERION_DEBUG) {
                        throw new Exception("Could not find caller class: originating method call is obscured.");
                    }
                    switch ($matches[1]) {
                        case 'self':
                        case 'parent':
                            return get_called_class($flag, $indexFlag + 1);
                        default:
                            return $matches[1];
                    }
                case '->':switch ($flag[$indexFlag]['function']) {
                        case '__get':
                            if (!is_object($flag[$indexFlag]['object'])) {
                                throw new Exception("Edge case fail. __get called on non object.");
                            }

                            return get_class($flag[$indexFlag]['object']);
                        default:return $flag[$indexFlag]['class'];
                    }
                default:
                    if (ASTERION_DEBUG) {
                        throw new Exception("Unknown backtrace method type.");
                    }
                    break;
            }
        }

    }
}

/**
 * Function to check if the "random_bytes" exists
 * and create it if it does not.
 */
if (!function_exists('random_bytes')) {
    function random_bytes($length = 6)
    {
        $characters = '0123456789';
        $characters_length = strlen($characters);
        $output = '';
        for ($i = 0; $i < $length; $i++) {
            $output .= $characters[rand(0, $characters_length - 1)];
        }
        return $output;
    }
}

/**
 * Function to fill an array with keys.
 */
function array_fillkeys($target, $value = '')
{
    $filledArray = array();
    foreach ($target as $key => $val) {
        $filledArray[$val] = is_array($value) ? $value[$key] : $value;
    }
    return $filledArray;
}

/**
 * Function to check if a URL exists.
 */
function url_exists($url)
{
    //Check if a URL exists
    return (!$fp = curl_init($url)) ? false : true;
}

/**
 * Function to remove an entire directory on the server.
 */
function rrmdir($dir)
{
    //Remove an entire directoy
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file)) {
            rrmdir($file);
        } else {
            @unlink($file);
        }
    }
    @rmdir($dir);
}

/**
 * Function to translate using the translation "code" of the Translation object.
 */
function __($code, $parameters = [])
{
    return Translation::translate($code, $parameters);
}

/**
 * Function to build an URL using the correct path to the website.
 *
 * $url: The single path for the URL
 * $admin: Boolean to determine if the URL is for the BackEnd
 *
 * Example:
 * echo url('about-us');
 * > http://localhost/asterion/about-us
 */
function url($url = '', $admin = false, $language = true)
{
    if (!is_array($url)) {
        return Url::getUrl($url, $admin, $language);
    } else {
        return Url::getUrl($url[Language::active()], $admin, $language);
    }
}

/**
 * Function to do a recursive glob search
 */
function rglob($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $directory) {
        $files = array_merge($files, rglob($directory . '/' . basename($pattern), $flags));
    }
    return $files;
}

/**
 * Check if the user is using a mobile phone
 */
function isMobile()
{
    $useragent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
    return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)));

}

/**
 * Convert camelCase to snake_case
 */
function camelToSnake($input)
{
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
        $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
}

/**
 * Convert snake_case to camelCase
 */
function snakeToCamel($input, $separator = '_')
{
    return ucwords(str_replace($separator, '', ucwords($input, $separator)));
}

/**
 * Use multiple delimiters in the explode function
 */
function multiexplode($delimiters, $string)
{
    return explode($delimiters[0], str_replace($delimiters, $delimiters[0], $string));
}

/**
 * String replace on nth ocurrence
 */
function str_replace_n($search, $replace, $subject, $nth)
{
    $found = preg_match_all('/' . preg_quote($search) . '/', $subject, $matches, PREG_OFFSET_CAPTURE);
    if (false !== $found && $found > $nth) {
        return substr_replace($subject, $replace, $matches[0][$nth][1], strlen($search));
    }
    return $subject;
}

/**
 * Function to dump a variable
 */
function dump($variable)
{
    echo '<pre>';
    foreach (func_get_args() as $variable) {
        var_dump($variable);
    }
    echo '<pre>';
}

/**
 * Function to dump a variable and stop the application
 */
function dumpExit($variable)
{
    dump($variable);
    exit();
}

/**
 * Function to dump a variable and stop the application
 */
function dd()
{
    foreach (func_get_args() as $variable) {
        dump($variable);
    }
    exit();
}
