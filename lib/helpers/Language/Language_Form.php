<?php
/**
 * @class LanguageController
 *
 * This class manages the forms for the Language objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Language_Form extends Form
{

    /**
     * Render the set of available ISO languages as options.
     */
    public static function createFormFieldsIso()
    {
        $languages = Language::isoList();
        ksort($languages);
        $content = '';
        foreach ($languages as $key => $language) {
            $content .= '<div class="iso_language">
                            ' . FormField::show('checkbox', ['name' => 'language['.$key.']', 'id' => 'language_' . $key]) . '
                            <label for="language_' . $key . '" class="iso_language_info">
                                <p><strong>' . $key . '</strong> ' . $language['name'] . '</p>
                                <p><span>' . $language['local_names'] . '</span></p>
                            </label>
                        </div>';
        }
        return '<div class="iso_languages same_height">' . $content . '</div>';
    }

    /**
     * Render the set of available ISO languages as options.
     */
    public static function createFormIso()
    {
        return Form::createForm(Language_Form::createFormFieldsIso(), ['submit' => 'Save', 'class'=>'form_admin_simple']);
    }

    /**
     * Render the set of available ISO languages as options.
     */
    public static function createFormFieldsBasic()
    {
        $languages = [
            'en' => ['name' => 'English', 'local_names' => 'English'],
            'fr' => ['name' => 'French', 'local_names' => 'Français'],
            'es' => ['name' => 'Spanish', 'local_names' => 'Español']
        ];
        ksort($languages);
        $content = '';
        foreach ($languages as $key => $language) {
            $content .= '<div class="iso_language">
                            ' . FormField::show('checkbox', ['name' => 'language['.$key.']', 'id' => 'language_' . $key]) . '
                            <label for="language_' . $key . '" class="iso_language_info">
                                <p><strong>' . $key . '</strong> ' . $language['name'] . '</p>
                                <p><span>' . $language['local_names'] . '</span></p>
                            </label>
                        </div>';
        }
        $content .= FormField::show('hidden', ['name' => 'admin_translations', 'value' => true]);
        return '<div class="iso_languages same_height">' . $content . '</div>';
    }

    /**
     * Render the set of available ISO languages as options.
     */
    public static function createFormBasic()
    {
        return Form::createForm(Language_Form::createFormFieldsBasic(), ['submit' => 'Save', 'class'=>'form_admin_simple']);
    }

}
