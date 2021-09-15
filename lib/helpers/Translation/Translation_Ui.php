<?php
/**
 * @class Translation_Ui
 *
 * This is the UI class for the Translation objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Translation_Ui extends Ui
{

    public function renderAdminInside($options = [])
    {
        $translations = '';
        foreach (Language::languages() as $language) {
            $translations .= '
                <div class="translation_item">
                    <em>' . $language['name'] . '</em>
                    <span>' . $this->object->get('translation_' . $language['id']) . '</span>
                </div>';
        }
        return '<div class="line_admin_wrapper_ins translation_item_wrapper translation_item_wrapper_' . count(Language::languages()) . '">
                    <div class="translation_item translation_item_code">' . $this->object->get('code') . '</div>
                    ' . $translations . '
                </div>';
    }

    public static function alertMissingTranslations()
    {
        $missingTranslationsKeysApp = Translation::missingTranslationsKeys('app');
        $missingTranslationsKeysBase = Translation::missingTranslationsKeys('base');
        $missingTranslationsKeys = array_merge($missingTranslationsKeysApp, $missingTranslationsKeysBase);
        if (count($missingTranslationsKeys) > 0) {
            return '<div class="message message_alert">
                        <p>' . __('missing_translations_message') . '</p>
                        <p class="message_simple">' . implode(', ', $missingTranslationsKeysApp) . '</p>
                        <p class="message_simple">' . implode(', ', $missingTranslationsKeysBase) . '</p>
                        <div class="buttons">
                            <a href="' . url('translation/fill_missing', true) . '" class="button">' . __('create_empty_translations') . '</a>
                        </div>
                    </div>';
        }
    }

    public static function statisticsTranslations()
    {
        $completeTranslationsHtml = '';
        $countTotal = (new Translation)->countResults();
        foreach (Language::languages() as $language) {
            $countLanguage = (new Translation)->countResults([
                'where' => 'translation_' . $language['id'] . '!="" AND translation_' . $language['id'] . ' IS NOT NULL',
            ]);
            $percentageLanguage = Text::decimal(100 * ($countLanguage / $countTotal));
            $completeTranslationsHtml .= '
                <div class="translation_statistic">
                    <div class="translation_statistic_title">' . $language['name'] . '</div>
                    <div class="translation_statistic_wrapper">
                        <div class="translation_statistic_bar_legend">
                            ' . $countLanguage . ' / ' . $countTotal . ' ( ' . $percentageLanguage . '% )
                        </div>
                        <div class="translation_statistic_bar">
                            <div class="translation_statistic_bar_completed" style="width: ' . $percentageLanguage . '%;"></div>
                        </div>
                        <div class="translation_statistic_reset"
                            data-url="' . url('translation/reset_admin?language_translation=' . $language['id'], true) . '"
                            data-confirm="' . __('reset_admin_translations') . '"
                            >' . __('reset_admin_translations') . '</div>
                    </div>
                    <div class="translation_statistic_results">
                        <div class="translation_statistic_result_created"><span></span> ' . __('terms_created') . '</div>
                        <div class="translation_statistic_result_updated"><span></span> ' . __('terms_updated') . '</div>
                    </div>
                </div>';
        }
        return '<div class="administration_block">
                    <div class="administration_block_title">' . __('statistics') . '</div>
                    <div class="administration_block_content">
                        ' . $completeTranslationsHtml . '
                    </div>
                </div>';
    }

    public static function importTranslations()
    {
        $fields = FormField::show('text_url', ['name' => 'import_translations_url', 'placeholder' => __('url_placeholder')]);
        return '<div class="administration_block">
                    <div class="administration_block_title">' . __('import_translations') . '</div>
                    <div class="administration_block_subtitle">' . __('import_translations_message') . '</div>
                    <div class="administration_block_content">
                        ' . Form::createForm($fields, ['action' => url('translation/import', true), 'submit' => __('import'), 'class'=>'form_admin_simple_line form_admin_import_translations']) . '
                        <div class="translation_statistic_results">
                            <div class="translation_statistic_result_created"><span></span> ' . __('terms_created') . '</div>
                            <div class="translation_statistic_result_updated"><span></span> ' . __('terms_updated') . '</div>
                        </div>
                    </div>
                </div>';
    }

}
