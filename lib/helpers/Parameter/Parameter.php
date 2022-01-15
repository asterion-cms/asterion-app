<?php
/**
 * @class Parameter
 *
 * This class contains the parameters to run the website.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Parameter extends Db_Object
{

    /**
     * Retrieve the values in the database and load them in memory.
     */
    public static function init()
    {
        if (ASTERION_DB_USE == true) {
            $query = 'SELECT code, information, image, file FROM ' . (new Parameter)->tableName;
            $items = [];
            $result = Db::returnAll($query);
            foreach ($result as $item) {
                $information = Text::decodeText($item['information']);
                $information = ($item['image'] != '') ? Db_Object::getImageUrlFromStock('Parameter', $item['image'], 'huge') : $information;
                $information = ($item['file'] != '') ? $item['file'] : $information;
                $items[$item['code']] = $information;
            }
            $GLOBALS['parameters'] = $items;
        }
    }

    /**
     * Get the list of parameters.
     */
    public static function parametersList()
    {
        return $GLOBALS['parameters'];
    }

    /**
     * Get a parameter. The script also searches for the active language.
     */
    public static function code($code)
    {
        if (isset($GLOBALS['parameters'][$code . '_' . Language::active()])) {
            return $GLOBALS['parameters'][$code . '_' . Language::active()];
        } else {
            return (isset($GLOBALS['parameters'][$code])) ? $GLOBALS['parameters'][$code] : '';
        }
    }

    /**
     * Load an object using its code
     */
    public static function load($code)
    {
        return (new Parameter)->readFirst(array('where' => 'code="' . $code . '"'));
    }

    /**
     * Load an image url using its code
     */
    public static function getImageUrlFromCode($code, $format='web')
    {
        $parameter = Parameter::load($code);
        return ($parameter->id()!='') ? $parameter->getImageUrl('image', $format) : '';;
    }

    /**
     * Load a file url using its code
     */
    public static function getFileUrlFromCode($code)
    {
        $parameter = Parameter::load($code);
        return ($parameter->id()!='') ? $parameter->getFileUrl('file') : '';;
    }

    /**
     * Load the initial parameters for the website.
     */
    public static function saveInitialValues()
    {
        $parameters = new Parameter();
        $parameters->createTable();
        $parameters = (new Parameter)->countResults();
        $itemsUrl = ASTERION_DATA_FILE . 'Parameter.json';
        if ($parameters == 0 && is_file($itemsUrl)) {
            $items = json_decode(file_get_contents($itemsUrl), true);
            foreach (Language::languages() as $language) {
                $items[] = ['code' => 'meta_title_page_' . $language['id'], 'name' => 'Title Page - ' . $language['name'], 'information' => ASTERION_TITLE];
                $items[] = ['code' => 'meta_description_' . $language['id'], 'name' => 'Meta Description - ' . $language['name'], 'information' => ASTERION_TITLE . '...'];
                $items[] = ['code' => 'meta_keywords_' . $language['id'], 'name' => 'Meta Keywords - ' . $language['name'], 'information' => ASTERION_TITLE . '...'];
            }
            $items[] = ['code' => 'email', 'name' => 'Email', 'information' => ASTERION_EMAIL];
            $items[] = ['code' => 'email_contact', 'name' => 'Emails sent in the contact section', 'information' => ASTERION_EMAIL];
            foreach ($items as $item) {
                $parameters = new Parameter($item);
                $parameters->persist();
            }
        }
    }

}
