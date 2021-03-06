<?php
/**
 * @class File
 *
 * This is a helper class to deal with the files.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class File
{

    /**
     * Upload a file using the field name.
     */
    public static function uploadUrl($dataFile, $objectName, $fileName)
    {
        if ($dataFile != '' && $objectName != '' && $fileName != '') {
            $mainFolder = ASTERION_STOCK_FILE . $objectName . 'Files';
            File::createDirectory($mainFolder);
            $fileDestination = $mainFolder . '/' . $fileName;
            $dataFile = str_replace(ASTERION_BASE_URL, ASTERION_BASE_FILE, $dataFile);
            if ($dataFile != '' && copy($dataFile, $fileDestination)) {
                @chmod($fileDestination, 0777);
                return true;
            }
        }
        return false;
    }

    /**
     * Upload a file using the field name.
     */
    public static function upload($objectName, $name, $fileName = '')
    {
        if (isset($_FILES[$name]) && $_FILES[$name]['tmp_name'] != '') {
            $mainFolder = ASTERION_STOCK_FILE . $objectName . 'Files';
            File::createDirectory($mainFolder);
            $fileName = ($fileName != '') ? $fileName : Text::simpleUrlFile($_FILES[$name]['name']);
            $fileOrigin = $_FILES[$name]['tmp_name'];
            $fileDestination = $mainFolder . '/' . $fileName;
            return move_uploaded_file($fileOrigin, $fileDestination);
        }
        return false;
    }

    /**
     * Upload a temporary image to the server.
     */
    public static function uploadTempImage($values)
    {
        $response = ['status' => StatusCode::NOK];
        if (isset($values['filename']) && isset($values['file'])) {
            $directory = ASTERION_STOCK_FILE . 'temp/';
            File::createDirectory($directory);
            $newFilename = $directory . substr(md5(rand()), 0, 5) . '_' . $values['filename'];
            $imageInfo = @getimagesize($values['file']);
            if (isset($imageInfo['mime']) && ($imageInfo['mime'] == 'image/png' || $imageInfo['mime'] == 'image/jpg' || $imageInfo['mime'] == 'image/jpeg')) {
                if (copy($values['file'], $newFilename)) {
                    $response = ['status' => StatusCode::OK, 'file' => str_replace(ASTERION_BASE_FILE, ASTERION_BASE_URL, $newFilename), 'filename' => $values['filename']];
                }
            }
        }
        return $response;
    }

    /**
     * Upload a temporary file to the server.
     */
    public static function uploadTempFile($values)
    {
        $response = ['status' => StatusCode::NOK];
        if (isset($values['filename']) && isset($values['file'])) {
            $directory = ASTERION_STOCK_FILE . 'temp/';
            File::createDirectory($directory);
            $newFilename = $directory . substr(md5(rand()), 0, 5) . '_' . $values['filename'];
            if (copy($values['file'], $newFilename)) {
                @chmod($newFilename, 0777);
                $response = ['status' => StatusCode::OK, 'file' => str_replace(ASTERION_BASE_FILE, ASTERION_BASE_URL, $newFilename), 'filename' => $values['filename']];
            }
        }
        return $response;
    }

    /**
     * Save content to a file.
     */
    public static function saveFile($filename, $content)
    {
        @touch($filename);
        if (file_exists($filename)) {
            $fhandle = fopen($filename, "w");
            fwrite($fhandle, $content);
            fclose($fhandle);
        }
    }

    /**
     * Copy an entire directory and its files.
     */
    public static function copyDirectory($source, $destination, $permissions = 0755)
    {
        if (is_link($source)) {
            return symlink(readlink($source), $destination);
        }
        if (is_file($source)) {
            return copy($source, $destination);
        }
        if (!is_dir($destination)) {
            mkdir($destination, $permissions);
        }
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            File::copyDirectory("$source/$entry", "$destination/$entry");
        }
        $dir->close();
        return true;
    }

    /**
     * Change the permissions of an entire directory an its files.
     */
    public static function chmodDirectory($path, $fileMode, $dirMode)
    {
        if (is_dir($path)) {
            if (!chmod($path, $dirMode)) {
                $dirMode_str = decoct($dirMode);
                return;
            }
            $directoryHead = opendir($path);
            while (($file = readdir($directoryHead)) !== false) {
                if ($file != '.' && $file != '..') {
                    $fullPath = $path . '/' . $file;
                    File::chmodDirectory($fullPath, $fileMode, $dirMode);
                }
            }
            closedir($directoryHead);
        } else {
            if (is_link($path)) {
                return;
            }
            if (!chmod($path, $fileMode)) {
                $fileMode_str = decoct($fileMode);
                return;
            }
        }
    }

    /**
     * Change headers and force a file download.
     */
    public static function download($file, $options = [])
    {
        $content = (isset($options['content'])) ? $options['content'] : '';
        $contentType = (isset($options['contentType'])) ? $options['contentType'] : '';
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . File::basename($file));
        header('Content-Type: ' . $contentType);
        header('Content-Transfer-Encoding: binary');
        if (is_string($content) != '') {
            echo $content;
        } else {
            readfile($file);
        }
    }

    /**
     * Get the basename of a file.
     */
    public static function basename($file)
    {
        $info = pathinfo($file);
        return (isset($info['basename'])) ? $info['basename'] : '';
    }

    /**
     * Get the basename of a file.
     */
    public static function filename($file)
    {
        $info = pathinfo($file);
        return (isset($info['filename'])) ? $info['filename'] : '';
    }

    /**
     * Create a directory in the server.
     */
    public static function createDirectory($dirname, $exception = false)
    {
        if (!is_dir($dirname)) {
            if (!@mkdir($dirname, 0755, true)) {
                if (ASTERION_DEBUG && $exception) {
                    throw new Exception('Could not create folder ' . $dirname);
                }
            }
        }
    }

    /**
     * Delete a directory and all files and subdirectories in it.
     */
    public static function deleteDirectory($dirname)
    {
        if (is_dir($dirname)) {
            $handle = opendir($dirname);
            if (!$handle) {
                return false;
            }
            while ($file = readdir($handle)) {
                if ($file != "." && $file != "..") {
                    if (!is_dir($dirname . "/" . $file)) {
                        unlink($dirname . "/" . $file);
                    } else {
                        File::deleteDirectory($dirname . '/' . $file);
                    }
                }
            }
            closedir($handle);
            rmdir($dirname);
        }
        return true;
    }

    /**
     * Get the extension of an URL.
     */
    public static function urlExtension($url)
    {
        if (url_exists($url)) {
            $urlComponents = parse_url($url);
            $urlPath = $urlComponents['path'];
            return pathinfo($urlPath, PATHINFO_EXTENSION);
        }
    }

    /**
     * Get the extension of a file.
     */
    public static function fileExtension($filename)
    {
        $info = explode('.', $filename);
        return strtolower($info[count($info) - 1]);
    }

    /**
     * Scan the website and return all the active objects.
     */
    public static function scanDirectoryObjects()
    {
        $objectNames = [];
        $objectNames = array_merge($objectNames, File::scanDirectoryObjectsApp());
        $objectNames = array_merge($objectNames, File::scanDirectoryObjectsBase());
        return $objectNames;
    }

    /**
     * Scan the website and return all the active objects from the application.
     */
    public static function scanDirectoryObjectsApp()
    {
        return File::scanDirectoryObjectsGeneric(ASTERION_APP_FILE . 'lib');
    }

    /**
     * Scan the website and return all the active objects from the public website.
     */
    public static function scanDirectoryObjectsBase()
    {
        return File::scanDirectoryObjectsGeneric(ASTERION_BASE_FILE . 'lib');
    }

    /**
     * Scan a directory and return all the active objects.
     */
    public static function scanDirectoryObjectsGeneric($directory)
    {
        $objectNames = File::scanDirectoryExtension($directory, 'xml');
        foreach ($objectNames as $key => $objectName) {
            $objectNames[$key] = basename($objectName, '.xml');
        }
        sort($objectNames);
        return $objectNames;
    }

    /**
     * Scan the website and return all the the files with some extension.
     */
    public static function scanDirectoryExtension($directory, $extension)
    {
        $recursiveDirectory = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new RecursiveIteratorIterator($recursiveDirectory);
        $files = [];
        foreach ($recursiveIterator as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == $extension) {
                $files[] = (string) $file;
            }
        }
        return $files;
    }

    public static function textFileToArray($filename)
    {
        $array = [];
        $fopen = @fopen($filename, 'r');
        if ($fopen) {
            $array = explode("\n", fread($fopen, filesize($filename)));
        }
        return $array;
    }

}
