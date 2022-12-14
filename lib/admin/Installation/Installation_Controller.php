<?php
/**
 * @class InstallationController
 *
 * This is the controller for all the actions in the administration area.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class Installation_Controller extends Controller
{

    /**
     * Main function to control the administration system.
     */
    public function getContent()
    {
        $ui = new NavigationAdmin_Ui($this);
        $this->mode = 'admin';
        $this->layout_page = 'clear';
        $this->meta_image = '';
        if (!ASTERION_DEBUG) {
            header('Location: ' . url(''));
            exit();
        }
        switch ($this->action) {
            default:
                $this->title_page = 'Configuration';
                $this->content = Installation_Ui::renderDatabaseConnection();
                return $ui->render();
                break;
            case 'database':
                $this->title_page = 'Database';
                $this->content = Installation_Ui::renderDatabase();
                return $ui->render();
                break;
            case 'update_database':
                foreach (Init::errorsDatabase() as $item) {
                    Db::execute($item['query']);
                }
                header('Location: ' . url('', true));
                exit();
                break;
            case 'languages':
                $this->layout_page = 'clear';
                $this->title_page = 'Languages';
                if (isset($this->values['language']) && count($this->values) > 0) {
                    Session::set('languages_creation', array_keys($this->values['language']));
                    Session::set('languages_creation_admin_translations', (isset($this->values['admin_translations'])));
                    $this->content = Installation_Ui::renderLanguagesVerification(array_keys($this->values['language']));
                    return $ui->render();
                }
                $this->content = Installation_Ui::renderLanguages();
                return $ui->render();
                break;
            case 'install_languages':
                $languages = Session::get('languages_creation');
                $admin_translations = Session::get('languages_creation_admin_translations');
                if (is_array($languages) && count($languages) > 0) {
                    $isoLanguages = Language::isoList();
                    $translation = new Translation();
                    foreach ($languages as $language) {
                        if (isset($isoLanguages[$language])) {
                            $newLanguage = new Language([
                                'id' => $language,
                                'name' => $isoLanguages[$language]['name'],
                                'local_names' => $isoLanguages[$language]['local_names']
                            ]);
                            $newLanguage->persist();
                        }
                    }
                    Language::load();
                    foreach (Language::languages() as $language) {
                        Db::execute($translation->updateAttributeQuery($translation->getAttribute('translation'), $language['id']));
                    }
                    foreach (Language::languages() as $language) {
                        if ($admin_translations) {
                            $file = ASTERION_DATA_FILE . 'admin_translations/' . $language['id'] . '.json';
                            if (is_file($file)) {
                                $translationCodes = @json_decode(@file_get_contents($file), true);
                                if (is_array($translationCodes)) {
                                    foreach ($translationCodes as $translationCode => $translationText) {
                                        $translation = (new Translation)->readFirst(['where' => 'code="' . $translationCode . '"']);
                                        if ($translation->id() != '') {
                                            $translation->set('translation_' . $language['id'], $translationText);
                                        } else {
                                            $translation = new Translation(['code' => $translationCode, 'translation_' . $language['id'] => $translationText]);
                                        }
                                        $translation->persist();
                                    }
                                }
                            }
                        }
                    }
                    header('Location: ' . url('', true));
                } else {
                    header('Location: ' . url('installation/languages', true));
                }
                break;
        }
    }

}
