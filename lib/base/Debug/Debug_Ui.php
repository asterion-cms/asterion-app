<?php
/**
 * @class DebugUi
 *
 * This class manages the UI for the Debug objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Debug_Ui
{

    public static function showInformation()
    {
        $debugInitialization = Session::get('debugInitialization');
        $debugContent = Session::get('debugContent');
        $debugQueries = Session::get('debugQueries');
        $debugQueries = (is_array($debugQueries)) ? $debugQueries : [];
        $debugQueriesHtml = '';
        $debugContentMicrotimeStart = (isset($debugContent['microtime_start'])) ? $debugContent['microtime_start'] : 0;
        $debugContentMicrotime = (isset($debugContent['microtime'])) ? $debugContent['microtime'] : 0;
        $debugInitializationMicrotime = (isset($debugInitialization['microtime'])) ? $debugInitialization['microtime'] : 0;
        $totalTime = $debugContentMicrotime + $debugInitializationMicrotime;
        $totalTimeQueries = 0;
        $index = 0;
        foreach (array_reverse($debugQueries) as $query) {
            $backtraceHtml = '';
            foreach ($query['backtrace'] as $backtrace) {
                $backtraceHtml .= '<div class="debug_query_backtrace_item">' . $backtrace['file'] . ' - ' . $backtrace['line'] . ' - ' . $backtrace['function'] . '</div>';
            }
            $totalTimeQueries += $query['microtime'];
            $debugQueriesHtml .= '
                <div class="debug_query ' . (($debugContentMicrotimeStart < $query['microtime_start']) ? 'debug_query_content' : '') . '">
                    <div class="debug_query_top">
                        <div class="debug_query_index">' . $index . '</div>
                        <div class="debug_query_raw">
                            ' . $query['query'] . '
                            ' . ((count($query['values']) > 0) ? json_encode($query['values']) : '') . '
                        </div>
                        <div class="debug_query_microtime">' . Text::formatMicrotime($query['microtime']) . '</div>
                    </div>
                    <div class="debug_query_bottom">
                        <div class="debug_query_options">
                            <div class="debug_query_option_backtrace">View backtrace</div>
                        </div>
                        <div class="debug_query_backtrace" style="display: none;">' . $backtraceHtml . '</div>
                    </div>
                </div>';
            $index++;
        }
        Debug::saveSession(memory_get_peak_usage(), $totalTime, $totalTimeQueries);
        $debugSession = Debug::getSessionAverages();
        return '
            <style>' . file_get_contents(ASTERION_APP_FILE . 'visual/css/stylesheets/debug.css') . '</style>
            <div class="debug">
                <div class="debug_menu" id="debug_menu">
                    <div class="debug_menu_button">&lt;/&gt;</div>
                </div>
                <div class="debug_wrapper" id="debug_wrapper">
                    <div class="debug_information">
                        <div class="debug_information_title">Actual</div>
                        <div class="debug_information_item"><strong>Peak memory :</strong> ' . Text::formatBytes(memory_get_peak_usage()) . '</div>
                        <div class="debug_information_item"><strong>Queries :</strong> ' . $index . ' - ' . Text::formatMicrotime($totalTimeQueries) . '</div>
                        <div class="debug_information_item"><strong>Total time :</strong> ' . Text::formatMicrotime($totalTime) . '</div>
                    </div>
                    <div class="debug_information debug_information_average">
                        <div class="debug_information_title">Average</div>
                        <div class="debug_information_item"><strong>Peak memory :</strong> ' . Text::formatBytes($debugSession['memory']) . '</div>
                        <div class="debug_information_item"><strong>Queries :</strong> ' . $index . ' - ' . Text::formatMicrotime($debugSession['total_time_queries']) . '</div>
                        <div class="debug_information_item"><strong>Total time :</strong> ' . Text::formatMicrotime($debugSession['total_time']) . '</div>
                    </div>
                    <div class="debug_queries">
                        ' . $debugQueriesHtml . '
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                var button = document.querySelector(\'#debug_menu\');
                var menu = document.querySelector(\'#debug_wrapper\');
                menu.style.display = "none";
                button.addEventListener(\'click\', function (event) {
                    if (menu.style.display == "") {
                        menu.style.display = "none";
                    } else {
                        menu.style.display = "";
                    }
                });
                var backtraceButtons = document.querySelectorAll(\'.debug_query_option_backtrace\');
                backtraceButtons.forEach(function (backtraceButton) {
                    backtraceButton.addEventListener(\'click\', function (event) {
                        var backtrace = this.closest(\'.debug_query\').querySelector(\'.debug_query_backtrace\');
                        if (backtrace.style.display == "") {
                            backtrace.style.display = "none";
                        } else {
                            backtrace.style.display = "";
                        }
                    });
                });
            </script>';
    }

}
