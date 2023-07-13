<?php
/**
 * @class DbSql
 *
 * This is the class that connects the object with the database in a logical level.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Db_Sql
{

    /**
     * Construct the object.
     */
    public function __construct($values = [])
    {
        $this->className = get_class($this);
        $this->snakeName = camelToSnake($this->className);
        $this->formName = $this->className . '_Form';
        $this->info = XML::readClass($this->className);
        $this->tableName = Db::prefixTable($this->info->table);
        $this->primary = (string) $this->info->info->sql->primary;
        $this->values = (is_array($values)) ? $values : [];
    }

    /**
     * Counts the number of objects in the DB (static function).
     */
    public function countResults($options = [], $values = [])
    {
        $fields = (isset($options['fields'])) ? $options['fields'] : '*';
        $table = (isset($options['table'])) ? $options['table'] : $this->tableName;
        $where = (isset($options['where']) && $options['where'] != '') ? $options['where'] : '1=1';
        $join = (isset($options['join'])) ? $this->formatJoin($options['join']) : '';
        $query = 'SELECT COUNT(' . $fields . ') AS count_items
                        FROM ' . $table . '
                        ' . $join . '
                        WHERE ' . $where;
        $result = Db::returnSingle($query, $values);
        return $result['count_items'];
    }

    /**
     * Returns the values of an object (static function).
     */
    public function readValues($id, $options = [])
    {
        $table = (isset($options['table'])) ? $options['table'] : $this->tableName;
        $fields = $this->formatFields($options);
        $join = (isset($options['join'])) ? $this->formatJoin($options['join']) : '';
        $query = 'SELECT ' . $fields . $this->fieldPoints() . '
                    FROM ' . $table . '
                    ' . $join . '
                    WHERE ' . $this->primary . '=:id';
        return Db::returnSingle($query, ['id' => $id]);
    }

    /**
     * Returns a single object using its id (static function).
     */
    public function read($id, $options = [])
    {
        $this->setValues($this->readValues($id, $options));
        return $this;
    }

    /**
     * Returns a single object (static function).
     */
    public function readFirst($options = [], $values = [])
    {
        $table = (isset($options['table'])) ? $options['table'] : $this->tableName;
        $values = (isset($options['values'])) ? $options['values'] : $values;
        $fields = $this->formatFields($options);
        $where = (isset($options['where']) && $options['where'] != '') ? $options['where'] : '1=1';
        $order = (isset($options['order']) && $options['order'] != '') ? ' ORDER BY ' . $options['order'] : '';
        $limit = (isset($options['limit']) && $options['limit'] != '') ? ' LIMIT ' . $options['limit'] . ',1' : ' LIMIT 1';
        $join = (isset($options['join'])) ? $this->formatJoin($options['join']) : '';
        $query = 'SELECT ' . $fields . $this->fieldPoints() . '
                    FROM ' . $table . '
                    ' . $join . '
                    WHERE ' . $where . '
                    ' . $order . '
                    ' . $limit;
        $this->setValues(Db::returnSingle($query, $values));
        return $this;
    }

    /**
     * Returns a list of objects (static function).
     */
    public function readList($options = [], $values = [])
    {
        $table = (isset($options['table'])) ? $options['table'] : $this->tableName;
        $values = (isset($options['values'])) ? $options['values'] : $values;
        $fields = $this->formatFields($options);
        $where = (isset($options['where']) && $options['where'] != '') ? $options['where'] : '1=1';
        $order = (isset($options['order']) && $options['order'] != '') ? ' ORDER BY ' . $options['order'] : '';
        $limit = (isset($options['limit']) && $options['limit'] != '') ? ' LIMIT ' . $options['limit'] : '';
        $join = (isset($options['join'])) ? $this->formatJoin($options['join']) : '';
        $query = 'SELECT ' . $fields . $this->fieldPoints() . '
                    FROM ' . $table . '
                    ' . $join . '
                    WHERE ' . $where . '
                    ' . $order . '
                    ' . $limit;
        $results = Db::returnAll($query, $values);
        $list = [];
        $completeList = (isset($options['completeList'])) ? $options['completeList'] : true;
        $results = (isset($options['fields']) && is_array($options['fields'])) ? $this->formatResultsFields($results, $options) : $results;
        foreach ($results as $result) {
            $list[] = ($completeList) ? new $this->className($result) : $result;
        }
        return $list;
    }

    /**
     * Returns a list using a query.
     */
    public function readListQuery($query, $values = [])
    {
        $query = str_replace('##', ASTERION_DB_PREFIX, $query);
        $results = Db::returnAll($query, $values);
        $list = [];
        $completeList = (isset($options['completeList'])) ? $options['completeList'] : true;
        foreach ($results as $result) {
            $list[] = ($completeList) ? new $this->className($result) : $result;
        }
        return $list;
    }

    /**
     * Formats the fields for a query.
     */
    public function formatFields($options)
    {
        $fields = $this->tableName . '.*';
        if (isset($options['fields'])) {
            if (is_array($options['fields'])) {
                $fieldsItems = [];
                foreach ($options['fields'] as $fieldObject) {
                    $fieldsItems[] = (new $fieldObject)->aliasAttributes();
                }
                $fields = implode(', ', $fieldsItems);
            } else {
                $fields = $options['fields'];
            }
        }
        return $fields;
    }

    /**
     * Formats the results for a query that has multiple objects as fields.
     */
    public function formatResultsFields($results, $options)
    {
        $list = [];
        $mainObject = $options['fields'][0];
        $mainPrefix = (new $mainObject)->snakeName . '__';
        foreach ($results as $result) {
            $mainObjectValues = [];
            foreach ($result as $resultKey => $resultItem) {
                if (strpos($resultKey, $mainPrefix) === 0) {
                    $mainObjectValues[str_replace($mainPrefix, '', $resultKey)] = $resultItem;
                }
            }
            $primaryKey = $mainObjectValues[(new $mainObject)->primary];
            $list[$primaryKey] = (isset($list[$primaryKey])) ? $list[$primaryKey] : $mainObjectValues;
            foreach ($options['fields'] as $object) {
                $prefix = (new $object)->snakeName . '__';
                if ($prefix != $mainPrefix) {
                    $prefixObjectValues = [];
                    foreach ($result as $resultKey => $resultItem) {
                        if (strpos($resultKey, $prefix) === 0) {
                            $prefixObjectValues[str_replace($prefix, '', $resultKey)] = $resultItem;
                        }
                    }
                    $prefixKey = $prefixObjectValues[(new $object)->primary];
                    $list[$primaryKey][$object][$prefixKey] = new $object($prefixObjectValues);
                }
            }
        }
        return $list;
    }

    /**
     * Formats the join option for a query.
     */
    public function formatJoin($joinOption)
    {
        if (is_array($joinOption)) {
            $joinArray = [];
            foreach ($joinOption as $join) {
                $joinArray[] = $this->formatJoinOption($join);
            }
            return implode(' ', $joinArray);
        } else {
            return $this->formatJoinOption($joinOption);
        }
    }

    /**
     * Formats the join option query.
     */
    public function formatJoinOption($join)
    {
        $completeJoin = '';
        if (is_array($join) && isset($join['object'])) {
            $joinObject = new $join['object'];
            $mode = (isset($join['mode'])) ? $join['mode'] . ' ' : '';
            if (isset($join['joinedObject'])) {
                $joinedObject = (isset($join['joinedObject'])) ? new $join['joinedObject'] : $this;
                $joinObjectField = $joinObject->findLinkAttributeName($joinedObject->className);
                $joinObjectField = ($joinObjectField != '') ? $joinObjectField : 'id';
                $completeJoin = $mode . 'JOIN ' . $joinObject->tableName . ' ON ' . $joinObject->tableName . '.' . $joinObject->primary . '=' . $joinedObject->tableName . '.' . $joinObjectField;
            } else {
                $joinObjectField = $joinObject->findLinkAttributeName($this->className);
                $joinObjectField = ($joinObjectField != '') ? $joinObjectField : 'id';
                $completeJoin = $mode . 'JOIN ' . $joinObject->tableName . ' ON ' . $joinObject->tableName . '.' . $joinObjectField . '=' . $this->tableName . '.' . $this->primary;
            }
        } else {
            $joinObject = new $join;
            $joinObjectField = $joinObject->findLinkAttributeName($this->className);
            $joinObjectField = ($joinObjectField != '') ? $joinObjectField : 'id';
            $completeJoin = 'JOIN ' . $joinObject->tableName . ' ON ' . $joinObject->tableName . '.' . $joinObjectField . '=' . $this->tableName . '.' . $this->primary;
        }
        $completeJoin .= ($completeJoin != '' && isset($join['joinAnd'])) ? ' AND ' . $join['joinAnd'] : '';
        return $completeJoin;
    }

    /**
     * Override function to perform actions before inserting an object.
     */
    public function checkBeforeInsert()
    {}

    /**
     * Override function to perform actions before updating an object.
     */
    public function checkBeforeModify()
    {}

    /**
     * Insert or update the object in the database.
     */
    public function persist($persistMultiple = true)
    {
        $objectExists = ($this->id() != '' && (new $this->className)->read($this->id())->id() != '') ? true : false;
        $mode = ($objectExists == 'true') ? 'modify' : 'insert';
        $errors = $this->validate();
        if (count($errors) > 0) {
            return ['status' => StatusCode::NOK, 'object' => $this, 'values' => $this->values, 'errors' => $errors];
        }
        if ($mode == 'insert') {
            if ($this->hasOrd() && (!isset($this->values['ord']) || $this->values['ord'] == '')) {
                $maxOrdResult = Db::returnSingle('SELECT MAX(ord) as max_ord FROM ' . $this->tableName);
                $maxOrd = (isset($maxOrdResult['max_ord'])) ? intval($maxOrdResult['max_ord']) + 1 : 1;
                $this->set('ord', $maxOrd);
            }
            $this->set('created', date("Y-m-d H:i:s"));
        }
        $this->set('modified', date("Y-m-d H:i:s"));
        $createSet = $this->createSet();
        if ($createSet['query'] != '') {
            if ($mode == 'insert') {
                $query = 'INSERT INTO ' . $this->tableName . ' SET ' . $createSet['query'];
            } else {
                $query = 'UPDATE ' . $this->tableName . ' SET ' . $createSet['query'] . ' WHERE ' . $this->primary . '="' . $this->id() . '"';
            }
            Db::execute($query, $createSet['setValues']);
            if ($mode == 'insert' && $this->hasIdAutoIncrement()) {
                $result = Db::returnSingle('SELECT LAST_INSERT_ID() AS id;');
                $this->setId(intval($result['id']));
            }
            $this->uploadFiles($this->values);
            if ($persistMultiple) {
                $this->persistMultiple();
                $this->reloadObject();
            }
            return ['status' => StatusCode::OK, 'object' => $this];
        }
        return ['status' => StatusCode::NOK, 'object' => $this, 'values' => $this->values];
    }

    /**
     * Modify a single attribute.
     */
    public function persistSimple($attribute, $value)
    {
        $query = 'UPDATE ' . $this->tableName . '
                SET ' . $attribute . ' = :' . $attribute . '
                WHERE ' . $this->primary . ' = :' . $this->primary;
        Db::execute($query, [$attribute => $value, $this->primary => $this->id()]);
        $this->set($attribute, $value);
        return ['status' => StatusCode::OK, 'object' => $this];
    }

    /**
     * Modify a list of single attributes.
     */
    public function persistSimpleArray($values)
    {
        $set = [];
        foreach ($values as $attribute => $value) {
            $set[] = $attribute . ' = :' . $attribute;
        }
        $values[$this->primary] = $this->id();
        $query = 'UPDATE ' . $this->tableName . '
                SET ' . implode(', ', $set) . '
                WHERE ' . $this->primary . ' = :' . $this->primary;
        Db::execute($query, $values);
        foreach ($values as $attribute => $value) {
            $this->set($attribute, $value);
        }
        return ['status' => StatusCode::OK, 'object' => $this];
    }

    /**
     * Insert the values related to an object.
     */
    public function persistMultiple()
    {
        $values = $this->values;
        foreach ($this->getAttributes() as $attribute) {
            $name = (string) $attribute->name;
            $type = (string) $attribute->type;
            switch ($type) {
                case 'multiple_object':
                    $refObject = (string) $attribute->refObject;
                    $linkAttribute = (string) $attribute->linkAttribute;
                    if (isset($values[$name]) && is_array($values[$name])) {
                        foreach ($values[$name] as $itemMultiple) {
                            //If it's an object
                            if (is_object($itemMultiple)) {
                                $itemMultiple->set($linkAttribute, $this->id());
                                $itemMultiple->persist();
                            }
                            //If it's an array
                            if (is_array($itemMultiple)) {
                                $itemMultipleObject = new $refObject($itemMultiple);
                                $itemMultipleObject->persist();
                                $itemMultipleObject->persistSimple($linkAttribute, $this->id());
                            }
                        }
                    }
                    break;
                case 'multiple_autocomplete':
                    $refObject = (string) $attribute->refObject;
                    $linkAttribute = (string) $attribute->linkAttribute;
                    $autoCompleteObject = (string) $attribute->autoCompleteObject;
                    $autoCompleteAttribute = (string) $attribute->autoCompleteAttribute;
                    $refObjectInstance = new $refObject();
                    if (isset($values[$name]) && is_string($values[$name])) {
                        Db::execute('DELETE FROM ' . $refObjectInstance->tableName . ' WHERE ' . $linkAttribute . '=:id', ['id' => $this->id()]);
                        $autoCompleteItems = explode(',', $values[$name]);
                        foreach ($autoCompleteItems as $autoCompleteItem) {
                            $autoCompleteItem = trim($autoCompleteItem);
                            if ($autoCompleteItem != '') {
                                //Check if it already exists
                                $autoCompleteItemObject = (new $autoCompleteObject)->readFirst(
                                    ['where' => 'BINARY ' . $autoCompleteAttribute . '=:autoCompleteItem'],
                                    ['autoCompleteItem' => $autoCompleteItem]);
                                if ($autoCompleteItemObject->id() == '') {
                                    $autoCompleteItemObject = new $autoCompleteObject([$autoCompleteAttribute => $autoCompleteItem]);
                                    $autoCompleteItemObject->persist();
                                }
                                //Check if the link exists and make it
                                $refObjectAutoCompleteAttribute = $refObjectInstance->findLinkAttributeName($autoCompleteObject);
                                $refObjectAdd = (new $refObject)->readFirst(
                                    ['where' => $linkAttribute . '=:id AND ' . $refObjectAutoCompleteAttribute . '=:autocompleteId'],
                                    ['id' => $this->id(), 'autocompleteId' => $autoCompleteItemObject->id()]);
                                if ($refObjectAdd->id() == '') {
                                    $refObjectAdd->set($linkAttribute, $this->id());
                                    $refObjectAdd->set($refObjectAutoCompleteAttribute, $autoCompleteItemObject->id());
                                    $refObjectAdd->persist(false);
                                }
                            }
                        }
                    }
                    break;
                case 'multiple_checkbox':
                    $refObject = (string) $attribute->refObject;
                    $linkAttribute = (string) $attribute->linkAttribute;
                    $checkboxObject = (string) $attribute->checkboxObject;
                    $refObjectInstance = new $refObject();
                    if (isset($values[$name]) && is_array($values[$name]) && isset($values[$name . '_checkboxes'])) {
                        Db::execute('DELETE FROM ' . $refObjectInstance->tableName . ' WHERE ' . $linkAttribute . '=:id', ['id' => $this->id()]);
                        $checkboxItems = array_keys($values[$name]);
                        foreach ($checkboxItems as $checkboxItem) {
                            //Check if the link exists and make it
                            $refObjectCheckboxAttribute = $refObjectInstance->findLinkAttributeName($checkboxObject);
                            $refObjectAdd = (new $refObject)->readFirst(
                                ['where' => $linkAttribute . '=:id AND ' . $refObjectCheckboxAttribute . '=:checkboxId'],
                                ['id' => $this->id(), 'checkboxId' => $checkboxItem]);
                            if ($refObjectAdd->id() == '') {
                                $refObjectAdd->set($linkAttribute, $this->id());
                                $refObjectAdd->set($refObjectCheckboxAttribute, $checkboxItem);
                                $refObjectAdd->persist(false);
                            }
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Delete an object and related values in multiple tables.
     */
    public function delete($parent = '', $parentAttribute = '')
    {
        foreach ($this->getAttributes() as $attribute) {
            if ((string) $attribute->onDelete == 'cascade') {
                $refObjectName = (string) $attribute->refObject;
                $refObject = new $refObjectName;
                $refObject->delete($this, $attribute);
            }
        }
        if (is_object($parent) && is_object($parentAttribute) && $parent->id() != '') {
            $parentLinkAttribute = (string) $parentAttribute->linkAttribute;
            $items = $this->readList(['where' => $parentLinkAttribute . '="' . $parent->id() . '"']);
            foreach ($items as $item) {
                $item->delete();
            }
        } else if ($this->id() != '') {
            $this->deleteFiles();
            $query = 'DELETE FROM ' . $this->tableName . ' WHERE ' . $this->primary . '="' . $this->id() . '"';
            Db::execute($query);
            return ['status' => StatusCode::OK];
        }
        return ['status' => StatusCode::NOK, 'object' => $this, 'message_error' => 'delete_error_message'];
    }

    /**
     * Delete files from the server.
     */
    public function deleteFiles()
    {
        foreach ($this->getAttributes() as $attribute) {
            if ((string) $attribute->type == "file") {
                $name = Text::simpleUrlFileBase((string) $attribute->name);
                if ((string) $attribute->mode == 'image') {
                    Image_File::deleteImage($this->className, $this->id() . '-' . $name);
                } else {
                    $file = ASTERION_STOCK_FILE . $this->className . 'Files/' . $this->get($name);
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Returns a SQL string in case of points.
     */
    public function fieldPoints()
    {
        $points = $this->info->xpath('//type[.="point"]/parent::*');
        $fields = '';
        foreach ($points as $point) {
            $pointName = (string) $point->name;
            $fields .= ', CONCAT(ST_X(' . $pointName . '),":",ST_Y(' . $pointName . ')) AS ' . $pointName;
        }
        return $fields;
    }

    /**
     * Update the order of a list of objects.
     */
    public function updateOrder($new_order)
    {
        $i = 1;
        foreach ($new_order as $new_order_id) {
            Db::execute('UPDATE ' . $this->tableName . ' SET ord=' . $i . ' WHERE ' . $this->primary . '=:id', ['id' => $new_order_id]);
            $i++;
        }
    }

    /**
     * Drops a table from the database.
     */
    public function dropTable()
    {
        $query = 'DROP TABLE IF EXISTS `' . $this->tableName . '`';
        Db::execute($query);
    }

    /**
     * Creates the table using the information in the XML file.
     */
    public function createTable()
    {
        $existsQuery = 'SHOW TABLES LIKE "' . $this->tableName . '"';
        $exists = Db::returnAll($existsQuery);
        if (count($exists) == 0) {
            $query = $this->createTableQuery();
            Db::execute($query);
            $this->createTableIndexes();
        }
    }

    /**
     * Get the query to create the table using the information in the XML file.
     */
    public function createTableQuery()
    {
        $query = 'CREATE TABLE `' . $this->tableName . '` (';
        $query .= ($this->info->info->sql->order == 'true') ? '`ord` int(10) unsigned DEFAULT NULL,' : '';
        $query .= ($this->info->info->sql->created == 'true') ? '`created` DATETIME DEFAULT NULL,' : '';
        $query .= ($this->info->info->sql->modified == 'true') ? '`modified` DATETIME DEFAULT NULL,' : '';
        $queryFields = '';
        foreach ($this->getAttributes() as $attribute) {
            $queryFields .= Db_ObjectType::createAttributeSql($attribute);
        }
        $engine = ((string) $this->info->info->sql->engine != '') ? (string) $this->info->info->sql->engine : 'MyISAM';
        $query .= substr($queryFields, 0, -1) . ') ENGINE=' . $engine . ' COLLATE utf8_unicode_ci;';
        return $query;
    }

    /**
     * Get the query to add an attribute to the table.
     */
    public function updateAttributeQuery($attribute, $language = '')
    {
        return 'ALTER TABLE `' . $this->tableName . '` ADD ' . Db_ObjectType::createAttributeSqlSimple($attribute, $language) . ';';
    }

    /**
     * Get the query for a "head" query.
     */
    public function updateHeadQuery($attribute)
    {
        if ($attribute == 'created') {
            return $this->updateCreatedQuery();
        }
        if ($attribute == 'modified') {
            return $this->updateModifiedQuery();
        }
        if ($attribute == 'order') {
            return $this->updateOrderQuery();
        }
    }

    /**
     * Get the query to add a "created" field to the table.
     */
    public function updateCreatedQuery()
    {
        return 'ALTER TABLE `' . $this->tableName . '` ADD `created` DATETIME DEFAULT NULL;';
    }

    /**
     * Get the query to add a "modified" field to the table.
     */
    public function updateModifiedQuery()
    {
        return 'ALTER TABLE `' . $this->tableName . '` ADD `modified` DATETIME DEFAULT NULL;';
    }

    /**
     * Get the query to add an "order" field to the table.
     */
    public function updateOrderQuery()
    {
        return 'ALTER TABLE `' . $this->tableName . '` ADD `ord` int(10) unsigned DEFAULT NULL;';
    }

    /**
     * Creates the table indexes defined in the class XML file.
     */
    public function createTableIndexes()
    {
        Db::executeMultiple($this->createTableIndexesQuery());
    }

    /**
     * Get the query to create the table indexes defined in the class XML file.
     */
    public function createTableIndexesQuery()
    {
        $queries = [];
        if (isset($this->info->indexes)) {
            foreach ($this->info->indexes->index as $index) {
                $name = (string) $index->name;
                $type = (string) $index->type;
                $fields = (string) $index->fields;
                $lang = (string) $index->language;
                if ($lang == 'true') {
                    foreach (Language::languages() as $language) {
                        $name = (string) $index->name . '_' . $language['id'];
                        $query = 'SHOW INDEX FROM ' . $this->tableName . ' WHERE KEY_NAME="' . $name . '"';
                        if (count(Db::returnAll($query, [], false)) == 0) {
                            $queries[] = 'CREATE ' . $type . ' INDEX `' . $name . '` ON ' . $this->tableName . ' (`' . $name . '`)';
                        }
                    }
                } else {
                    $query = 'SHOW INDEX FROM ' . $this->tableName . ' WHERE KEY_NAME="' . $name . '"';
                    if (count(Db::returnAll($query, [], false)) == 0) {
                        $queries[] = 'CREATE ' . $type . ' INDEX `' . $name . '` ON ' . $this->tableName . ' (' . $fields . ')';
                    }
                }
            }
        }
        return $queries;
    }

    /**
     * Creates a SET string used in the insertion and modification of the values in the DB.
     */
    public function createSet()
    {
        $query = '';
        $values = $this->values;
        $setValues = [];
        if ($this->hasOrd() && isset($values['ord']) && $values['ord'] != '') {
            $setValues['ord'] = $values['ord'];
            $query .= '`ord` = :ord, ';
        }
        if ($this->hasCreated() && isset($values['created']) && $values['created'] != '') {
            $setValues['created'] = $values['created'];
            $query .= '`created` = :created, ';
        }
        if ($this->hasModified() && isset($values['modified']) && $values['modified'] != '') {
            $setValues['modified'] = $values['modified'];
            $query .= '`modified` = :modified, ';
        }
        foreach ($this->getAttributes() as $attribute) {
            $name = (string) $attribute->name;
            $type = (string) $attribute->type;
            switch ($type) {
                default:
                    if ((string) $attribute->language == 'true') {
                        foreach (Language::languages() as $language) {
                            $nameLanguage = $name . '_' . $language['id'];
                            if (isset($values[$nameLanguage])) {
                                $setValues[$nameLanguage] = $values[$nameLanguage];
                                $query .= '`' . $nameLanguage . '` = :' . $nameLanguage . ', ';
                            }
                        }
                    } else {
                        if (isset($values[$name])) {
                            $setValues[$name] = $values[$name];
                            $query .= '`' . $name . '` = :' . $name . ', ';
                        }
                    }
                    break;
                case 'id_char32':
                    if ($this->id() == '' && (!isset($values[$name]) || $values[$name] == '')) {
                        $idMd5 = md5(microtime() * rand() * rand());
                        $setValues[$name] = $idMd5;
                        $query .= '`' . $name . '` = :' . $name . ', ';
                        $this->setId($idMd5);
                    }
                    break;
                case 'id_varchar':
                    if (isset($values[$name])) {
                        $setValues[$name] = Text::simpleUrl($values[$name], '');
                        $query .= '`' . $name . '` = :' . $name . ', ';
                        $this->setId($setValues[$name]);
                    }
                    break;
                case 'password':
                    if (isset($values[$name])) {
                        $password = hash('sha256', $values[$name]);
                        $setValues[$name] = $password;
                        $query .= '`' . $name . '` = :' . $name . ', ';
                    }
                    break;
                case 'hidden_url':
                    if ((string) $attribute->language == 'true') {
                        foreach (Language::languages() as $language) {
                            $textUrl = (string) $attribute->refAttribute . '_' . $language['id'];
                            $nameLanguage = $name . '_' . $language['id'];
                            if (isset($values[$textUrl])) {
                                $setValues[$nameLanguage] = Text::simple($values[$textUrl]);
                                $query .= '`' . $nameLanguage . '` = :' . $nameLanguage . ', ';
                            }
                        }
                    } else {
                        $textUrl = (string) $attribute->refAttribute;
                        if (isset($values[$textUrl])) {
                            $setValues[$name] = Text::simple($values[$textUrl]);
                            $query .= '`' . $name . '` = :' . $name . ', ';
                        }
                    }
                    break;
                case 'hidden_user_admin':
                    if (isset($values[$name . '_force'])) {
                        $setValues[$name] = $values[$name . '_force'];
                        $query .= $name . ' = :' . $name . ', ';
                    } elseif (!isset($values[$name]) || $values[$name] == '') {
                        $userAdminLogged = UserAdmin_Login::getInstance();
                        $setValues[$name] = $userAdminLogged->id();
                        $query .= $name . ' = :' . $name . ', ';
                    }
                    break;
                case 'point':
                    $pointLat = (isset($values[$name . '_lat'])) ? floatval($values[$name . '_lat']) : 0;
                    $pointLng = (isset($values[$name . '_lng'])) ? floatval($values[$name . '_lng']) : 0;
                    $query .= (isset($values[$name . '_lat']) && isset($values[$name . '_lng'])) ? '`' . $name . '`=POINT(' . $pointLat . ', ' . $pointLng . '), ' : '';
                    break;
                case 'text_code':
                    if ((string) $attribute->language == 'true') {
                        foreach (Language::languages() as $language) {
                            $nameLanguage = $name . '_' . $language['id'];
                            if (isset($values[$nameLanguage])) {
                                $setValues[$nameLanguage] = Text::simpleCode($values[$nameLanguage]);
                                $query .= '`' . $nameLanguage . '` = :' . $nameLanguage . ', ';
                            }
                        }
                    } else {
                        if (isset($values[$name])) {
                            $setValues[$name] = Text::simpleCode($values[$name]);
                            $query .= '`' . $name . '` = :' . $name . ', ';
                        }
                    }
                    break;
                case 'text_double':
                    if (isset($values[$name]) && $values[$name] != '') {
                        $setValues[$name] = floatval($values[$name]);
                        $query .= '`' . $name . '` = :' . $name . ', ';
                    }
                    break;
                case 'text_integer':
                    if (isset($values[$name]) && $values[$name] != '') {
                        $setValues[$name] = intval($values[$name]);
                        $query .= '`' . $name . '` = :' . $name . ', ';
                    }
                    break;
                case 'date':
                case 'date_complete':
                case 'date_hour':
                case 'date-checkbox':
                    if ($type == 'date-checkbox') {
                        $nameCheckbox = $name . '_checkbox';
                        $values[$nameCheckbox] = (isset($values[$nameCheckbox])) ? $values[$nameCheckbox] : 0;
                        $values[$nameCheckbox] = ($values[$nameCheckbox] === "on") ? 1 : $values[$nameCheckbox];
                        if ($values[$nameCheckbox] == '1') {
                            if (isset($values[$name]) && !isset($values[$name . 'yea'])) {
                                $query .= '`' . $name . '`="' . $values[$name] . '", ';
                            } else {
                                $yea = isset($values[$name . 'yea']) ? str_pad(intval($values[$name . 'yea']), 2, "0", STR_PAD_LEFT) : 0;
                                $mon = isset($values[$name . 'mon']) ? str_pad(intval($values[$name . 'mon']), 2, "0", STR_PAD_LEFT) : 0;
                                $day = isset($values[$name . 'day']) ? str_pad(intval($values[$name . 'day']), 2, "0", STR_PAD_LEFT) : 0;
                                $hou = isset($values[$name . 'hou']) ? str_pad(intval($values[$name . 'hou']), 2, "0", STR_PAD_LEFT) : 0;
                                $min = isset($values[$name . 'min']) ? str_pad(intval($values[$name . 'min']), 2, "0", STR_PAD_LEFT) : 0;
                                $date = $yea . '-' . $mon . '-' . $day . ' ' . $hou . ':' . $min . ':00';
                                $query .= isset($values[$name . 'yea']) ? '`' . $name . '`="' . $date . '", ' : '';
                            }
                        } else {
                            $query .= '`' . $name . '`=NULL, ';
                        }
                    } else {
                        if (isset($values[$name]) && !isset($values[$name . 'yea'])) {
                            $query .= '`' . $name . '`="' . $values[$name] . '", ';
                        } else {
                            $yea = isset($values[$name . 'yea']) ? str_pad(intval($values[$name . 'yea']), 2, "0", STR_PAD_LEFT) : 0;
                            $mon = isset($values[$name . 'mon']) ? str_pad(intval($values[$name . 'mon']), 2, "0", STR_PAD_LEFT) : 0;
                            $day = isset($values[$name . 'day']) ? str_pad(intval($values[$name . 'day']), 2, "0", STR_PAD_LEFT) : 0;
                            $hou = isset($values[$name . 'hou']) ? str_pad(intval($values[$name . 'hou']), 2, "0", STR_PAD_LEFT) : 0;
                            $min = isset($values[$name . 'min']) ? str_pad(intval($values[$name . 'min']), 2, "0", STR_PAD_LEFT) : 0;
                            $date = $yea . '-' . $mon . '-' . $day . ' ' . $hou . ':' . $min . ':00';
                            $query .= (isset($values[$name . 'yea']) || isset($values[$name . 'hou'])) ? '`' . $name . '`="' . $date . '", ' : '';
                        }
                    }
                    break;
                case 'date_text':
                    if (isset($values[$name])) {
                        $valueDate = $values[$name];
                        $valueDateInfo = explode('-', $valueDate);
                        $valueDate = null;
                        if (isset($valueDateInfo[2])) {
                            $valueDate = intval($valueDateInfo[0]) . '-' . intval($valueDateInfo[1]) . '-' . intval($valueDateInfo[2]);
                        }
                        $query .= '`' . $name . '` = :' . $name . ', ';
                        $setValues[$name] = $valueDate;
                    }
                    break;
                case 'datetime_local':
                    if (isset($values[$name])) {
                        $valueDate = '0000-00-00 00:00:00';
                        if (strlen($values[$name]) == 16) {
                            $valueDate = substr($values[$name], 0, 10) . ' ' . substr($values[$name], 11, 5) . ':00';
                        }
                        $query .= '`' . $name . '`="' . $valueDate . '", ';
                    }
                    break;
                case 'checkbox':
                    $values[$name] = (isset($values[$name])) ? $values[$name] : 0;
                    $values[$name] = ($values[$name] === "on") ? 1 : $values[$name];
                    $query .= isset($values[$name]) ? '`' . $name . '`="' . $values[$name] . '", ' : '`' . $name . '`=NULL, ';
                    break;
                case 'id_autoincrement':
                case 'file':
                case 'file_drag':
                case 'multiple_object':
                case 'multiple_autocomplete':
                case 'multiple_checkbox':
                    break;
            }
        }
        $query = ($query != '') ? substr($query, 0, -2) : $query;
        return ['query' => $query, 'setValues' => $setValues];
    }

    /**
     * Creates the foreign keys.
     */
    public function createForeignKeys()
    {
        $queries = [];
        foreach ($this->getAttributes() as $attribute) {
            $name = (string) $attribute->name;
            $type = (string) $attribute->type;
            switch (Db_ObjectType::baseType($type)) {
                case 'multiple':
                    $refObjectName = (string) $attribute->refObject;
                    $linkAttribute = (string) $attribute->linkAttribute;
                    if ($refObjectName != '' && $linkAttribute != '') {
                        $refObject = new $refObjectName;
                        $foreignKeyName = 'fk_' . $refObject->tableName . '_' . $this->tableName;
                        $queries[] = 'ALTER TABLE `' . $refObject->tableName . '`
                                    ADD CONSTRAINT `' . $foreignKeyName . '` FOREIGN KEY IF NOT EXISTS
                                    (`' . $linkAttribute . '`) REFERENCES `' . $this->tableName . '` (`' . $this->primary . '`)';
                    }
                    break;
            }
        }
        return $queries;
    }

    /**
     * Upload the files of an object according the its attributes.
     */
    public function uploadFiles($values = [])
    {
        $fields = $this->info->xpath('//type[.="file_drag"]/parent::*');
        foreach ($fields as $field) {
            $fieldName = (string) $field->name;
            $layout = (string) $field->layout;
            $originalName = (isset($values[$fieldName . '_filename'])) ? '_' . $values[$fieldName . '_filename'] : '';
            $uploadedUrl = (isset($values[$fieldName . '_uploaded']) && $values[$fieldName . '_uploaded'] != '') ? $values[$fieldName . '_uploaded'] : '';
            $uploadFile = ($uploadedUrl != '') ? $uploadedUrl : $values[$fieldName];
            $fileName = $this->id() . '_' . $fieldName . $originalName;
            if ($layout == 'image') {
                $fileName = Text::simpleUrlFileBase($fileName);
                if (Image_File::saveImageObject($uploadFile, $this->className, $fileName)) {
                    $this->persistSimple($fieldName, $fileName);
                }
            } else {
                if (File::uploadUrl($uploadFile, $this->className, $fileName)) {
                    $this->persistSimple($fieldName, $fileName);
                }
            }
        }
        $fields = $this->info->xpath('//type[.="file"]/parent::*');
        foreach ($fields as $field) {
            $fieldName = (string) $field->name;
            if (isset($values['refMultiple']) && $values['refMultiple'] != '') {
                // Upload from the FILES array
                $fieldNameMultiple = $values['refMultiple'] . '-' . $fieldName;
                // Case multiple
                if (isset($_FILES[$fieldNameMultiple]) && isset($_FILES[$fieldNameMultiple]['tmp_name']) && $_FILES[$fieldNameMultiple]['tmp_name'] != '') {
                    if ((string) $field->mode == 'image') {
                        $fileTemporary = $_FILES[$fieldNameMultiple]['tmp_name'];
                        $fileName = Text::simpleUrlFileBase($this->id() . '_' . $fieldName);
                        if (Image_File::saveImageObject($fileTemporary, $this->className, $fileName)) {
                            $this->persistSimple($fieldName, $fileName);
                        }
                        unset($_FILES[$fieldNameMultiple]);
                    } else {
                        $fileTemporary = $_FILES[$fieldNameMultiple]['tmp_name'];
                        $fileName = $this->id() . '_' . Text::simpleUrlFile($_FILES[$fieldNameMultiple]['name']);
                        if (File::uploadUrl($fileTemporary, $this->className, $fileName)) {
                            $this->persistSimple($fieldName, $fileName);
                        }
                        unset($_FILES[$fieldNameMultiple]);
                    }
                }
            } else if (isset($_FILES[$fieldName]) && isset($_FILES[$fieldName]['tmp_name']) && $_FILES[$fieldName]['tmp_name'] != '') {
                // Case single
                if (is_array($_FILES[$fieldName]['tmp_name'])) {
                    // Multiple files
                    $filesArray = [];
                    for ($i = 0; $i < count($_FILES[$fieldName]['tmp_name']); $i++) {
                        $filesArray[] = ['name' => (isset($_FILES[$fieldName]['name'][$i]) ? $_FILES[$fieldName]['name'][$i] : ''),
                            'tmp_name' => (isset($_FILES[$fieldName]['tmp_name'][$i]) ? $_FILES[$fieldName]['tmp_name'][$i] : ''),
                            'type' => (isset($_FILES[$fieldName]['type'][$i]) ? $_FILES[$fieldName]['type'][$i] : ''),
                            'error' => (isset($_FILES[$fieldName]['error'][$i]) ? $_FILES[$fieldName]['error'][$i] : ''),
                            'size' => (isset($_FILES[$fieldName]['size'][$i]) ? $_FILES[$fieldName]['size'][$i] : '')];
                    }
                    $filesSaved = [];
                    foreach ($filesArray as $key => $fileItem) {
                        $fileTemporary = $fileItem['tmp_name'];
                        switch (File::fileExtension($fileItem['name'])) {
                            default:
                                $fileName = $this->id() . '_' . Text::simpleUrlFile($_FILES[$fieldName]['name']) . '-' . $key;
                                if (File::uploadUrl($fileTemporary, $this->className, $fileName)) {
                                    $filesSaved[] = $fileName;
                                }
                                break;
                            case 'jpg':
                            case 'jpeg':
                            case 'png':
                            case 'gif':
                                $fileName = Text::simpleUrlFileBase($this->id() . '_' . $fieldName) . '-' . $key;
                                if (Image_File::saveImageObject($fileTemporary, $this->className, $fileName)) {
                                    $filesSaved[] = $fileName;
                                }
                                break;
                        }
                    }
                    if (count($filesSaved) > 0) {
                        $this->persistSimple($fieldName, implode(':', $filesSaved));
                    }

                    unset($_FILES[$fieldName]);
                } elseif ((string) $field->mode == 'adaptable') {
                    // Image and file
                    $fileTemporary = $_FILES[$fieldName]['tmp_name'];
                    switch (File::fileExtension($_FILES[$fieldName]['name'])) {
                        default:
                            $fileName = $this->id() . '_' . Text::simpleUrlFile($_FILES[$fieldName]['name']);
                            if (File::uploadUrl($fileTemporary, $this->className, $fileName)) {
                                $this->persistSimple($fieldName, $fileName);
                            }
                            break;
                        case 'jpg':
                        case 'jpeg':
                        case 'png':
                        case 'gif':
                            $fileName = Text::simpleUrlFileBase($this->id() . '_' . $fieldName);
                            if (Image_File::saveImageObject($fileTemporary, $this->className, $fileName)) {
                                $this->persistSimple($fieldName, $fileName);
                            }
                            break;
                    }
                    unset($_FILES[$fieldName]);
                } elseif ((string) $field->mode == 'image') {
                    // Image only
                    $fileTemporary = $_FILES[$fieldName]['tmp_name'];
                    $fileName = Text::simpleUrlFileBase($this->id() . '_' . $fieldName);
                    if (Image_File::saveImageObject($fileTemporary, $this->className, $fileName)) {
                        $this->persistSimple($fieldName, $fileName);
                    }
                    unset($_FILES[$fieldName]);
                } else {
                    // File only
                    $fileTemporary = $_FILES[$fieldName]['tmp_name'];
                    $fileName = $this->id() . '_' . Text::simpleUrlFile($_FILES[$fieldName]['name']);
                    if (File::uploadUrl($fileTemporary, $this->className, $fileName)) {
                        $this->persistSimple($fieldName, $fileName);
                    }
                    unset($_FILES[$fieldName]);
                }
            } else if (isset($values[$fieldName]) && filter_var($values[$fieldName], FILTER_VALIDATE_URL)) {
                // Upload from an URL
                if ((string) $field->mode == 'image') {
                    $fileName = Text::simpleUrlFileBase($this->id() . '_' . $fieldName);
                    if (Image_File::saveImageObject($values[$fieldName], $this->className, $fileName)) {
                        $this->persistSimple($fieldName, $fileName);
                    }
                } else {
                    $fileName = $this->id() . '_' . $fieldName . '.' . File::urlExtension($values[$fieldName]);
                    if (File::uploadUrl($values[$fieldName], $this->className, $fileName)) {
                        $this->persistSimple($fieldName, $fileName);
                    }
                }
            }
        }
    }

    /**
     * Get the table field name.
     */
    public function tableField($attributeName)
    {
        $attribute = $this->info->attributes->xpath('//attribute[name[.="' . $attributeName . '"]]');
        if (isset($attribute[0])) {
            return $this->tableName . '.' . $attributeName;
        }
    }

    /**
     * Find the attribute that links another object.
     */
    public function findLinkAttributeName($refObject)
    {
        $attribute = $this->attributeInfoByRefObject($refObject);
        if (is_object($attribute)) {
            return ((string) $attribute->linkAttribute != '') ? (string) $attribute->linkAttribute : (string) $attribute->name;
        }
    }

    /**
     * Creates a string with the alias of all the attributes.
     */
    public function aliasAttributes()
    {
        $alias = [];
        if ($this->hasOrd()) {
            $alias[] = ($this->tableName) . '.ord AS ' . $this->snakeName . '__ord';
        }
        if ($this->hasCreated()) {
            $alias[] = ($this->tableName) . '.created AS ' . $this->snakeName . '__created';
        }
        if ($this->hasModified()) {
            $alias[] = ($this->tableName) . '.modified AS ' . $this->snakeName . '__modified';
        }
        foreach ($this->getAttributes() as $attribute) {
            $name = (string) $attribute->name;
            $type = (string) $attribute->type;
            if (Db_ObjectType::baseType($type) != 'multiple') {
                $alias[] = ($this->tableName) . '.' . $name . ' AS ' . $this->snakeName . '__' . $name;
            }
        }
        return implode(', ', $alias);
    }

}
