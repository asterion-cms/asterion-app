<?php
/**
 * @class DbObject
 *
 * This class the main class for all of the content objects, they all inherit the functions contained here.
 * It basically maps the information on the database into a PHP object.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Db_Object extends Db_Sql
{

    public $values = [];
    public $errors = [];
    public $loadedMultiple = false;

    /**
     * Construct the object.
     */
    public function __construct($values = [])
    {
        $this->values = (is_array($values)) ? $values : [];
        $this->errors = [];
        parent::__construct($this->values);
        $this->setValues();
        $this->loadedMultiple = false;
    }

    /**
     * Reload the object.
     */
    public function reloadObject()
    {
        $this->values = array_merge($this->values, $this->readValues($this->id()));
        $this->setValues();
    }

    /**
     * Synchronize the values of an object.
     */
    public function setValues($newValues = [])
    {
        if (isset($newValues[$this->primary]) && $newValues[$this->primary] == '') {
            unset($newValues[$this->primary]);
        }
        $values = $this->mergeValues($newValues);
        $this->values['ord'] = (isset($values['ord'])) ? $values['ord'] : '';
        $this->values['created'] = (isset($values['created'])) ? $values['created'] : '';
        $this->values['modified'] = (isset($values['modified'])) ? $values['modified'] : '';
        foreach ($this->getAttributes() as $attribute) {
            $name = (string) $attribute->name;
            $type = (string) $attribute->type;
            switch (Db_ObjectType::baseType($type)) {
                default:
                    if ((string) $attribute->language == 'true') {
                        foreach (Language::languages() as $language) {
                            $keyLanguage = $name . '_' . $language['id'];
                            $this->values[$keyLanguage] = isset($values[$keyLanguage]) ? $values[$keyLanguage] : '';
                        }
                    } else {
                        $this->values[$name] = isset($values[$name]) ? $values[$name] : '';
                        if ($type == 'textarea_ck') {
                            $this->values[$name] = Text::decodeText($this->values[$name]);
                        }
                    }
                    break;
                case 'checkbox':
                    if (isset($newValues['hidden_verification_' . $name])) {
                        $this->values[$name] = (isset($newValues[$name])) ? $newValues[$name] : 0;
                    } else {
                        $this->values[$name] = (isset($values[$name])) ? $values[$name] : 0;
                    }
                    $this->values[$name] = ($this->values[$name] === 'on') ? 1 : $this->values[$name];
                    break;
                case 'point':
                    if (isset($values[$name . '_lat']) && isset($values[$name . '_lng'])) {
                        $values[$name] = $values[$name . '_lat'] . ':' . $values[$name . '_lng'];
                    }
                    if (isset($values[$name]) && $values[$name] != '') {
                        $infoPoint = explode(':', $values[$name]);
                        $this->values[$name . '_lat'] = (isset($infoPoint[0])) ? $infoPoint[0] : '';
                        $this->values[$name . '_lng'] = (isset($infoPoint[1])) ? $infoPoint[1] : '';
                    }
                    break;
                case 'multiple':
                    $this->values[$name] = isset($values[$name]) ? $values[$name] : [];
                    break;
            }
        }
    }

    /**
     * Add certain values to the object.
     */
    public function addValues($newValues = [])
    {
        $this->values = $this->mergeValues($newValues);
        $this->setValues();
    }

    /**
     * Merge values to the object.
     */
    public function mergeValues($newValues = [])
    {
        foreach ($newValues as $key => $newValue) {
            if (is_array($newValue)) {
                $this->values[$key] = (isset($this->values[$key]) && is_array($this->values[$key])) ? ($newValue + $this->values[$key]) : $newValue;
            } else {
                $this->values[$key] = $newValue;
            }
        }
        return $this->values;
    }

    /**
     * Load all the multiple values of the object.
     */
    public function loadMultipleValues()
    {
        if ($this->loadedMultiple != true) {
            foreach ($this->getAttributes() as $attribute) {
                $baseType = Db_ObjectType::baseType((string) $attribute->type);
                if ($baseType == 'multiple' || $baseType == 'linkid') {
                    $this->loadMultipleValuesAttribute($attribute);
                }
            }
            $this->setValues();
            $this->loadedMultiple = true;
        }
    }

    /**
     * Load all the multiple values of the object using an attribute.
     */
    public function loadMultipleValuesSingleAttribute($attributeName)
    {
        $this->loadMultipleValuesAttribute($this->getAttribute($attributeName));
    }

    /**
     * Load the multiple values of a certain attribute.
     */
    public function loadMultipleValuesAttribute($attribute)
    {
        $name = (string) $attribute->name;
        $type = (string) $attribute->type;
        switch ($type) {
            case 'linkid_autoincrement':
            case 'select':
                if ((string) $attribute->refObject != '') {
                    $name = (string) $attribute->name;
                    $object = (string) $attribute->refObject;
                    $this->set($name . '_object', (new $object)->read($this->values[$name]));
                }
                break;
            case 'multiple_object':
                $refObject = (string) $attribute->refObject;
                $linkAttribute = (string) $attribute->linkAttribute;
                $refObjectInstance = new $refObject();
                $order = ($refObjectInstance->hasOrd()) ? 'ord' : $refObjectInstance->orderBy();
                $list = $refObjectInstance->readList(
                    [
                        'where' => $refObjectInstance->tableName . '.' . $linkAttribute . '=:id',
                        'order' => $order,
                    ],
                    ['id' => $this->id()]
                );
                foreach ($list as $keyList => $listItem) {
                    foreach ($listItem->info->attributes->attribute as $listItemAttribute) {
                        if (Db_ObjectType::baseType((string) $listItemAttribute->type) == 'linkid' && (string) $listItemAttribute->refObject != '' && (string) $listItemAttribute->refObject != $this->className) {
                            $insideObjectName = (string) $listItemAttribute->refObject;
                            $insideObject = (new $insideObjectName)->read($listItem->get((string) $listItemAttribute->name));
                            $list[$keyList]->set(camelToSnake((string) $listItemAttribute->refObject), $insideObject);
                        }
                    }
                }
                $this->set($name, $list);
                break;
            case 'multiple_autocomplete':
                $refObject = (string) $attribute->refObject;
                $refObjectInstance = new $refObject();
                $autoCompleteObject = (string) $attribute->autoCompleteObject;
                $autoCompleteObjectInstance = new $autoCompleteObject();
                $linkAttribute = (string) $attribute->linkAttribute;
                $list = $autoCompleteObjectInstance->readList(
                    [
                        'join' => $refObjectInstance->className,
                        'where' => $refObjectInstance->tableName . '.' . $linkAttribute . '=:id',
                    ],
                    ['id' => $this->id()]
                );
                $this->set($name, $list);
                break;
            case 'multiple_checkbox':
                $refObject = (string) $attribute->refObject;
                $refObjectInstance = new $refObject();
                $linkAttribute = (string) $attribute->linkAttribute;
                $list = $refObjectInstance->readList(
                    ['where' => $refObjectInstance->tableName . '.' . $linkAttribute . '=:id'],
                    ['id' => $this->id()]
                );
                $this->set($name, $list);
                break;
        }
    }

    /**
     * Get the id of an object, defined in the XML final as "primary".
     */
    public function id()
    {
        return (isset($this->values[$this->primary])) ? $this->values[$this->primary] : '';
    }

    /**
     * Gets the basic info of an object, normally this function should be overwritten for each object.
     */
    public function getBasicInfo()
    {
        $label = (string) $this->info->info->form->label;
        if ($label != '') {
            return $this->decomposeText($label);
        } else {
            return $this->id();
        }
    }

    /**
     * Function to decompose a text for the labels.
     */
    public function decomposeText($label)
    {
        $info = explode('_', $label);
        $result = '';
        foreach ($info as $item) {
            if (substr($item, 0, 1) == '#') {
                $result .= $this->get(substr($item, 1));
            } else {
                if (substr($item, 0, 1) == '?') {
                    $result .= __(substr($item, 1));
                } else {
                    $result .= $item;
                }
            }
        }
        return $result;
    }

    /**
     * Gets the basic info of an object, used in the admin level.
     */
    public function getBasicInfoAdmin()
    {
        return $this->getBasicInfo();
    }

    /**
     * Gets the basic info of an object, used for the autocompletion inputs.
     */
    public function getBasicInfoAutocomplete()
    {
        return $this->getBasicInfo();
    }

    /**
     * Returns all the attributes information in the XML file.
     */
    public function getAttributes()
    {
        return $this->info->attributes->attribute;
    }

    /**
     * Returns an attribute by name.
     */
    public function getAttribute($name)
    {
        foreach ($this->getAttributes() as $attribute) {
            if ((string) $attribute->name == $name) {
                return $attribute;
            }
        }
        return false;
    }

    /**
     * Gets the public url of an object based on the "publicUrl" value on the XML file.
     */
    public function url()
    {
        $publicUrl = (string) $this->info->info->form->publicUrl;
        $attributes = Text::arrayWordsStarting('#', $publicUrl);
        foreach ($attributes as $attribute) {
            $publicUrl = str_replace($attribute, $this->get(str_replace('#', '', $attribute)), $publicUrl);
        }
        $translations = Text::arrayWordsStarting('@', $publicUrl);
        foreach ($translations as $translation) {
            $publicUrl = str_replace($translation, Text::simpleUrl(__(str_replace('@', '', $translation))), $publicUrl);
        }
        return url(str_replace(' ', '', $publicUrl));
    }

    /**
     * Gets the public url of a list of objects based on the "publicUrlList" value on the XML file.
     */
    public function urlList()
    {
        $publicUrlList = (string) $this->info->info->form->publicUrlList;
        $attributes = Text::arrayWordsStarting('#', $publicUrlList);
        foreach ($attributes as $attribute) {
            $publicUrlList = str_replace($attribute, $this->get(str_replace('#', '', $attribute)), $publicUrlList);
        }
        $translations = Text::arrayWordsStarting('@', $publicUrlList);
        foreach ($translations as $translation) {
            $publicUrlList = str_replace($translation, Text::simpleUrl(__(str_replace('@', '', $translation))), $publicUrlList);
        }
        return url(str_replace(' ', '', $publicUrlList));
    }

    /**
     * Function to get an url for the object depending on the language.
     * You need to overwrite it.
     */
    public function urlLanguage($language)
    {
        return '';
    }

    /**
     * Function to get an array of the URLs of the object for each language.
     */
    public function urlsLanguage()
    {
        $urls = [];
        foreach (Language::languages() as $language) {
            $urls[$language['id']] .= $this->urlLanguage($language['id']);
        }
        return $urls;
    }

    /**
     * Gets the administration url list.
     */
    public function urlListAdmin()
    {
        return url($this->snakeName . '/list_items/', true);
    }

    /**
     * Gets the url to modify an object in an admin level.
     */
    public function urlAdmin()
    {
        return url($this->snakeName . '/modify_view/' . $this->id(), true);
    }

    /**
     * Gets the url of a select_link attribute.
     */
    public function urlLink($attribute)
    {
        $info = explode('_', $this->get($attribute));
        switch ($info[0]) {
            case 'home_page':
                return url();
                break;
            case 'public':
                if (isset($info[1])) {
                    $objectName = $info[1];
                    $object = new $objectName();
                    return $object->urlList();
                }
                break;
            case 'item':
                if (isset($info[2])) {
                    $objectName = $info[1];
                    $object = new $objectName();
                    $object = $object->read($info[2]);
                    return $object->url();
                }
                break;
        }
    }

    /**
     * Return the url for modification, in an admin context.
     */
    public function urlModify()
    {
        return (Url::isAdministration()) ? url($this->snakeName . '/modify_view/' . $this->id(), true) : '';
    }

    /**
     * Return the url for modification, in an admin context.
     */
    public function urlModifyAjax()
    {
        return (Url::isAdministration()) ? url($this->snakeName . '/modify_view_ajax/' . $this->id(), true) : '';
    }

    /**
     * Return the url for deletion, in an admin context.
     */
    public function urlDelete($ajax = false)
    {
        return (Url::isAdministration()) ? url($this->snakeName . '/' . (($ajax) ? 'delete_item_ajax' : 'delete') . '/' . $this->id(), true) : '';
    }

    /**
     * Return the url to delete an image, in an admin context.
     */
    public function urlDeleteImage($valueFile = '')
    {
        return (Url::isAdministration()) ?
        url($this->snakeName . '/delete_image/' . $this->id() . '/' . $valueFile, true) :
        $this->urlDeleteImagePublic($valueFile);
    }

    /**
     * Return the url to delete an image in an public context. Function to override when needed.
     */
    public function urlDeleteImagePublic($valueFile = '')
    {
        return '';
    }

    /**
     * Return the url to delete a file, in an admin context.
     */
    public function urlDeleteFile($valueFile = '')
    {
        return (Url::isAdministration()) ?
        url($this->snakeName . '/delete_file/' . $this->id() . '/' . $valueFile, true) :
        $this->urlDeleteFilePublic($valueFile);
    }

    /**
     * Return the url to delete a file in an public context. Function to override when needed.
     */
    public function urlDeleteFilePublic($valueFile = '')
    {
        return '';
    }

    /**
     * Return the url to upload a temporary image in an admin context.
     */
    public function urlUploadTempImage()
    {
        return (Url::isAdministration()) ? url($this->snakeName . '/upload_temp_image', true) : $this->urlUploadTempImagePublic();
    }

    /**
     * Return the url to upload a temporary image a public context. Function to override when needed.
     */
    public function urlUploadTempImagePublic()
    {
        return '';
    }

    /**
     * Return the url to upload a temporary file in an admin context.
     */
    public function urlUploadTempFile()
    {
        return (Url::isAdministration()) ? url($this->snakeName . '/upload_temp_file', true) : $this->urlUploadTempFilePublic();
    }

    /**
     * Return the url to upload a temporary file in a public context. Function to override when needed.
     */
    public function urlUploadTempFilePublic()
    {
        return '';
    }

    /**
     * Gets the html basic link of an object.
     */
    public function link()
    {
        return '<a href="' . $this->url() . '">' . $this->getBasicInfo() . '</a>';
    }

    /**
     * Gets the html basic link of an object that opens in a new window.
     */
    public function linkNew()
    {
        return '<a href="' . $this->url() . '" target="_blank">' . $this->getBasicInfo() . '</a>';
    }

    /**
     * Gets the html link of list of objects.
     */
    public function linkList()
    {
        return '<a href="' . $this->urlList() . '">' . $this->title . '</a>';
    }

    /**
     * Gets the html basic link of an object in an admin level.
     */
    public function linkAdmin()
    {
        return '<a href="' . $this->urlAdmin() . '">' . $this->urlAdmin() . '</a>';
    }

    /**
     * Returns the values of the object.
     */
    public function valuesArray()
    {
        return (is_array($this->values)) ? $this->values : [];
    }

    /**
     * Returns the values of the object in JSON format.
     */
    public function toJson()
    {
        return json_encode((array)$this->values);
    }

    /**
     * Gets all the values of the object.
     */
    public function getValues($attribute, $admin = false)
    {
        $info = $this->attributeInfo($attribute);
        if (isset($info->refObject) && (string) $info->refObject != '') {
            $refObjectName = (string) $info->refObject;
            $refObject = new $refObjectName;
            return ($admin) ? $refObject->basicInfoAdminArray() : $refObject->basicInfoArray();
        } else {
            $result = [];
            $i = 0;
            foreach ($info->values->value as $itemIns) {
                $id = (isset($itemIns['id'])) ? (string) $itemIns['id'] : $i;
                $result[$id] = __((string) $itemIns);
                $i++;
            }
            return $result;
        }
    }

    /**
     * Returns an array with the basic information of a list of objects using the ids as keys.
     */
    public function basicInfoArray($options = [])
    {
        $options = ($this->orderBy() != "") ? array_merge(['order' => $this->orderBy()], $options) : $options;
        $items = $this->readList($options);
        $result = [];
        foreach ($items as $item) {
            $result[$item->id()] = $item->getBasicInfo();
        }
        return $result;
    }

    /**
     * Returns an array with the basic information of a list of objects using the ids as keys, in an admin level.
     */
    public function basicInfoAdminArray($options = [])
    {
        $orderAttribute = $this->orderBy();
        $order = '';
        if ($orderAttribute != '') {
            $orderInfo = $this->attributeInfo($orderAttribute);
            $order = (is_object($orderInfo) && (string) $orderInfo->language == 'true') ? $orderAttribute . '_' . Language::active() : $orderAttribute;
        }
        $options = ($order != "") ? array_merge(['order' => $order], $options) : $options;
        $items = $this->readList($options);
        $result = [];
        foreach ($items as $item) {
            $result[$item->id()] = $item->getBasicInfoAdmin();
        }
        return $result;
    }

    /**
     * Gets the information on the search properties of the object.
     */
    public function infoSearch()
    {
        return (string) $this->info->info->form->search;
    }

    /**
     * Get the search query
     */
    public function infoSearchQuery()
    {
        return (string) $this->info->info->form->searchQuery;
    }

    /**
     * Get the search query count
     */
    public function infoSearchQueryCount()
    {
        return (string) $this->info->info->form->searchQueryCount;
    }

    /**
     * Get the filter by user value
     */
    public function infoFilterByUser()
    {
        return (string) $this->info->info->form->filterByUser;
    }

    /**
     * Gets the value of an attribute.
     */
    public function get($name)
    {
        $nameLanguage = $name . '_' . Language::active();
        $result = (isset($this->values[$name])) ? $this->values[$name] : '';
        $result = (isset($this->values[$nameLanguage])) ? $this->values[$nameLanguage] : $result;
        return $result;
    }

    /**
     * Check if an attribute exists or not.
     */
    public function attributeExists($attribute)
    {
        $match = $this->info->xpath("/object/attributes/attribute//name[.='" . $attribute . "']/..");
        return (isset($match[0]) || in_array($attribute, ['ord', 'created', 'modified'])) ? true : false;
    }

    /**
     * Gets the attribute information in the XML file.
     */
    public function attributeInfo($attribute)
    {
        $match = $this->info->xpath("/object/attributes/attribute//name[.='" . $attribute . "']/..");
        return (isset($match[0])) ? $match[0] : '';
    }

    /**
     * Gets the attribute information in the XML file using a refObject.
     */
    public function attributeInfoByRefObject($refObject)
    {
        $match = $this->info->xpath("/object/attributes/attribute//refObject[.='" . $refObject . "']/..");
        return (isset($match[0])) ? $match[0] : '';
    }

    /**
     * Returns all the attribute names.
     */
    public function attributeNames()
    {
        $list = [$this->primary, 'ord', 'created', 'modified'];
        foreach ($this->getAttributes() as $attribute) {
            $list[] = (string) $attribute->name;
        }
        return $list;
    }

    /**
     * Gets the label of an attribute, it works for attributes as selects of multiple objects.
     */
    public function label($attribute, $admin = false)
    {
        $info = $this->attributeInfo($attribute);
        if ((string) $info->type == 'autocomplete') {
            $refObject = (string) $info->form->refObject;
            $object = new $refObject;
            $object = $object->read($this->get($attribute));
            return ($admin) ? $object->getBasicInfoAdmin() : $object->getBasicInfo();
        } else {
            if ((string) $info->refObject != '') {
                $refObjectName = (string) $info->refObject;
                $refObject = new $refObjectName;
                $refObject = $refObject->read($this->get($attribute));
                return $refObject->getBasicInfo();
            } else {
                $values = $this->getValues($attribute, $admin);
                return (isset($values[$this->get($attribute)])) ? $values[$this->get($attribute)] : '';
            }
        }
    }

    /**
     * Gets the link to the file that the attribute points.
     */
    public function getFileLink($attributeName)
    {
        $file = $this->getFileUrl($attributeName);
        return ($file != '') ? '
            <a href="' . $file . '" target="_blank" class="button button_small">
                <i class="fa fa-file"></i>
                <span>' . __('view_file') . ' ' . substr($this->get($attributeName), 0, 30) . '</span>
            </a>
        ' : '';
    }

    /**
     * Gets the url to the file that the attribute points.
     */
    public function getFileUrl($attributeName)
    {
        $file = ASTERION_STOCK_URL . $this->className . 'Files/' . $this->get($attributeName);
        return (is_file(str_replace(ASTERION_STOCK_URL, ASTERION_STOCK_FILE, $file))) ? $file : '';
    }

    /**
     * Gets the base to the file that the attribute points.
     */
    public function getFile($attributeName)
    {
        $file = ASTERION_STOCK_FILE . $this->className . 'Files/' . $this->get($attributeName);
        return (is_file($file)) ? $file : '';
    }

    /**
     * Gets the HTML image that the attribute points.
     */
    public function getImage($attributeName, $version = '', $alternative = '', $modified = false)
    {
        $imageUrl = $this->getImageUrl($attributeName, $version);
        if ($imageUrl != '') {
            $imageUrlWebp = $imageUrl . '.webp';
            $imageFileWebp = str_replace(ASTERION_BASE_URL, ASTERION_BASE_FILE, $imageUrl) . '.webp';
            if (file_exists($imageFileWebp)) {
                return '<img src="' . $imageUrlWebp . '" alt="' . str_replace('"', '', $this->getBasicInfo()) . '" loading="lazy"/>';
            }
            $imageFile = str_replace(ASTERION_BASE_URL, ASTERION_BASE_FILE, $imageUrl);
            if (file_exists($imageFile)) {
                return '<img src="' . $imageUrl . '" alt="' . str_replace('"', '', $this->getBasicInfo()) . '" loading="lazy"/>';
            }
        }
        return $alternative;
    }

    /**
     * Gets the HTML image that the attribute points.
     */
    public function getImageWidth($attributeName, $version = '', $alternative = '', $modified = false, $altText = '')
    {
        $imageUrl = $this->getImageUrl($attributeName, $version);
        if ($imageUrl != '') {
            $imageUrlWebp = $imageUrl . '.webp';
            $imageFileWebp = str_replace(ASTERION_BASE_URL, ASTERION_BASE_FILE, $imageUrl) . '.webp';
            $imageFile = str_replace(ASTERION_BASE_URL, ASTERION_BASE_FILE, $imageUrl);
            $imageSize = @getimagesize($imageFile);
            $altTextFinal = ($altText != '') ? $altText : $this->getBasicInfo();
            $altTextFinal = str_replace('"', '', $altTextFinal);
            if (isset($imageSize[1])) {
                if (file_exists($imageFileWebp)) {
                    return '<img src="' . $imageUrlWebp . '" alt="' . $altTextFinal . '" width="' . $imageSize[0] . '" height="' . $imageSize[1] . '" loading="lazy"/>';
                }
                return '<img src="' . $imageUrl . '" alt="' . $altTextFinal . '" width="' . $imageSize[0] . '" height="' . $imageSize[1] . '" loading="lazy"/>';
            }
        }
        return $alternative;
    }

    /**
     * Gets the HTML image that the attribute points using the AMP version.
     */
    public function getImageAmp($attributeName, $version = '', $layout = 'responsive', $attributes = '', $alternative = '')
    {
        $imageUrl = $this->getImageUrl($attributeName, $version);
        $imageUrl = ($imageUrl != '') ? $imageUrl : $alternative;
        if ($imageUrl != '') {
            $imageFile = str_replace(ASTERION_BASE_URL, ASTERION_BASE_FILE, $imageUrl);
            if (file_exists($imageFile)) {
                $imageSize = @getimagesize($imageFile);
                if (isset($imageSize[1])) {
                    return '<amp-img ' . $attributes . ' src="' . $imageUrl . '" alt="' . str_replace('"', '', $this->getBasicInfo()) . '" width="' . $imageSize[0] . '" height="' . $imageSize[1] . '" layout="' . $layout . '"/>';
                }
            }
        }
    }

    /**
     * Gets the HTML image that the attribute points using the AMP version.
     */
    public function getImageAmpWebp($attributeName, $version = '', $layout = 'responsive', $attributes = '', $alternative = '')
    {
        $imageUrl = $this->getImageUrl($attributeName, $version);
        $imageUrl = ($imageUrl != '') ? $imageUrl : $alternative;
        if ($imageUrl != '') {
            $imageUrlWebp = $imageUrl . '.webp';
            $imageFileWebp = str_replace(ASTERION_BASE_URL, ASTERION_BASE_FILE, $imageUrl) . '.webp';
            if (!file_exists($imageFileWebp)) {
                return $this->getImageAmp($attributeName, $version, $layout, $attributes, $alternative);
            }
            $imageFile = str_replace(ASTERION_BASE_URL, ASTERION_BASE_FILE, $imageUrl);
            if (file_exists($imageFile)) {
                $imageSize = getimagesize($imageFile);
                return '
                    <amp-img ' . $attributes . ' src="' . $imageUrlWebp . '" alt="' . str_replace('"', '', $this->getBasicInfo()) . '" width="' . $imageSize[0] . '" height="' . $imageSize[1] . '" layout="' . $layout . '">
                        <amp-img fallback ' . $attributes . ' src="' . $imageUrl . '" alt="' . str_replace('"', '', $this->getBasicInfo()) . '" width="' . $imageSize[0] . '" height="' . $imageSize[1] . '" layout="' . $layout . '">
                        </amp-img>
                    </amp-img>';
            }
        }
    }

    /**
     * Gets the HTML image that exists and fits to an icon.
     */
    public function getImageIcon($attributeName)
    {
        $imageUrl = $this->getImage($attributeName, 'thumb');
        if ($imageUrl != '') {
            return $imageUrl;
        } else {
            $imageUrl = $this->getImage($attributeName, 'small');
            if ($imageUrl != '') {
                return $imageUrl;
            } else {
                $imageUrl = $this->getImage($attributeName, 'web');
                if ($imageUrl != '') {
                    return $imageUrl;
                }
            }
        }
    }

    /**
     * Gets the url of an image that the attribute points.
     */
    public function getImageUrl($attributeName, $version = '', $modified = false)
    {
        $version = ($version != '') ? '_' . strtolower($version) : '';
        $file = ASTERION_STOCK_FILE . $this->className . '/' . $this->get($attributeName) . '/' . $this->get($attributeName) . $version . '.jpg';
        if (is_file($file)) {
            return str_replace(ASTERION_STOCK_FILE, ASTERION_STOCK_URL, $file) . ($modified && ($this->get('modified') != '') ? '?v=' . Date::sqlInt($this->get('modified')) : '');
        }
    }

    /**
     * Gets the url of an image that the attribute points.
     */
    public function getImageUrlWebp($attributeName, $version = '', $modified = false)
    {
        $version = ($version != '') ? '_' . strtolower($version) : '';
        $file = ASTERION_STOCK_FILE . $this->className . '/' . $this->get($attributeName) . '/' . $this->get($attributeName) . $version . '.jpg.webp';
        if (is_file($file)) {
            return str_replace(ASTERION_STOCK_FILE, ASTERION_STOCK_URL, $file) . ($modified && ($this->get('modified') != '') ? '?v=' . Date::sqlInt($this->get('modified')) : '');
        }
        return $this->getImageUrl($attributeName, $version, $modified);
    }

    /**
     * Gets the url of an image using a class name.
     */
    public static function getImageUrlFromStock($className, $imageName, $version = '')
    {
        $version = ($version != '') ? '_' . strtolower($version) : '';
        $file = ASTERION_STOCK_FILE . $className . '/' . $imageName . '/' . $imageName . $version . '.jpg';
        if (is_file($file)) {
            return str_replace(ASTERION_STOCK_FILE, ASTERION_STOCK_URL, $file);
        }
    }

    /**
     * Function to save an image.
     */
    public function saveImage($fileImage, $fieldName)
    {
        $info = $this->getAttribute($fieldName);
        $fileName = Text::simpleUrlFileBase($this->id() . '_' . $fieldName);
        if (isset($info->fileFieldName)) {
            $extension = pathinfo($fileImage, PATHINFO_EXTENSION);
            $fileName = Text::simpleUrlFileBase($this->get((string) $info->fileFieldName) . '.' . $extension);
        }
        if (Image_File::saveImageObject($fileImage, $this->className, $fileName)) {
            $this->persistSimple($fieldName, $fileName);
        }
    }

    /**
     * Reload the object.
     */
    public function orderBy()
    {
        return (string) $this->info->info->form->orderBy;
    }

    /**
     * Sets the id of an object.
     */
    public function setId($id)
    {
        $this->values[$this->primary] = $id;
    }

    /**
     * Sets a value to an attribute of an object.
     */
    public function set($name, $value = '')
    {
        if (is_array($name)) {
            foreach ($name as $key => $item) {
                $this->set($key, $item);
            }
        } else {
            $this->values[$name] = $value;
        }
    }

    /**
     * Checks if the object has the attribute "created".
     */
    public function hasCreated()
    {
        return ((string) $this->info->info->sql->created == 'true');
    }

    /**
     * Checks if the object has the attribute "modified".
     */
    public function hasModified()
    {
        return ((string) $this->info->info->sql->modified == 'true');
    }

    /**
     * Checks if the object has the attribute "order".
     */
    public function hasOrd()
    {
        return ((string) $this->info->info->sql->order == 'true');
    }

    /**
     * Checks if the object has an auto-incremented id.
     */
    public function hasIdAutoIncrement()
    {
        if (!is_object($this->attributeInfo($this->primary))) {
            return false;
        }
        return ((string) $this->attributeInfo($this->primary)->type == 'id_autoincrement');
    }

    /**
     * Creates an instance of the UI object and returns a function to render.
     */
    public function showUi($functionName = 'Public', $parameters = [])
    {
        $render = 'render' . ucwords($functionName);
        $fileHtml = ASTERION_BASE_FILE . 'cache/' . $this->className . '/' . $render . '_' . $this->id() . '.htm';
        if (is_file($fileHtml)) {
            return file_get_contents($fileHtml);
        }
        $fileHtml = ASTERION_BASE_FILE . 'cache/' . $this->className . '/' . $render . '.htm';
        if (is_file($fileHtml)) {
            return file_get_contents($fileHtml);
        }
        $uiObjectName = $this->className . '_Ui';
        $uiObject = new $uiObjectName($this);
        return $uiObject->$render($parameters);
    }

    /**
     * Check if the values of the object are valid.
     */
    public function validate()
    {
        foreach ($this->getAttributes() as $attribute) {
            $error = $this->validateField($attribute);
            if (count($error) > 0) {
                $this->errors = array_merge($error, $this->errors);
            }
        }
        return $this->errors;
    }

    /**
     * Check if the values of certain attributes of the object are valid.
     */
    public function validateAttributes($attributeNames)
    {
        foreach ($attributeNames as $attributeName) {
            $error = $this->validateField($this->attributeInfo($attributeName));
            if (count($error) > 0) {
                $this->errors = array_merge($error, $this->errors);
            }
        }
        return $this->errors;
    }

    /**
     * Checks if an item is valid.
     */
    public function validateField($attribute, $required = '')
    {
        $error = [];
        $name = (string) $attribute->name;
        $required = ($required == '') ? (string) $attribute->required : $required;
        switch ($required) {
            case 'not_empty':
                if ((string) $attribute->language == 'true') {
                    foreach (Language::languages() as $language) {
                        if (!isset($this->values[$name . '_' . $language['id']]) || strlen(trim($this->values[$name . '_' . $language['id']])) == 0) {
                            $error[$name . '_' . $language['id']] = __('not_empty');
                        }
                    }
                } else {
                    if (!isset($this->values[$name]) || trim($this->values[$name]) == '') {
                        $error[$name] = __('not_empty');
                    }
                }
                break;
            case 'not_empty_point':
                if (!isset($this->values[$name . '_lat']) || trim($this->values[$name . '_lat']) == '') {
                    $error[$name] = __('not_empty');
                }
                if (!isset($this->values[$name . '_lng']) || trim($this->values[$name . '_lng']) == '') {
                    $error[$name] = __('not_empty');
                }
                break;
            case 'email':
                $error = array_merge($error, $this->validateField($attribute, 'not_empty'));
                if (!filter_var($this->values[$name], FILTER_VALIDATE_EMAIL)) {
                    $error[$name] = __('error_mail');
                }
                break;
            case 'code':
                $error = array_merge($error, $this->validateField($attribute, 'not_empty'));
                if (!filter_var($this->values[$name], FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-z0-9_-]+$/"]])) {
                    $error[$name] = __('error_code');
                }
                break;
            case 'password':
                if (!isset($this->values[$name]) || trim($this->values[$name]) == '') {
                    $error[$name] = __('not_empty');
                } else {
                    $errorPassword = Db_Validation::validatePassword($this->values[$name]);
                    if ($errorPassword != '') {
                        $error[$name] = $errorPassword;
                    }
                }
                break;
            case 'unique':
                $error = array_merge($error, $this->validateField($attribute, 'not_empty'));
                $whereId = ($this->id() != '') ? $this->primary . '!="' . $this->id() . '" AND ' : '';
                if ((string) $attribute->language == 'true') {
                    foreach (Language::languages() as $language) {
                        $existingObject = (new $this->className)->readFirst(['where' => $whereId . $name . '_' . $language['id'] . '="' . $this->values[$name . '_' . $language['id']] . '"']);
                        if ($existingObject->id() != '') {
                            $error[$name . '_' . $language['id']] = __('error_item_exists');
                        }
                    }
                } else {
                    $existingObject = (new $this->className)->readFirst(['where' => $whereId . $name . '="' . $this->values[$name] . '"']);
                    if ($existingObject->id() != '') {
                        $error[$name] = __('error_item_exists');
                    }
                }
                break;
            case 'unique_email':
                $error = array_merge($error, $this->validateField($attribute, 'email'));
                $error = array_merge($error, $this->validateField($attribute, 'unique'));
                break;
            case 'unique_code':
                $error = array_merge($error, $this->validateField($attribute, 'code'));
                $error = array_merge($error, $this->validateField($attribute, 'unique'));
                break;
        }
        return $error;
    }

    public function validateReCaptchaV3()
    {
        $name = 'g-recaptcha-response';
        if (!isset($this->values[$name]) || trim($this->values[$name]) == '') {
            $this->errors[$name] = __('not_empty');
        } else {
            $secret = (defined('ASTERION_RECAPTCHAV3_SITE_SECRET') && ASTERION_RECAPTCHAV3_SITE_SECRET != '') ? ASTERION_RECAPTCHAV3_SITE_SECRET : Parameter::code('recaptchav3_site_secret');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['secret' => $secret, 'response' => $this->values[$name]]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $jsonResponse = json_decode($response, true);
            if (!isset($jsonResponse['success']) || $jsonResponse['success'] != '1' || $jsonResponse['score'] < 0.5) {
                $errors[$name] = __('error_recaptcha');
            }
        }
        return $this->errors;
    }

}
