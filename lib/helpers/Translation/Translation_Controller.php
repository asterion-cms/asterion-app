<?php
/**
 * @class Translation_Controller
 *
 * This is the controller class for the Translation objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Translation_Controller extends Controller
{
    public function getContent()
    {
        $this->ui = new Navigation_Ui($this);
        switch ($this->action) {
            default:
                return parent::getContent();
                break;
            case 'fill_missing':
                $this->checkLoginAdmin();
                $missingTranslationsKeys = Translation::missingTranslationsKeys();
                foreach ($missingTranslationsKeys as $missingTranslationsKey) {
                    $translation = new Translation(['code' => $missingTranslationsKey]);
                    $translation->persist();
                }
                header('Location: ' . url('translation/list_items', true));
                exit();
                break;
            case 'reset_admin':
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('connexion_error')];
                if ($this->checkLoginAdmin()) {
                    $language = isset($this->parameters['language_translation']) ? $this->parameters['language_translation'] : '';
                    $response = Translation::resetAdminTranslations($language);
                }
                return json_encode($response);
                break;
            case 'import':
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('connexion_error')];
                if ($this->checkLoginAdmin() && isset($this->values['import_translations_url'])) {
                    $contents = json_decode(Url::getContents($this->values['import_translations_url']), true);
                    $response = Translation::import($contents);
                }
                return json_encode($response);
                break;
            case 'export':
                $this->checkLoginAdmin();
                $tmp_file = tmpfile();
                $tmp_location = stream_get_meta_data($tmp_file)['uri'];
                $zip = new ZipArchive;
                $res = $zip->open($tmp_location, ZipArchive::CREATE);
                foreach (Language::languages() as $languageValues) {
                    $language = new Language($languageValues);
                    $zip->addFromString($language->id() . '.json', json_encode($language->getTranslations()));
                }
                $zip->close();
                File::download('translations.zip', [
                    'content' => file_get_contents($tmp_location),
                    'contentType' => 'application/zip',
                ]);
                exit();
                break;
        }
    }

    public function listAdmin()
    {
        $information = (string) $this->object->info->info->form->information;
        return '
            ' . (($information != '') ? '<div class="information">' . __($information) . '</div>' : '') . '
            ' . Translation_Ui::alertMissingTranslations() . '
            ' . Translation_Ui::statisticsTranslations() . '
            ' . Translation_Ui::importTranslations() . '
            ' . $this->listAdminItems();
    }

    public function menuInsideItems()
    {
        return '
            ' . parent::menuInsideItems() . '
            ' . (($this->action == 'list_items') ? Ui::menuAdminInside('translation/export', 'download', 'export') : '');
    }

}
