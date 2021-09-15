<?php
/**
 * @class Translation
 *
 * This class contains all the translations of the phrases and words in Asterion.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Translation extends Db_Object
{

    /**
     * Get the translation of a phrase or a word using its code.
     */
    public static function translate($code)
    {
        return (isset($GLOBALS['translations'][$code]) && $GLOBALS['translations'][$code] != '') ? $GLOBALS['translations'][$code] : $code;
    }

    /**
     * Load the translations for a specific language.
     */
    public static function load($idLanguage)
    {
        $query = 'SELECT code, translation_' . $idLanguage . ' as translation FROM ' . Db::prefixTable('translation');
        $items = [];
        foreach (Db::returnAll($query) as $item) {
            $items[$item['code']] = $item['translation'];
        }
        return $items;
    }

    /**
     * Get an array of the translations by code.
     */
    public static function getTranslationsCodes()
    {
        $items = [];
        foreach ((new Translation)->readList() as $translation) {
            $items[$translation->get('code')] = $translation;
        }
        return $items;
    }

    /**
     * Check that all the files have their translations correctly.
     */
    public static function missingTranslationsKeys($directories = 'all')
    {
        $translationsKeys = Translation::translationsKeys($directories);
        $existingTranslationsKeys = array_keys($GLOBALS['translations']);
        return array_diff($translationsKeys, $existingTranslationsKeys);
    }

    /**
     * Get all the translations codes.
     */
    public static function translationsKeys($directories = 'all')
    {
        $files = [];
        $files = ($directories == 'all' || $directories == 'app') ? array_merge($files, File::scanDirectoryExtension(ASTERION_APP_FILE . 'lib', 'php')) : $files;
        $files = ($directories == 'all' || $directories == 'base') ? array_merge($files, File::scanDirectoryExtension(ASTERION_BASE_FILE . 'lib', 'php')) : $files;
        $translationsKeys = [];
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $outputTranslations = [];
            preg_match_all('/__\(\'(.*?)\'\)/', $content, $outputTranslations);
            if (isset($outputTranslations[1]) && is_array($outputTranslations[1])) {
                $translationsKeys = array_merge($translationsKeys, $outputTranslations[1]);
            }
        }
        return array_unique($translationsKeys);
    }

    /**
     * Reset the translations used in the administration area.
     **/
    public static function resetAdminTranslations($language)
    {
        $urlApi = ASTERION_DATA_FILE . 'admin_translations/' . $language . '.json';
        $apiItems = @json_decode(file_get_contents($urlApi), true);
        $response = ['status' => StatusCode::NOK, 'message_error' => __('translations_admin_reset_error_message')];
        if (is_array($apiItems)) {
            $translations = Translation::getTranslationsCodes();
            $statistics = ['translations_updated' => 0, 'translations_created' => 0];
            foreach ($apiItems as $key => $apiItem) {
                if (isset($translations[$key])) {
                    $translation = $translations[$key];
                    $translation->set('translation_' . $language, $apiItem);
                    $statistics['translations_updated']++;
                } else {
                    $translation = new Translation(['code' => $key, 'translation_' . $language => $apiItem]);
                    $statistics['translations_created']++;
                }
                $translation->persist();
            }
            $response = ['status' => StatusCode::OK, 'statistics' => $statistics];
        }
        return $response;
    }

}
