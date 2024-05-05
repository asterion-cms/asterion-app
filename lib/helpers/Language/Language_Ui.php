<?php
/**
 * @class LanguageUi
 *
 * This class manages the UI for the Language objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Language_Ui extends Ui
{

    /**
     * @cache
     * Render the set of available languages.
     */
    public static function showLanguages($simple = false, $links = [])
    {
        $languageActive = Language::active();
        $languages = Language::languages();
        if (count($languages) > 1) {
            $html = '';
            foreach ($languages as $language) {
                $html .= '<div class="language language_' . $language['id'] . '">';
                $name = ($simple) ? $language['id'] : '<em class="language_complete">' . $language['local_names'] . '</em><em class="language_id">' . $language['id'] . '</em>';
                if ($language['id'] == $languageActive) {
                    $html .= '<span title="' . $language['local_names'] . '">' . $name . '</span> ';
                } else {
                    $link = (isset($links[$language['id']])) ? $links[$language['id']] : Url::urlLanguageHome($language['id']);
                    $html .= '<a href="' . $link . '" title="' . $language['local_names'] . '">' . $name . '</a> ';
                }
                $html .= '</div>';
            }
            return '<div class="languages">' . $html . '</div>';
        }
    }

    /**
     * Render the set of available languages in a simple way.
     */
    public static function showLanguagesSimple()
    {
        return Language::showLanguages(true);
    }

    /**
     * Render the alternative metatag for the homepage.
     */
    public static function showHrefLanguages($suffixArray = [])
    {
        $html = '';
        foreach (Language::languages() as $language) {
            $suffix = (isset($suffixArray[$language['id']])) ? $suffixArray[$language['id']] : '';
            $html .= '<link rel="alternate" href="' . url($language['id'] . '/' . $suffix, false, false) . '" hreflang="' . $language['id'] . '" />';
        }
        return $html;
    }

}
