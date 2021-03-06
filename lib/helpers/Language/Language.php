<?php
/**
 * @class Language
 *
 * This class represents a language for the website.
 * It is used to manage the different translations.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Language extends Db_Object
{

    /**
     * Initialize the translations.
     */
    public static function init()
    {
        $languages = Language::languages();
        $languageUrl = (isset($_GET['language'])) ? $_GET['language'] : '';
        $language = (isset($languages[$languageUrl])) ? $languages[$languageUrl] : reset($languages);
        Session::set('language', $language);
        $GLOBALS['translations'] = [];
        if (ASTERION_DB_USE == true && (ASTERION_LANGUAGE_LOAD_TRANSLATIONS == true || Url::isAdministration())) {
            $GLOBALS['translations'] = Translation::load($language['id']);
        }
    }

    /**
     * Get the languages.
     */
    public static function languages()
    {
        if (!isset($GLOBALS['languages'])) {
            Language::load();
        }
        return $GLOBALS['languages'];
    }

    /**
     * Fill the laguange code and labels into ENV variables.
     */
    public static function load()
    {
        if (defined('ASTERION_LANGUAGE_ID')) {
            $GLOBALS['languages'] = [
                constant('ASTERION_LANGUAGE_ID') => [
                    'id' => constant('ASTERION_LANGUAGE_ID'),
                    'name' => constant('ASTERION_LANGUAGE_NAME'),
                ],
            ];
        } else if (ASTERION_DB_USE == true && (ASTERION_LANGUAGE_LOAD_TRANSLATIONS == true || Url::isAdministration())) {
            $query = 'SELECT * FROM ' . (new Language())->tableName . ' ORDER BY ord';
            $languages = [];
            foreach (Db::returnAll($query, [], false) as $item) {
                $languages[$item['id']] = $item;
            }
            $GLOBALS['languages'] = $languages;
        } else {
            $GLOBALS['languages'] = [];
        }
    }

    /**
     * Get the active language
     */
    public static function active()
    {
        return (isset(Session::get('language')['id'])) ? Session::get('language')['id'] : '';
    }

    /**
     * set the active language
     */
    public static function setActive($languageCode)
    {
        $languages = Language::languages();
        $language = (isset($languages[$languageCode])) ? $languages[$languageCode] : [];
        Session::set('language', $language);
    }

    /**
     * Format the table field using the languages.
     */
    public static function field($field)
    {
        $result = '';
        foreach (Language::languages() as $language) {
            $result .= $field . '_' . $language . ',';
        }
        return substr($result, 0, -1);
    }

    /**
     * Return an array with all the translations by code.
     */
    public function getTranslations()
    {
        $result = [];
        $translations = (new Translation)->readList(['order' => 'code']);
        $keys = Translation::translationsKeys();
        foreach ($translations as $translation) {
            $result[$translation->get('code')] = $translation->get('translation_' . $this->id());
        }
        return $result;
    }

    /**
     * List of all ISO languages
     */
    public static function isoList()
    {
        return [
            'ab' => ['group' => 'Northwest Caucasian', 'name' => 'Abkhazian', 'local_names' => '?????????? ????????????, ????????????'],
            'aa' => ['group' => 'Afro-Asiatic', 'name' => 'Afar', 'local_names' => 'Afaraf'],
            'af' => ['group' => 'Indo-European', 'name' => 'Afrikaans', 'local_names' => 'Afrikaans'],
            'ak' => ['group' => 'Niger???Congo', 'name' => 'Akan', 'local_names' => 'Akan'],
            'sq' => ['group' => 'Indo-European', 'name' => 'Albanian', 'local_names' => 'Shqip'],
            'am' => ['group' => 'Afro-Asiatic', 'name' => 'Amharic', 'local_names' => '????????????'],
            'ar' => ['group' => 'Afro-Asiatic', 'name' => 'Arabic', 'local_names' => '??????????????'],
            'an' => ['group' => 'Indo-European', 'name' => 'Aragonese', 'local_names' => 'aragon??s'],
            'hy' => ['group' => 'Indo-European', 'name' => 'Armenian', 'local_names' => '??????????????'],
            'as' => ['group' => 'Indo-European', 'name' => 'Assamese', 'local_names' => '?????????????????????'],
            'av' => ['group' => 'Northeast Caucasian', 'name' => 'Avaric', 'local_names' => '???????? ????????, ???????????????? ????????'],
            'ae' => ['group' => 'Indo-European', 'name' => 'Avestan', 'local_names' => 'avesta'],
            'ay' => ['group' => 'Aymaran', 'name' => 'Aymara', 'local_names' => 'aymar aru'],
            'az' => ['group' => 'Turkic', 'name' => 'Azerbaijani', 'local_names' => 'az??rbaycan dili'],
            'bm' => ['group' => 'Niger???Congo', 'name' => 'Bambara', 'local_names' => 'bamanankan'],
            'ba' => ['group' => 'Turkic', 'name' => 'Bashkir', 'local_names' => '?????????????? ????????'],
            'eu' => ['group' => 'Languageuage isolate', 'name' => 'Basque', 'local_names' => 'euskara, euskera'],
            'be' => ['group' => 'Indo-European', 'name' => 'Belarusian', 'local_names' => '???????????????????? ????????'],
            'bn' => ['group' => 'Indo-European', 'name' => 'Bengali', 'local_names' => '???????????????'],
            'bh' => ['group' => 'Indo-European', 'name' => 'Bihari languages', 'local_names' => '?????????????????????'],
            'bi' => ['group' => 'Creole', 'name' => 'Bislama', 'local_names' => 'Bislama'],
            'bs' => ['group' => 'Indo-European', 'name' => 'Bosnian', 'local_names' => 'bosanski jezik'],
            'br' => ['group' => 'Indo-European', 'name' => 'Breton', 'local_names' => 'brezhoneg'],
            'bg' => ['group' => 'Indo-European', 'name' => 'Bulgarian', 'local_names' => '?????????????????? ????????'],
            'my' => ['group' => 'Sino-Tibetan', 'name' => 'Burmese', 'local_names' => '???????????????'],
            'ca' => ['group' => 'Indo-European', 'name' => 'Catalan, Valencian', 'local_names' => 'catal??, valenci??'],
            'ch' => ['group' => 'Austronesian', 'name' => 'Chamorro', 'local_names' => 'Chamoru'],
            'ce' => ['group' => 'Northeast Caucasian', 'name' => 'Chechen', 'local_names' => '?????????????? ????????'],
            'ny' => ['group' => 'Niger???Congo', 'name' => 'Chichewa, Chewa, Nyanja', 'local_names' => 'chiChe??a, chinyanja'],
            'zh' => ['group' => 'Sino-Tibetan', 'name' => 'Chinese', 'local_names' => '????????(Zh??ngw??n),????????,????????'],
            'cv' => ['group' => 'Turkic', 'name' => 'Chuvash', 'local_names' => '?????????? ??????????'],
            'kw' => ['group' => 'Indo-European', 'name' => 'Cornish', 'local_names' => 'Kernewek'],
            'co' => ['group' => 'Indo-European', 'name' => 'Corsican', 'local_names' => 'corsu, lingua corsa'],
            'cr' => ['group' => 'Algonquian', 'name' => 'Cree', 'local_names' => '?????????????????????'],
            'hr' => ['group' => 'Indo-European', 'name' => 'Croatian', 'local_names' => 'hrvatski jezik'],
            'cs' => ['group' => 'Indo-European', 'name' => 'Czech', 'local_names' => '??e??tina, ??esk?? jazyk'],
            'da' => ['group' => 'Indo-European', 'name' => 'Danish', 'local_names' => 'dansk'],
            'dv' => ['group' => 'Indo-European', 'name' => 'Divehi, Dhivehi, Maldivian', 'local_names' => '????????????'],
            'nl' => ['group' => 'Indo-European', 'name' => 'Dutch,??Flemish', 'local_names' => 'Nederlands, Vlaams'],
            'dz' => ['group' => 'Sino-Tibetan', 'name' => 'Dzongkha', 'local_names' => '??????????????????'],
            'en' => ['group' => 'Indo-European', 'name' => 'English', 'local_names' => 'English'],
            'eo' => ['group' => 'Constructed', 'name' => 'Esperanto', 'local_names' => 'Esperanto'],
            'et' => ['group' => 'Uralic', 'name' => 'Estonian', 'local_names' => 'eesti, eesti keel'],
            'ee' => ['group' => 'Niger???Congo', 'name' => 'Ewe', 'local_names' => 'E??egbe'],
            'fo' => ['group' => 'Indo-European', 'name' => 'Faroese', 'local_names' => 'f??royskt'],
            'fj' => ['group' => 'Austronesian', 'name' => 'Fijian', 'local_names' => 'vosa Vakaviti'],
            'fi' => ['group' => 'Uralic', 'name' => 'Finnish', 'local_names' => 'suomi, suomen kieli'],
            'fr' => ['group' => 'Indo-European', 'name' => 'French', 'local_names' => 'fran??ais, langue fran??aise'],
            'ff' => ['group' => 'Niger???Congo', 'name' => 'Fulah', 'local_names' => 'Fulfulde, Pulaar, Pular'],
            'gl' => ['group' => 'Indo-European', 'name' => 'Galician', 'local_names' => 'Galego'],
            'ka' => ['group' => 'Kartvelian', 'name' => 'Georgian', 'local_names' => '?????????????????????'],
            'de' => ['group' => 'Indo-European', 'name' => 'German', 'local_names' => 'Deutsch'],
            'el' => ['group' => 'Indo-European', 'name' => 'Greek, Modern (1453???)', 'local_names' => '????????????????'],
            'gn' => ['group' => 'Tupian', 'name' => 'Guarani', 'local_names' => 'Ava??e\'???'],
            'gu' => ['group' => 'Indo-European', 'name' => 'Gujarati', 'local_names' => '?????????????????????'],
            'ht' => ['group' => 'Creole', 'name' => 'Haitian, Haitian Creole', 'local_names' => 'Krey??l ayisyen'],
            'ha' => ['group' => 'Afro-Asiatic', 'name' => 'Hausa', 'local_names' => '(Hausa) ????????????'],
            'he' => ['group' => 'Afro-Asiatic', 'name' => 'Hebrew', 'local_names' => '??????????'],
            'hz' => ['group' => 'Niger???Congo', 'name' => 'Herero', 'local_names' => 'Otjiherero'],
            'hi' => ['group' => 'Indo-European', 'name' => 'Hindi', 'local_names' => '??????????????????, ???????????????'],
            'ho' => ['group' => 'Austronesian', 'name' => 'Hiri Motu', 'local_names' => 'Hiri Motu'],
            'hu' => ['group' => 'Uralic', 'name' => 'Hungarian', 'local_names' => 'magyar'],
            'ia' => ['group' => 'Constructed', 'name' => 'Interlingua', 'local_names' => 'Interlingua'],
            'id' => ['group' => 'Austronesian', 'name' => 'Indonesian', 'local_names' => 'Bahasa Indonesia'],
            'ie' => ['group' => 'Constructed', 'name' => 'Interlingue, Occidental', 'local_names' => '??Occidental, Interlingue'],
            'ga' => ['group' => 'Indo-European', 'name' => 'Irish', 'local_names' => 'Gaeilge'],
            'ig' => ['group' => 'Niger???Congo', 'name' => 'Igbo', 'local_names' => 'As???s??? Igbo'],
            'ik' => ['group' => 'Eskimo???Aleut', 'name' => 'Inupiaq', 'local_names' => 'I??upiaq, I??upiatun'],
            'io' => ['group' => 'Constructed', 'name' => 'Ido', 'local_names' => 'Ido'],
            'is' => ['group' => 'Indo-European', 'name' => 'Icelandic', 'local_names' => '??slenska'],
            'it' => ['group' => 'Indo-European', 'name' => 'Italian', 'local_names' => 'Italiano'],
            'iu' => ['group' => 'Eskimo???Aleut', 'name' => 'Inuktitut', 'local_names' => '??????????????????'],
            'ja' => ['group' => 'Japonic', 'name' => 'Japanese', 'local_names' => '???????????(????????????)'],
            'jv' => ['group' => 'Austronesian', 'name' => 'Javanese', 'local_names' => '????????????, Basa Jawa'],
            'kl' => ['group' => 'Eskimo???Aleut', 'name' => 'Kalaallisut, Greenlandic', 'local_names' => 'kalaallisut, kalaallit oqaasii'],
            'kn' => ['group' => 'Dravidian', 'name' => 'Kannada', 'local_names' => '???????????????'],
            'kr' => ['group' => 'Nilo-Saharan', 'name' => 'Kanuri', 'local_names' => 'Kanuri'],
            'ks' => ['group' => 'Indo-European', 'name' => 'Kashmiri', 'local_names' => '?????????????????????,?????????????????'],
            'kk' => ['group' => 'Turkic', 'name' => 'Kazakh', 'local_names' => '?????????? ????????'],
            'km' => ['group' => 'Austroasiatic', 'name' => 'Central Khmer', 'local_names' => '???????????????, ????????????????????????, ???????????????????????????'],
            'ki' => ['group' => 'Niger???Congo', 'name' => 'Kikuyu, Gikuyu', 'local_names' => 'G??k??y??'],
            'rw' => ['group' => 'Niger???Congo', 'name' => 'Kinyarwanda', 'local_names' => 'Ikinyarwanda'],
            'ky' => ['group' => 'Turkic', 'name' => 'Kirghiz, Kyrgyz', 'local_names' => '????????????????, ???????????? ????????'],
            'kv' => ['group' => 'Uralic', 'name' => 'Komi', 'local_names' => '???????? ??????'],
            'kg' => ['group' => 'Niger???Congo', 'name' => 'Kongo', 'local_names' => 'Kikongo'],
            'ko' => ['group' => 'Koreanic', 'name' => 'Korean', 'local_names' => '?????????'],
            'ku' => ['group' => 'Indo-European', 'name' => 'Kurdish', 'local_names' => 'Kurd??,???????????????'],
            'kj' => ['group' => 'Niger???Congo', 'name' => 'Kuanyama, Kwanyama', 'local_names' => 'Kuanyama'],
            'la' => ['group' => 'Indo-European', 'name' => 'Latin', 'local_names' => 'latine, lingua latina'],
            'lb' => ['group' => 'Indo-European', 'name' => 'Luxembourgish, Letzeburgesch', 'local_names' => 'L??tzebuergesch'],
            'lg' => ['group' => 'Niger???Congo', 'name' => 'Ganda', 'local_names' => 'Luganda'],
            'li' => ['group' => 'Indo-European', 'name' => 'Limburgan, Limburger, Limburgish', 'local_names' => 'Limburgs'],
            'ln' => ['group' => 'Niger???Congo', 'name' => 'Lingala', 'local_names' => 'Ling??la'],
            'lo' => ['group' => 'Tai???Kadai', 'name' => 'Lao', 'local_names' => '?????????????????????'],
            'lt' => ['group' => 'Indo-European', 'name' => 'Lithuanian', 'local_names' => 'lietuvi?? kalba'],
            'lu' => ['group' => 'Niger???Congo', 'name' => 'Luba-Katanga', 'local_names' => 'Kiluba'],
            'lv' => ['group' => 'Indo-European', 'name' => 'Latvian', 'local_names' => 'latvie??u valoda'],
            'gv' => ['group' => 'Indo-European', 'name' => 'Manx', 'local_names' => 'Gaelg, Gailck'],
            'mk' => ['group' => 'Indo-European', 'name' => 'Macedonian', 'local_names' => '???????????????????? ??????????'],
            'mg' => ['group' => 'Austronesian', 'name' => 'Malagasy', 'local_names' => 'fiteny malagasy'],
            'ms' => ['group' => 'Austronesian', 'name' => 'Malay', 'local_names' => 'Bahasa Melayu,?????????? ?????????????'],
            'ml' => ['group' => 'Dravidian', 'name' => 'Malayalam', 'local_names' => '??????????????????'],
            'mt' => ['group' => 'Afro-Asiatic', 'name' => 'Maltese', 'local_names' => 'Malti'],
            'mi' => ['group' => 'Austronesian', 'name' => 'Maori', 'local_names' => 'te reo M??ori'],
            'mr' => ['group' => 'Indo-European', 'name' => 'Marathi', 'local_names' => '???????????????'],
            'mh' => ['group' => 'Austronesian', 'name' => 'Marshallese', 'local_names' => 'Kajin M??aje??'],
            'mn' => ['group' => 'Mongolic', 'name' => 'Mongolian', 'local_names' => '???????????? ??????'],
            'na' => ['group' => 'Austronesian', 'name' => 'Nauru', 'local_names' => 'Dorerin Naoero'],
            'nv' => ['group' => 'Den?????Yeniseian', 'name' => 'Navajo, Navaho', 'local_names' => 'Din?? bizaad'],
            'nd' => ['group' => 'Niger???Congo', 'name' => 'North Ndebele', 'local_names' => 'isiNdebele'],
            'ne' => ['group' => 'Indo-European', 'name' => 'Nepali', 'local_names' => '??????????????????'],
            'ng' => ['group' => 'Niger???Congo', 'name' => 'Ndonga', 'local_names' => 'Owambo'],
            'nb' => ['group' => 'Indo-European', 'name' => 'Norwegian Bokm??l', 'local_names' => 'Norsk Bokm??l'],
            'nn' => ['group' => 'Indo-European', 'name' => 'Norwegian Nynorsk', 'local_names' => 'Norsk Nynorsk'],
            'no' => ['group' => 'Indo-European', 'name' => 'Norwegian', 'local_names' => 'Norsk'],
            'ii' => ['group' => 'Sino-Tibetan', 'name' => 'Sichuan Yi, Nuosu', 'local_names' => '????????? Nuosuhxop'],
            'nr' => ['group' => 'Niger???Congo', 'name' => 'South Ndebele', 'local_names' => 'isiNdebele'],
            'oc' => ['group' => 'Indo-European', 'name' => 'Occitan', 'local_names' => 'occitan, lenga d\'??c'],
            'oj' => ['group' => 'Algonquian', 'name' => 'Ojibwa', 'local_names' => '????????????????????????'],
            'cu' => ['group' => 'Indo-European', 'name' => 'Church??Slavic, Old Bulgarian', 'local_names' => '?????????? ????????????????????'],
            'om' => ['group' => 'Afro-Asiatic', 'name' => 'Oromo', 'local_names' => 'Afaan Oromoo'],
            'or' => ['group' => 'Indo-European', 'name' => 'Oriya', 'local_names' => '???????????????'],
            'os' => ['group' => 'Indo-European', 'name' => 'Ossetian, Ossetic', 'local_names' => '???????? ??????????'],
            'pa' => ['group' => 'Indo-European', 'name' => 'Punjabi, Panjabi', 'local_names' => '??????????????????,?????????????????'],
            'pi' => ['group' => 'Indo-European', 'name' => 'Pali', 'local_names' => '????????????, ????????????'],
            'fa' => ['group' => 'Indo-European', 'name' => 'Persian', 'local_names' => '??????????'],
            'pl' => ['group' => 'Indo-European', 'name' => 'Polish', 'local_names' => 'j??zyk polski, polszczyzna'],
            'ps' => ['group' => 'Indo-European', 'name' => 'Pashto, Pushto', 'local_names' => '????????'],
            'pt' => ['group' => 'Indo-European', 'name' => 'Portuguese', 'local_names' => 'Portugu??s'],
            'qu' => ['group' => 'Quechuan', 'name' => 'Quechua', 'local_names' => 'Runa Simi, Kichwa'],
            'rm' => ['group' => 'Indo-European', 'name' => 'Romansh', 'local_names' => 'Rumantsch Grischun'],
            'rn' => ['group' => 'Niger???Congo', 'name' => 'Rundi', 'local_names' => 'Ikirundi'],
            'ro' => ['group' => 'Indo-European', 'name' => 'Romanian, Moldavian, Moldovan', 'local_names' => 'Rom??n??'],
            'ru' => ['group' => 'Indo-European', 'name' => 'Russian', 'local_names' => '??????????????'],
            'sa' => ['group' => 'Indo-European', 'name' => 'Sanskrit', 'local_names' => '???????????????????????????'],
            'sc' => ['group' => 'Indo-European', 'name' => 'Sardinian', 'local_names' => 'sardu'],
            'sd' => ['group' => 'Indo-European', 'name' => 'Sindhi', 'local_names' => '??????????????????,???????????? ?????????????'],
            'se' => ['group' => 'Uralic', 'name' => 'Northern Sami', 'local_names' => 'Davvis??megiella'],
            'sm' => ['group' => 'Austronesian', 'name' => 'Samoan', 'local_names' => 'gagana fa\'a Samoa'],
            'sg' => ['group' => 'Creole', 'name' => 'Sango', 'local_names' => 'y??ng?? t?? s??ng??'],
            'sr' => ['group' => 'Indo-European', 'name' => 'Serbian', 'local_names' => '???????????? ??????????'],
            'gd' => ['group' => 'Indo-European', 'name' => 'Gaelic, Scottish Gaelic', 'local_names' => 'G??idhlig'],
            'sn' => ['group' => 'Niger???Congo', 'name' => 'Shona', 'local_names' => 'chiShona'],
            'si' => ['group' => 'Indo-European', 'name' => 'Sinhala, Sinhalese', 'local_names' => '???????????????'],
            'sk' => ['group' => 'Indo-European', 'name' => 'Slovak', 'local_names' => 'Sloven??ina, Slovensk?? Jazyk'],
            'sl' => ['group' => 'Indo-European', 'name' => 'Slovenian', 'local_names' => 'Slovenski Jezik, Sloven????ina'],
            'so' => ['group' => 'Afro-Asiatic', 'name' => 'Somali', 'local_names' => 'Soomaaliga, af Soomaali'],
            'st' => ['group' => 'Niger???Congo', 'name' => 'Southern Sotho', 'local_names' => 'Sesotho'],
            'es' => ['group' => 'Indo-European', 'name' => 'Spanish, Castilian', 'local_names' => 'Espa??ol'],
            'su' => ['group' => 'Austronesian', 'name' => 'Sundanese', 'local_names' => 'Basa Sunda'],
            'sw' => ['group' => 'Niger???Congo', 'name' => 'Swahili', 'local_names' => 'Kiswahili'],
            'ss' => ['group' => 'Niger???Congo', 'name' => 'Swati', 'local_names' => 'SiSwati'],
            'sv' => ['group' => 'Indo-European', 'name' => 'Swedish', 'local_names' => 'Svenska'],
            'ta' => ['group' => 'Dravidian', 'name' => 'Tamil', 'local_names' => '???????????????'],
            'te' => ['group' => 'Dravidian', 'name' => 'Telugu', 'local_names' => '??????????????????'],
            'tg' => ['group' => 'Indo-European', 'name' => 'Tajik', 'local_names' => '????????????,??to??ik??,?????????????????'],
            'th' => ['group' => 'Tai???Kadai', 'name' => 'Thai', 'local_names' => '?????????'],
            'ti' => ['group' => 'Afro-Asiatic', 'name' => 'Tigrinya', 'local_names' => '????????????'],
            'bo' => ['group' => 'Sino-Tibetan', 'name' => 'Tibetan', 'local_names' => '?????????????????????'],
            'tk' => ['group' => 'Turkic', 'name' => 'Turkmen', 'local_names' => 'T??rkmen, ??????????????'],
            'tl' => ['group' => 'Austronesian', 'name' => 'Tagalog', 'local_names' => 'Wikang Tagalog'],
            'tn' => ['group' => 'Niger???Congo', 'name' => 'Tswana', 'local_names' => 'Setswana'],
            'to' => ['group' => 'Austronesian', 'name' => 'Tonga??(Tonga Islands)', 'local_names' => 'Faka Tonga'],
            'tr' => ['group' => 'Turkic', 'name' => 'Turkish', 'local_names' => 'T??rk??e'],
            'ts' => ['group' => 'Niger???Congo', 'name' => 'Tsonga', 'local_names' => 'Xitsonga'],
            'tt' => ['group' => 'Turkic', 'name' => 'Tatar', 'local_names' => '?????????? ????????,??tatar tele'],
            'tw' => ['group' => 'Niger???Congo', 'name' => 'Twi', 'local_names' => 'Twi'],
            'ty' => ['group' => 'Austronesian', 'name' => 'Tahitian', 'local_names' => 'Reo Tahiti'],
            'ug' => ['group' => 'Turkic', 'name' => 'Uighur, Uyghur', 'local_names' => '???????????????????,??Uyghurche'],
            'uk' => ['group' => 'Indo-European', 'name' => 'Ukrainian', 'local_names' => '????????????????????'],
            'ur' => ['group' => 'Indo-European', 'name' => 'Urdu', 'local_names' => '????????'],
            'uz' => ['group' => 'Turkic', 'name' => 'Uzbek', 'local_names' => 'O??zbek,????????????,?????????????????'],
            've' => ['group' => 'Niger???Congo', 'name' => 'Venda', 'local_names' => 'Tshiven???a'],
            'vi' => ['group' => 'Austroasiatic', 'name' => 'Vietnamese', 'local_names' => 'Ti???ng Vi???t'],
            'vo' => ['group' => 'Constructed', 'name' => 'Volap??k', 'local_names' => 'Volap??k'],
            'wa' => ['group' => 'Indo-European', 'name' => 'Walloon', 'local_names' => 'Walon'],
            'cy' => ['group' => 'Indo-European', 'name' => 'Welsh', 'local_names' => 'Cymraeg'],
            'wo' => ['group' => 'Niger???Congo', 'name' => 'Wolof', 'local_names' => 'Wollof'],
            'fy' => ['group' => 'Indo-European', 'name' => 'Western Frisian', 'local_names' => 'Frysk'],
            'xh' => ['group' => 'Niger???Congo', 'name' => 'Xhosa', 'local_names' => 'isiXhosa'],
            'yi' => ['group' => 'Indo-European', 'name' => 'Yiddish', 'local_names' => '????????????'],
            'yo' => ['group' => 'Niger???Congo', 'name' => 'Yoruba', 'local_names' => 'Yor??b??'],
            'za' => ['group' => 'Tai???Kadai', 'name' => 'Zhuang, Chuang', 'local_names' => 'Sa?? cue????, Saw cuengh']];
    }

    /**
     * List of all ISO languages
     */
    public static function isoListSelectValues()
    {
        $result = [];
        foreach (Language::isoList() as $languageCode => $language) {
            $result[$languageCode] = $language['name'] . ' / ' . $language['local_names'];
        }
        return $result;
    }

}
