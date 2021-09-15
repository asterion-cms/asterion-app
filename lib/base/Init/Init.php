<?php
/**
 * @class Init
 *
 * This class contains static functions to initialize the website.
 * It is only called in ASTERION_DEBUG mode and it helps to setup Asterion for the first time.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Init
{

    /**
     * Asterion initializes the common services.
     * It parses the URL and then checks if the database is correct.
     * It also loads the translations and parameters.
     */
    public static function initSite()
    {
        Url::init();
        if (ASTERION_DEBUG) {
            if (ASTERION_DB_USE && ASTERION_DEBUG_SCHEMA) {
                if ($_GET['type'] == 'installation') {
                    return true;
                }
                if (!Db_Connection::testConnection()) {
                    header('Location: ' . url('installation', true));
                    exit();
                }
                $errorsDatabase = Init::errorsDatabase();
                if (count($errorsDatabase) > 0) {
                    header('Location: ' . url('installation/database', true));
                    exit();
                }
                if (count(Language::languages()) == 0) {
                    header('Location: ' . url('installation/languages', true));
                    exit();
                }
                Parameter::saveInitialValues();
                $objectNames = File::scanDirectoryObjects();
                foreach ($objectNames as $objectName) {
                    $options = ($objectName == 'UserAdmin') ? ['EMAIL' => ASTERION_EMAIL] : [];
                    Init::saveInitialValues($objectName, $options);
                    if (method_exists($objectName, 'init')) {
                        $object = new $objectName();
                        $object->init();
                    }
                }
                if ((new Translation)->countResults() == 0) {
                    foreach (Language::languages() as $language) {
                        Translation::resetAdminTranslations($language['id']);
                    }
                }
            }
        }
        Language::init();
        Parameter::init();
    }

    /**
     * Check if the database is correct.
     */
    public static function errorsDatabase()
    {
        $errors = [];
        $objectNames = File::scanDirectoryObjects();
        foreach ($objectNames as $objectName) {
            $errors = array_merge(Init::errorsDatabaseObject($objectName), $errors);
        }
        return $errors;
    }

    /**
     * Check if an object is correct.
     */
    public static function errorsDatabaseObject($objectName)
    {
        $errors = [];
        $headAttributes = ['created' => 'created', 'modified' => 'modified', 'order' => 'ord'];
        $object = new $objectName;
        if (!Db::tableExists($object->tableName)) {
            $errors[] = ['object' => $objectName, 'action' => 'create', 'query' => $object->createTableQuery()];
            foreach ($object->createTableIndexesQuery() as $indexQuery) {
                $errors[] = ['object' => $objectName, 'action' => 'create_index', 'query' => $indexQuery];
            }
        } else {
            $tableDescription = Db::describe($object->tableName);
            $headInfo = (array) $object->info->info->sql;
            foreach ($headAttributes as $headAttribute => $headAttributeField) {
                if (isset($headInfo[$headAttribute]) && $headInfo[$headAttribute] == 'true') {
                    if (!isset($tableDescription[$headAttributeField])) {
                        $errors[] = [
                            'object' => $objectName,
                            'field' => $headAttribute,
                            'action' => 'update',
                            'query' => $object->updateHeadQuery($headAttribute),
                        ];
                    }
                }
            }
            foreach ($object->info->attributes->attribute as $attribute) {
                $name = (string) $attribute->name;
                $type = (string) $attribute->type;
                if (Db_ObjectType::baseType($type) != 'multiple') {
                    if ((string) $attribute->language == 'true') {
                        foreach (Language::languages() as $language) {
                            if (!isset($tableDescription[$name . '_' . $language['id']])) {
                                $errors[] = [
                                    'object' => $objectName,
                                    'field' => $name,
                                    'action' => 'update',
                                    'query' => $object->updateAttributeQuery($attribute, $language['id']),
                                ];
                            }
                        }
                    } else {
                        if (!isset($tableDescription[$name])) {
                            $errors[] = [
                                'object' => $objectName,
                                'field' => $name,
                                'action' => 'update',
                                'query' => $object->updateAttributeQuery($attribute),
                            ];
                        }
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * Load the initial values at the time of installation
     * and save them in the database.
     */
    public static function saveInitialValues($className, $extraValues = [])
    {
        $object = new $className;
        $object->createTable();
        $numberItems = $object->countResults();
        $dataUrl = ASTERION_DATA_FILE . $className . '.json';
        if (file_exists($dataUrl) && $numberItems == 0) {
            $items = json_decode(file_get_contents($dataUrl), true);
            foreach ($items as $item) {
                if (count($extraValues) > 0) {
                    foreach ($extraValues as $keyExtraValue => $itemExtraValue) {
                        foreach ($item as $keyItem => $eleItem) {
                            $item[$keyItem] = str_replace('##' . $keyExtraValue, $itemExtraValue, $eleItem);
                        }
                    }
                }
                $itemSave = new $className($item);
                $itemSave->persist();
            }
        }
    }

    /**
     * Save the Translation items for a new language.
     */
    public static function saveTranslation($lang)
    {
        $className = 'Translation';
        $object = new $className;
        $object->createTable();
        $dataUrl = ASTERION_DATA_FILE . $className . '.json';
        if (file_exists($dataUrl)) {
            $items = json_decode(file_get_contents($dataUrl), true);
            $itemTranslation = 'translation_' . $lang;
            foreach ($items as $item) {
                if (isset($item[$itemTranslation])) {
                    $query = 'UPDATE ' . $object->tableName . '
                                SET ' . $itemTranslation . '="' . $item[$itemTranslation] . '"
                                WHERE code="' . $item['code'] . '"';
                    Db::execute($query);
                }
            }
        }
    }

}
