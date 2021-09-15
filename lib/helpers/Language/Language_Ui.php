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
    public static function showLanguages($simple = false)
    {
        $languageActive = Language::active();
        $languages = Language::languages();
        if (count($languages) > 1) {
            $html = '';
            foreach ($languages as $language) {
                $html .= '<div class="language language_' . $language['id'] . '">';
                $name = ($simple) ? $language['id'] : $language['name'];
                if ($language['id'] == $languageActive) {
                    $html .= '<span title="' . $language['name'] . '">' . $name . '</span> ';
                } else {
                    $html .= '<a href="' . Url::urlLanguageHome($language['id']) . '" title="' . $language['name'] . '">' . $name . '</a> ';
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

}
