<?php
/**
 * @class Cache
 *
 * This is a helper class to manage the backups.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Cache
{

    /**
     * Function to cache one object.
     */
    public static function cacheObject($className)
    {
        try {
            $uiClassName = $className . '_Ui';
            $object = new $className;
            if (method_exists($object, 'readList')) {
                $ui = new $uiClassName($object);
                $reflection = new ReflectionClass($uiClassName);
                $classMethods = get_class_methods($uiClassName);
                $cacheUrl = Parameter::code('cache-url');
                foreach ($classMethods as $classMethod) {
                    $method = new ReflectionMethod($uiClassName, $classMethod);
                    $documentation = $reflection->getMethod($classMethod)->getDocComment();
                    preg_match_all('#@(.*?)\n#s', $documentation, $annotations);
                    if (isset($annotations[0]) && isset($annotations[0][0])) {
                        if (trim($annotations[0][0]) == "@cache") {
                            File::createDirectory(ASTERION_BASE_FILE . 'cache', false);
                            File::createDirectory(ASTERION_BASE_FILE . 'cache/' . $className, false);
                            if (!$method->isStatic()) {
                                $items = $object->readList();
                                foreach ($items as $item) {
                                    $itemUi = new $uiClassName($item);
                                    $file = ASTERION_BASE_FILE . 'cache/' . $className . '/' . $classMethod . '_' . $item->id() . '.htm';
                                    $content = $itemUi->$classMethod();
                                    $content = ($cacheUrl != '') ? str_replace(ASTERION_LOCAL_URL, $cacheUrl, $content) : $content;
                                    $content = ($cacheUrl != '') ? str_replace(urlencode(ASTERION_LOCAL_URL), $cacheUrl, $content) : $content;
                                    File::saveFile($file, $content);
                                }
                            } else {
                                $file = ASTERION_BASE_FILE . 'cache/' . $className . '/' . $classMethod . '.htm';
                                $content = $ui->$classMethod();
                                $content = ($cacheUrl != '') ? str_replace(ASTERION_LOCAL_URL, $cacheUrl, $content) : $content;
                                $content = ($cacheUrl != '') ? str_replace(urlencode(ASTERION_LOCAL_URL), urlencode($cacheUrl), $content) : $content;
                                File::saveFile($file, $content);
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {}
    }

    /**
     * Function to cache all objects.
     */
    public static function cacheAll()
    {
        $filenames = array_merge(rglob(ASTERION_BASE_FILE . 'lib/*_Ui.php'), rglob(ASTERION_APP_FILE . 'lib/*_Ui.php'));
        foreach ($filenames as $filename) {
            $className = str_replace('_Ui.php', '', basename($filename));
            if ($className != 'Navigation' && $className != 'NavigationAdmin' && $className != 'Installation') {
                Cache::cacheObject($className);
            }
        }
    }

    /**
     * Function to show a cached version of a static function.
     */
    public static function show($className, $classMethod)
    {
        $file = ASTERION_BASE_FILE . 'cache/' . $className . '/' . $classMethod . '.htm';
        if (is_file($file)) {
            return file_get_contents($file);
        }
        $uiClassName = $className.'_Ui';
        $itemUi = new $uiClassName(new $className);
        return $itemUi->$classMethod();
    }

}
