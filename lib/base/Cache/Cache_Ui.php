<?php
/**
 * @class Cache_Ui
 *
 * This class manages the UI for the Cache object.
 * Here we render the template for the administration area.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class Cache_Ui extends Ui
{

    /**
     * Show the intro screen of the cache page.
     */
    public static function intro()
    {
        $info = '';
        $filenames = array_merge(rglob(ASTERION_BASE_FILE . 'lib/*_Ui.php'), rglob(ASTERION_APP_FILE . 'lib/*_Ui.php'));
        sort($filenames);
        foreach ($filenames as $filename) {
            $className = str_replace('.php', '', basename($filename));
            $classObjectName = str_replace('_Ui', '', $className);
            $reflection = new ReflectionClass($className);
            $infoClass = '';
            $classMethods = get_class_methods($className);
            sort($classMethods);
            $directory = ASTERION_BASE_FILE . 'cache/' . $classObjectName;
            $cachedFiles = [];
            if (is_dir($directory)) {
                $directoryFiles = array_filter(scandir($directory), function ($item) {
                    return (strpos($item, '.htm') != false);
                });
                foreach ($directoryFiles as $directoryFile) {
                    $directoryFile = $directory . '/' . $directoryFile;
                    $cachedFiles[$directoryFile] = ['file' => $directoryFile, 'date' => filemtime($directoryFile)];
                }
            }
            foreach ($classMethods as $classMethod) {
                $method = new ReflectionMethod($className, $classMethod);
                $documentation = $reflection->getMethod($classMethod)->getDocComment();
                preg_match_all('#@(.*?)\n#s', $documentation, $annotations);
                if (isset($annotations[0]) && isset($annotations[0][0])) {
                    if (trim($annotations[0][0]) == "@cache") {
                        $fileName = $directory . '/' . $classMethod . '.htm';
                        $staticLabel = ($method->isStatic()) ? ' <span>( ' . __('static_method') . ' )</span>' : '';
                        $cachedFilesHtml = '';
                        if (isset($cachedFiles[$fileName])) {
                            $cachedFilesHtml = '<p><em>' . __('last_cached_date') . ' : ' . Date::timestampText($cachedFiles[$fileName]['date']) . '</em></p>';
                        } else {
                            $fileNameSearch = str_replace('.htm', '', $fileName);
                            foreach ($cachedFiles as $cachedFile=>$cachedFileInfo) {
                                if (strpos($cachedFile, $fileNameSearch) === 0) {
                                    $cachedFilesHtml .= '<p><em>'.File::basename($cachedFile).' - ' . __('last_cached_date') . ' : ' . Date::timestampText($cachedFiles[$cachedFile]['date']) . '</em></p>';
                                }
                            }
                        }
                        $infoClass .= '<p>' . $classMethod . $staticLabel . '</p>' . $cachedFilesHtml;
                    }
                }
                unset($annotations);
            }
            $info .= ($infoClass != '') ? '
                <div class="simple_grid_line">
                    <div class="simple_grid_item simple_grid_item_8">
                        <div class="simple_information">
                            <p><strong>' . $className . '</strong></p>
                            <hr/>
                            ' . $infoClass . '
                        </div>
                    </div>
                    <div class="simple_grid_item simple_grid_item_right simple_grid_item_4">
                        <div class="buttons buttons_small">
                            <a href="' . url('cache/object/' . $classObjectName, true) . '" class="button">' . __('cache') . '</a>
                        </div>
                    </div>
                </div>' : '';
        }
        if ($info == '') {
            return '<div class="message message_error">' . __('no_objects_to_cache') . '</div>';
        } else {
            return '
                <div class="information">' . __('cache_information') . '</div>
                <div class="button_cards">
                    <div class="button_card">
                        <a href="' . url('cache/all', true) . '">
                            <i class="fa fa-sync"></i>
                            <p><strong>' . __('cache_all') . '</strong></p>
                            <p>' . __('cache_all_disclaimer') . '</p>
                        </a>
                    </div>
                </div>
                <h2>' . __('objects_to_cache') . '</h2>
                <div class="simple_grid simple_grid_zebra simple_grid_border">' . $info . '</div>';
        }
    }

}
