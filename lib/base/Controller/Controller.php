<?php
/**
 * @class Controller
 *
 * This is the "controller" component of the MVC pattern used by Asterion.
 * All of the controllers for the content objects extend from this class.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
abstract class Controller
{

    public $object;
    public $mode;
    public $type;
    public $objectType;
    public $action;
    public $id;
    public $extraId;
    public $addId;
    public $parameters;
    public $values;
    public $files;
    public $ui;
    public $title_page;
    public $meta_description;
    public $meta_keywords;
    public $meta_image;
    public $meta_url;
    public $head;
    public $layout;
    public $layout_page;
    public $content;
    public $content_top;
    public $content_bottom;
    public $bread_crumbs;
    public $login;
    public $message;
    public $message_error;
    public $menu_inside;

    /**
     * The general constructor for the controllers.
     * $GET : Array with the loaded $_GET values.
     * $POST : Array with the loaded $_POST values.
     * $FILES : Array with the loaded $_FILES values.
     */
    public function __construct($GET, $POST, $FILES)
    {
        $this->mode = isset($GET['mode']) ? $GET['mode'] : 'public';
        $this->type = isset($GET['type']) ? $GET['type'] : '';
        $this->objectType = snakeToCamel($this->type);
        $this->action = isset($GET['action']) ? $GET['action'] : 'list';
        $this->id = isset($GET['id']) ? $GET['id'] : '';
        $this->extraId = isset($GET['extraId']) ? $GET['extraId'] : '';
        $this->addId = isset($GET['addId']) ? $GET['addId'] : '';
        $this->parameters = isset($GET) ? $GET : [];
        $this->values = isset($POST) ? $POST : [];
        $this->files = isset($FILES) ? $FILES : [];
    }

    /**
     * Function to get the title for a page.
     * By default it uses the title defined in the Parameters.
     */
    public function getTitle()
    {
        $title_page = Parameter::code('meta_title_page');
        if (isset($this->title_page) && $this->title_page != '') {
            $title_page = (strlen($this->title_page) <= 35) ? $this->title_page . (isset($this->hide_title_page_appendix) ? '' : ' - ' . $title_page) : $this->title_page;
        }
        return str_replace('"', '', $title_page);
    }

    /**
     * Function to get the extra header tags for a page.
     * It can be used to load extra CSS or JS files.
     */
    public function getHead()
    {
        return (isset($this->head)) ? $this->head : '';
    }

    /**
     * Function to get the meta_description for a page.
     * By default it uses the meta_description defined in the Parameters.
     */
    public function getMetaDescription()
    {
        $meta_description = (isset($this->meta_description) && $this->meta_description != '') ? $this->meta_description : Parameter::code('meta_description');
        return str_replace('"', '', $meta_description);
    }

    /**
     * Function to get the meta-keywords for a page.
     * By default it uses the keywords defined in the Parameters.
     */
    public function getMetaKeywords()
    {
        $meta_keywords = (isset($this->meta_keywords)) ? $this->meta_keywords : Parameter::code('meta_keywords');
        return str_replace('"', '', $meta_keywords);
    }

    /**
     * Function to get the meta-image for a page.
     * By default it uses the ASTERION_LOGO defined in the configuration file.
     */
    public function getMetaImage()
    {
        $image = (isset($this->meta_image) && $this->meta_image != '') ? $this->meta_image : Parameter::getImageUrlFromCode('meta_image');
        if ($image != '') {
            $imageFile = str_replace(ASTERION_BASE_URL, ASTERION_BASE_FILE, $image);
            if (is_file($imageFile)) {
                $imageSize = getimagesize($imageFile);
                return '
                    <meta property="og:image" content="' . $image . '" />
                    <meta property="og:image:width" content="' . $imageSize[0] . '" />
                    <meta property="og:image:height" content="' . $imageSize[1] . '" />';
            }
        }
    }

    /**
     * Function to get the url address for a page.
     * A common use is the canonical URL of the current page.
     */
    public function getMetaUrl()
    {
        return (isset($this->meta_url)) ? $this->meta_url : Url::urlActual();
    }

    /**
     * Function to get the mode to render a page.
     * By default it uses the public method.
     * The render goes on the main index.php file.
     */
    public function getMode()
    {
        return (isset($this->mode)) ? $this->mode : 'public';
    }

    /**
     * Main function of the controller.
     * It works as a huge switch that uses the $action attribute defined in the URL.
     * By default this actions are built for the BackEnd since we usually do not modify
     * the objects in the FrontEnd. However for those situations we must override this
     * function in the child controller.
     */
    public function getContent()
    {
        $this->mode = 'admin';
        $this->object = new $this->objectType;
        $this->title_page = __((string) $this->object->info->info->form->title);
        $this->layout = (string) $this->object->info->info->form->layout;
        $this->layout_page = '';
        $this->menu_inside = $this->menuInside();
        $this->login = UserAdmin_Login::getInstance();
        $this->ui = new NavigationAdmin_Ui($this);
        LogAdmin::log($this->action . '_' . $this->objectType, $this->values);
        switch ($this->action) {
            default:
                header('Location: ' . url(''));
                exit();
                break;
            case 'list_items':
                /**
                 * This is the main action for the BackEnd. If we are in ASTERION_DEBUG mode
                 * it will create the table automatically.
                 */
                if ($this->getContentType() == 'json') {
                    $this->mode = 'json';
                    $response = ['status' => StatusCode::NOK, 'message_error' => __('connexion_error')];
                    if ($this->checkLoginAdmin()) {
                        $response = ['status' => StatusCode::OK, 'html' => $this->listAdminItems()];
                    }
                    return json_encode($response);
                } else {
                    $this->checkLoginAdmin();
                    $this->content = $this->listAdmin();
                    return $this->ui->render();
                    break;
                }
            case 'insert_view':
                /**
                 * This is the action that shows the form to insert a record.
                 */
                $this->checkLoginAdmin();
                $this->object = new $this->objectType;
                $form = new $this->object->formName();
                $this->content = $form->createFormInsertAdministrator();
                return $this->ui->render();
                break;
            case 'insert_view_ajax':
                /**
                 * This is the action that shows the form to insert a record.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('insert_error')];
                if ($this->checkLoginAdmin()) {
                    $form = new $this->object->formName();
                    $response = ['status' => StatusCode::OK, 'html' => $form->createFormInsertAdministrator()];
                }
                return json_encode($response);
                break;
            case 'insert_item':
                /**
                 * This is the action that inserts a record in the BackEnd.
                 * If the insertion is successful it shows a form to check the record,
                 * if not it creates a form with the errors to correct.
                 */
                $this->checkLoginAdmin();
                $object = new $this->objectType($this->values);
                $this->object->checkBeforeInsert();
                $persist = $object->persist();
                if ($persist['status'] == StatusCode::OK) {
                    header('Location: ' . url($this->type . '/insert_check/' . $persist['object']->id(), true));
                    exit();
                } else {
                    $this->message_error = __('errors_form');
                    $form = new $this->object->formName($persist['values'], $persist['errors']);
                    $this->content = $form->createFormInsertAdministrator();
                    return $this->ui->render();
                }
                break;
            case 'insert_item_ajax':
                /**
                 * This is the action that updates a record when updating it.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('insert_error')];
                if ($this->checkLoginAdmin()) {
                    $object = new $this->objectType($this->values);
                    $this->object->checkBeforeInsert();
                    $persist = $object->persist();
                    if ($persist['status'] == StatusCode::OK) {
                        $response = ['status' => StatusCode::OK];
                    } else {
                        $this->message_error = __('errors_form');
                        $form = new $this->object->formName($persist['values'], $persist['errors']);
                        $response = ['status' => StatusCode::NOK, 'form' => $form->createFormInsertAdministrator()];
                    }
                }
                return json_encode($response);
                break;
            case 'modify_view':
            case 'modify_view_check':
            case 'insert_check':
                /**
                 * This is the action that shows the form to check a record insertion.
                 */
                $this->checkLoginAdmin();
                $this->object = (new $this->objectType)->read($this->id);
                if ($this->object->id() != '') {
                    $this->menu_inside = $this->menuInside();
                    if (!$this->allowFilterByUser($this->object)) {
                        header('Location: ' . $this->object->urlListAdmin());
                    }
                    $form = new $this->object->formName($this->object->values);
                    $this->message = ($this->action == 'insert_check' || $this->action == 'modify_view_check') ? __('saved_form') : '';
                    $this->content = $form->createFormModifyAdministrator();
                    return $this->ui->render();
                }
                header('Location: ' . $this->object->urlListAdmin());
                exit();
                break;
            case 'modify_view_ajax':
                /**
                 * This is the action that shows the form to check a record insertion.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('update_error')];
                if ($this->checkLoginAdmin()) {
                    $this->object = (new $this->objectType)->read($this->id);
                    if ($this->object->id() != '' && $this->allowFilterByUser($this->object)) {
                        $form = new $this->object->formName($this->object->values);
                        $response = ['status' => StatusCode::OK, 'html' => $form->createFormModifyAdministrator()];
                    }
                }
                return json_encode($response);
                break;
            case 'modify_item':
                /**
                 * This is the action that updates a record when updating it.
                 */
                $this->checkLoginAdmin();
                $primary = $this->object->primary;
                $idPrimary = (isset($this->values[$primary])) ? $this->values[$primary] : '';
                $this->object = $this->object->read($idPrimary);
                if ($this->object->id() == '' || !$this->allowFilterByUser($this->object)) {
                    $this->message_error = __('item_does_not_exist');
                    return $this->ui->render();
                } else {
                    $this->object->setValues($this->values);
                    $this->object->checkBeforeModify();
                    $persist = $this->object->persist();
                    if ($persist['status'] == StatusCode::OK) {
                        header('Location: ' . url($this->type . '/modify_view_check/' . $persist['object']->id(), true));
                        exit();
                    } else {
                        $this->message_error = __('errors_form');
                        $form = new $this->object->formName($persist['values'], $persist['errors']);
                        $this->content = $form->createFormModifyAdministrator();
                        return $this->ui->render();
                    }
                }
                break;
            case 'modify_item_ajax':
                /**
                 * This is the action that updates a record when updating it.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('update_error')];
                if ($this->checkLoginAdmin()) {
                    $primary = $this->object->primary;
                    $this->object = $this->object->read($this->values[$primary]);
                    if ($this->object->id() == '' || !$this->allowFilterByUser($this->object)) {
                        $response = ['status' => StatusCode::NOK, 'message_error' => __('item_does_not_exist')];
                    } else {
                        $this->object->setValues($this->values);
                        $this->object->checkBeforeModify();
                        $persist = $this->object->persist();
                        if ($persist['status'] == StatusCode::OK) {
                            $response = ['status' => StatusCode::OK];
                        } else {
                            $form = new $this->object->formName($persist['values'], $persist['errors']);
                            $response = ['status' => StatusCode::OK, 'form' => $form->createFormModifyAdministrator()];
                        }
                    }
                }
                return json_encode($response);
                break;
            case 'upload_temp_image':
                /**
                 * This is the action that deletes a record.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('upload_temp_image_error')];
                if ($this->checkLoginAdmin()) {
                    $response = File::uploadTempImage($this->values);
                }
                return json_encode($response);
                break;
            case 'upload_temp_file':
                /**
                 * This is the action that deletes a record.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('upload_temp_file_error')];
                if ($this->checkLoginAdmin()) {
                    $response = File::uploadTempFile($this->values);
                }
                return json_encode($response);
                break;
            case 'delete_item':
                /**
                 * This is the action that deletes a record.
                 */
                $this->checkLoginAdmin();
                $this->object->read($this->id);
                if ($this->object->id() != '' && $this->allowFilterByUser($this->object)) {
                    $this->object->delete();
                }
                header('Location: ' . $this->object->urlListAdmin());
                exit();
                break;
            case 'delete_item_ajax':
                /**
                 * This is the action that deletes a record using ajax.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('delete_error')];
                if ($this->checkLoginAdmin()) {
                    $this->object->read($this->id);
                    if ($this->object->id() != '' && $this->allowFilterByUser($this->object)) {
                        $response = $this->object->delete();
                    }
                }
                return json_encode($response);
                break;
            case 'delete_image':
                /**
                 * This is the action that deletes a record.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('delete_image_error')];
                if ($this->checkLoginAdmin()) {
                    if ($this->id != '') {
                        $type = new $this->objectType();
                        $object = $type->read($this->id);
                        $directory = ASTERION_STOCK_FILE . $object->className . '/' . $this->extraId;
                        if (is_dir($directory)) {
                            if (File::deleteDirectory($directory)) {
                                $response = ['status' => StatusCode::OK];
                            }
                        }
                    }
                }
                return json_encode($response);
                break;
            case 'delete_file':
                /**
                 * This is the action that deletes a record.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('delete_file_error')];
                if ($this->checkLoginAdmin()) {
                    if ($this->id != '') {
                        $type = new $this->objectType();
                        $object = $type->read($this->id);
                        $file = ASTERION_STOCK_FILE . (isset($this->object->className) ? $this->object->className : 'Asterion') . 'Files/' . $this->extraId;
                        if (is_file($file) && unlink($file)) {
                            $response = ['status' => StatusCode::OK];
                        }
                    }
                }
                return json_encode($response);
                break;
            case 'activate_item':
            case 'deactivate_item':
                /**
                 * This is the action that deletes a record using ajax.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('update_error')];
                if ($this->checkLoginAdmin()) {
                    $this->object->read($this->id);
                    if ($this->object->id() != '') {
                        $active = ($this->action == 'activate_item') ? 1 : 0;
                        $this->object->persistSimple('active', $active);
                        $response = ['status' => StatusCode::OK, 'html' => $this->object->showUi('ActiveOptions')];
                    }
                }
                return json_encode($response);
                break;
            case 'sort_items':
                /**
                 * This is the action that saves the order of a list of records.
                 * It is used when sorting using the BackEnd.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('update_error')];
                if ($this->checkLoginAdmin()) {
                    $new_order = (isset($this->values['new_order'])) ? $this->values['new_order'] : [];
                    $this->object->updateOrder($new_order);
                    $response = ['status' => StatusCode::OK];
                }
                return json_encode($response);
                break;
            case 'sort_list_items':
                /**
                 * This is the action that changes the order of the list.
                 */
                $this->checkLoginAdmin();
                $info = substr($this->id, 4);
                if ($this->object->attributeExists($info)) {
                    $orderType = (substr($this->id, 0, 3) == 'asc') ? 'asc' : 'des';
                    Session::set('ord_' . $this->type, $orderType . '_' . $info);
                }
                header('Location: ' . $this->object->urlListAdmin());
                exit();
                break;
            case 'multiple_action':
                /**
                 * This is the action that deletes multiple records at once.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK, 'message_error' => __('update_error')];
                if ($this->checkLoginAdmin() && isset($this->values['list_ids']) && count($this->values['list_ids']) > 0) {
                    foreach ($this->values['list_ids'] as $id) {
                        $object = $this->object->read($id);
                        if ($this->allowFilterByUser($object)) {
                            switch ($this->id) {
                                case 'delete':
                                    $object->delete();
                                    break;
                                case 'activate':
                                    $object->persistSimple('active', '1');
                                    break;
                                case 'deactivate':
                                    $object->persistSimple('active', '0');
                                    break;
                            }
                            $response = ['status' => StatusCode::OK];
                        }
                    }
                }
                return json_encode($response);
                break;
            case 'autocomplete':
                /**
                 * This is the action that returns a json string with the records that match a search string.
                 * It is used for the autocomplete text input.
                 */
                $this->mode = 'json';
                $response = ['status' => StatusCode::NOK];
                if ($this->checkLoginAdmin()) {
                    $term = (isset($_GET['term'])) ? $_GET['term'] : '';
                    if ($term != '') {
                        $where = '';
                        $concat = '';
                        $attributes = explode('_', $this->id);
                        foreach ($attributes as $attribute) {
                            $attribute = $this->object->attributeInfo($attribute);
                            $name = (string) $attribute->name;
                            if (is_object($attribute) && $name != '') {
                                $where .= $name . ' LIKE "%' . $term . '%" OR ';
                                $concat .= $name . '," ",';
                            }
                        }
                        $where = substr($where, 0, -4);
                        $concat = 'CONCAT(' . substr($concat, 0, -5) . ')';
                        if ($where != '') {
                            $query = 'SELECT ' . $this->object->primary . ' as id, ' . $concat . ' as value
                                    FROM ' . $this->object->tableName . ' WHERE ' . $where . ' ORDER BY ' . $name . ' LIMIT 20';
                            $results = [];
                            foreach (Db::returnAll($query) as $result) {
                                $results[] = ['id' => $result['id'], 'value' => $result['value'], 'label' => $result['value']];
                            }
                            $response = ['status' => StatusCode::OK, 'results' => $results];
                        }
                    }
                }
                return json_encode($response);
                break;
            case 'search':
                /**
                 * This is the action that does the default "search" on a content object.
                 */
                $this->checkLoginAdmin();
                if ($this->id != '') {
                    $this->content = $this->listAdmin();
                    return $this->ui->render();
                } else {
                    if (isset($this->values['search']) && $this->values['search'] != '') {
                        $searchString = urlencode(html_entity_decode($this->values['search']));
                        header('Location: ' . url($this->type . '/search/' . $searchString, true));
                    } else {
                        header('Location: ' . $this->object->urlListAdmin());
                    }
                }
                break;
            case 'export_json':
                /**
                 * This is the action that exports the complete list of objects in JSON format.
                 */
                $this->mode = 'ajax';
                $query = 'SELECT * FROM ' . Db::prefixTable($this->type);
                $items = Db::returnAll($query);
                $file = $this->type . '.json';
                $options = ['content' => json_encode($items), 'contentType' => 'application/json'];
                File::download($file, $options);
                return '';
                break;
        }
    }

    /**
     * Render the search, controls and the list of the items in the administration area.
     */
    public function listAdmin()
    {
        $information = (string) $this->object->info->info->form->information;
        return '
            ' . (($information != '') ? '<div class="information">' . __($information) . '</div>' : '') . '
            ' . $this->searchForm() . '
            ' . $this->listAdminControlsTop() . '
            ' . $this->listAdminItems();
    }

    /**
     * Render the list of the items in the administration area.
     */
    public function listAdminItems()
    {
        if ((string) $this->object->info->info->form->group != '') {
            return $this->listGroupAdmin();
        }
        $search = $this->object->infoSearch();
        $searchQuery = $this->object->infoSearchQuery();
        $searchQueryCount = $this->object->infoSearchQueryCount();
        $searchValue = urldecode($this->id);
        $searchValue = str_replace('"', "", $searchValue);
        $searchValue = str_replace("'", "", $searchValue);
        $filterByUser = $this->object->infoFilterByUser();
        $sortableListClass = ($this->object->hasOrd()) ? 'sortable_list' : '';
        $ordSession = Session::get('ord_' . $this->type);
        $ordObject = substr($ordSession, 4);
        $ordObjectType = (substr($ordSession, 0, 3) == 'asc') ? 'ASC' : 'DESC';
        $values = [];
        $options['order'] = $this->orderField();
        if ($ordObject != '') {
            $orderInfo = $this->object->attributeInfo($ordObject);
            $orderInfoItem = (is_object($orderInfo) && (string) $orderInfo->language == "true") ? $ordObject . '_' . Language::active() : $ordObject;
            $orderInfoItem = ($orderInfoItem != '' && (is_object($orderInfo) && Db_ObjectType::isNumeric((string) $orderInfo->type))) ? 'ABS(' . $orderInfoItem . ')' : $orderInfoItem;
            $options['order'] = $orderInfoItem . ' ' . $ordObjectType;
        }
        $options['results'] = (int) $this->object->info->info->form->pager;
        $options['pagerTop'] = (isset($this->object->info->info->form->pagerTop) && (string) $this->object->info->info->form->pagerTop == 'true') ? true : false;
        $options['where'] = ($search != '' && $searchValue != '') ? str_replace('#TABLE', $this->object->tableName, str_replace('#SEARCH', $searchValue, $search)) : '';
        if (isset($this->object->info->info->form->showHide)) {
            $showHideField = (string) $this->object->info->info->form->showHide->field;
            $showHideLabel = (string) $this->object->info->info->form->showHide->label;
            $showHideFieldValue = (isset($this->parameters[$showHideField])) ? $this->parameters[$showHideField] : 1;
            if ($showHideFieldValue != 'null') {
                $values[$showHideField] = (isset($this->parameters[$showHideField])) ? $this->parameters[$showHideField] : 1;
                $showFieldWhere = $showHideField . '=:' . $showHideField;
                $options['where'] = ($options['where'] != '') ? ' AND ' . $showFieldWhere : $showFieldWhere;
            }
        }
        $filterByUserWhere = '1=1';
        if ($filterByUser != '' && $this->login->isConnected()) {
            $user = $this->login->user();
            if (!$user->managesPermissions()) {
                $filterByUserWhere = $filterByUser . '="' . $user->id() . '"';
                $options['where'] = ($options['where'] != '') ? '( ' . $options['where'] . ') AND ' : $options['where'];
                $options['where'] = $options['where'] . $filterByUserWhere;
            }
        }
        $replaceSearch = ['#TABLE', '#SEARCH', '#FILTER_BY_USER'];
        $replaceValues = [$this->object->tableName, $searchValue, $filterByUserWhere];
        $options['query'] = ($searchQuery != '' && $searchValue != '') ? str_replace($replaceSearch, $replaceValues, $searchQuery) : '';
        $options['queryCount'] = ($searchQueryCount != '' && $searchValue != '') ? str_replace($replaceSearch, $replaceValues, $searchQueryCount) : '';
        $list = new ListObjects($this->objectType, $options, $values);
        $multipleChoice = (count((array) $this->object->info->info->form->multipleActions->action) > 0);
        return '
            <div class="list_items reload_list_items list_items' . $this->type . ' ' . $sortableListClass . '"
                data-url="' . $this->object->urlListAdmin() . '"
                data-urlsort="' . url($this->type . '/sort_items/', true) . '">
                ' . $list->showListPager(['function' => 'Admin', 'message' => '<div class="message">' . __('no_items') . '</div>', 'pagerTop' => $options['pagerTop']], ['user_admin_type' => $this->login->get('type'), 'multipleChoice' => $multipleChoice]) . '
            </div>';
    }

    /**
     * Render the controls for the list of the administration area.
     */
    public function listAdminControlsTop()
    {
        $controlsTop = $this->multipleActionsControl() . $this->orderControl() . $this->showHideControl();
        return ($controlsTop != '') ? '<div class="controls_top">' . $controlsTop . '</div>' : '';
    }

    /**
     * Render the list of the items when is the case of a group.
     */
    public function listGroupAdmin()
    {
        $group = (string) $this->object->info->info->form->group;
        $items = $this->object->getValues($group, true);
        $listItems = '';
        $multipleChoice = (count((array) $this->object->info->info->form->multipleActions->action) > 0);
        foreach ($items as $key => $item) {
            $sortableListClass = ($this->object->hasOrd()) ? 'sortable_list' : '';
            $list = new ListObjects($this->objectType, [
                'where' => $group . '="' . $key . '"',
                'function' => 'Admin',
                'order' => $this->orderField(),
            ]);
            if (!$list->isEmpty() || !isset($this->object->info->info->form->groupHideEmpty) || (string)$this->object->info->info->form->groupHideEmpty != 'true') {
                $listItems .= '
                    <div class="line_admin_block">
                        <div class="line_admin_title">' . $item . '</div>
                        <div class="line_adminItems">
                            <div class="list_items ' . $sortableListClass . '"
                                data-urlsort="' . url($this->type . '/sort_items/', true) . '">
                                <div class="list_content">
                                    ' . $list->showList(['function' => 'Admin', 'message' => '<div class="message">' . __('no_items') . '</div>'], ['user_admin_type' => $this->login->get('type'), 'multipleChoice' => $multipleChoice]) . '
                                </div>
                            </div>
                        </div>
                    </div>';
            }

        }
        return '<div class="line_admin_blockWrapper">' . $listItems . '</div>';
    }

    /**
     * Check for the order field.
     */
    public function orderField()
    {
        $orderAttribute = (string) $this->object->info->info->form->orderBy;
        if ($orderAttribute != '') {
            $orderAttribute = explode(',', $orderAttribute);
            $orderAttribute = explode(' ', $orderAttribute[0]);
            $orderType = (isset($orderAttribute[1]) && $orderAttribute[1] == 'DESC') ? 'DESC' : 'ASC';
            $orderAttribute = $orderAttribute[0];
            $orderInfo = $this->object->attributeInfo($orderAttribute);
            $in = (is_object($orderInfo) && (string) $orderInfo->language == "true") ? $orderAttribute . '_' . Language::active() . ' ' . $orderType : $orderAttribute . ' ' . $orderType;
            return (is_object($orderInfo) && (string) $orderInfo->language == "true") ? $orderAttribute . '_' . Language::active() . ' ' . $orderType : $orderAttribute . ' ' . $orderType;
        }
    }

    /**
     * Render the order control.
     */
    public function orderControl()
    {
        $orderField = (string) $this->object->info->info->form->orderBy;
        $orderItems = explode(',', $orderField);
        if (count($orderItems) > 1 && $orderField != '') {
            $options = [];
            $selectedItem = Session::get('ord_' . $this->type);
            foreach ($orderItems as $orderItem) {
                $infoOrderItem = explode(' ', trim($orderItem));
                $options['asc_' . $infoOrderItem[0]] = __($infoOrderItem[0]);
                $options['des_' . $infoOrderItem[0]] = __($infoOrderItem[0]) . ' (' . __('reverse') . ')';
            }
            return '
                <div class="order_actions" data-url="' . url($this->type . '/sort_list_items/', true) . '">
                    <div class="order_actions_wrapper">
                        ' . FormField::show('select', ['label' => __('order_by'), 'name' => 'order_list', 'value' => $options, 'selected' => $selectedItem]) . '
                    </div>
                </div>';
        }
    }

    /**
     * Render the show/hide for certain fields.
     */
    public function showHideControl()
    {
        if (isset($this->object->info->info->form->showHide)) {
            $showHideField = (string) $this->object->info->info->form->showHide->field;
            $showHideLabel = (string) $this->object->info->info->form->showHide->label;
            $showHideFieldValue = (isset($this->parameters[$showHideField])) ? $this->parameters[$showHideField] : 1;
            return '
                <div class="showhide_control">
                    <div class="showhide_control_ins">
                        ' . (($showHideFieldValue != '1') ? '
                            <a class="showhide_control_button_active" href="' . $this->object->urlListAdmin() . '?' . $showHideField . '=1">
                                ' . __($showHideLabel . '_active') . '
                            </a>
                        ' : '') . '
                        ' . (($showHideFieldValue != '0') ? '
                            <a class="showhide_control_button_active" href="' . $this->object->urlListAdmin() . '?' . $showHideField . '=0">
                                ' . __($showHideLabel . '_inactive') . '
                            </a>
                        ' : '') . '
                        ' . (($showHideFieldValue != 'null') ? '
                            <a class="showhide_control_button_active" href="' . $this->object->urlListAdmin() . '?' . $showHideField . '=null">
                                ' . __($showHideLabel . '_all') . '
                            </a>
                        ' : '') . '
                    </div>
                </div>';
        }
    }

    /**
     * Render the multiple actions control.
     */
    public function multipleActionsControl()
    {
        $multipleActions = $this->object->info->info->form->multipleActions->action;
        if (isset($multipleActions) && count($multipleActions) > 0) {
            $multipleActionsOptions = '';
            foreach ($multipleActions as $multipleAction) {
                if (($multipleAction == 'activate' || $multipleAction == 'deactivate') && !$this->allowFilterByUser($this->object)) {
                    continue;
                }
                $multipleActionLabel = (string) $multipleAction;
                $icon = (isset($multipleAction['icon'])) ? $multipleAction['icon'] : '';
                $multipleActionsOptions .= '
                    <div class="multiple_action multiple_option"
                        data-url="' . url($this->type . '/multiple_action/' . $multipleActionLabel, true) . '">
                        <i class="fa fa-' . $icon . '"></i>
                        <span>' . __($multipleActionLabel . '_selected') . '</span>
                    </div>';
            }
            return '
                <div class="multiple_actions">
                    <div class="multiple_action_check_all">
                        ' . FormField::show('checkbox_icon', ['name' => 'checkbox_list']) . '
                    </div>
                    ' . $multipleActionsOptions . '
                </div>';
        }
    }

    /**
     * Render a search form for the object.
     */
    public function searchForm()
    {
        $search = $this->object->infoSearch();
        $searchQuery = $this->object->infoSearchQuery();
        $searchValue = urldecode($this->id);
        if ($search != '' || $searchQuery != '') {
            $fieldsSearch = FormField::show('text', ['name' => 'search', 'value' => $searchValue]);
            return '
                <div class="form_admin_search_wrapper">
                    ' . Form::createForm($fieldsSearch, ['action' => url($this->type . '/search', true), 'submit' => __('search'), 'class' => 'form_admin_search']) . '
                            ' . (($this->id != '') ? '
                            <a class="form_admin_search_back" href="' . $this->object->urlListAdmin() . '">' . __('view_all_items') . '</a>
                            <h2>' . __('results_for') . ': "' . $searchValue . '"' . '</h2>
                            ' : '') . '
                </div>';
        }
    }

    /**
     * Render the inside menu for certain actions.
     */
    public function menuInside()
    {
        $items = $this->menuInsideItems();
        return ($items != '') ? '<nav class="menu_simple">' . $items . '</nav>' : '';
    }

    /**
     * Render the items for the inside menu.
     */
    public function menuInsideItems()
    {
        $items = '';
        if (Permission::canInsert($this->type)) {
            if ($this->layout == 'modal') {
                $items = Ui::menuAdminInsideAjax($this->type . '/insert_view_ajax', 'plus', 'add');
            } else {
                $items = Ui::menuAdminInside($this->type . '/insert_view', 'plus', 'add');
            }
        }
        if (in_array($this->action, $this->menuInsideItemsListElements())) {
            $items .= Ui::menuAdminInside($this->type . '/list_items', 'list', 'view_list');
        }
        if ((string) $this->object->info->info->form->viewPublic == 'true') {
            $items .= Ui::menuAdminInside($this->object->url(), 'eye', 'view', true);
        }

        return $items;
    }

    /**
     * Return the array of actions that show the "list elements" icon.
     */
    public function menuInsideItemsListElements()
    {
        return ['insert_view', 'insert_view_ajax', 'insert_check', 'modify_view', 'modify_view_ajax', 'modify_view_check'];
    }

    /**
     * Functions to manage the permissions.
     */
    public function checkLoginAdmin($checkPermissions = true)
    {
        $this->login = UserAdmin_Login::getInstance();
        $this->login->checkLoginRedirect();
        if ($checkPermissions && !$this->login->user()->managesPermissions()) {
            $permissionsCheck = [
                'list_items' => 'permission_list_items',
                'upload_temp_image' => 'permission_list_items',
                'upload_temp_file' => 'permission_list_items',
                'search' => 'permission_list_items',
                'insert' => 'permission_insert',
                'insert_view' => 'permission_insert',
                'insert_view_ajax' => 'permission_insert',
                'insert_item' => 'permission_insert',
                'insert_item_ajax' => 'permission_insert',
                'insert_check' => 'permission_insert',
                'modify' => 'permission_modify',
                'modify_view' => 'permission_modify',
                'modify_view_ajax' => 'permission_modify',
                'modify_view_check' => 'permission_modify',
                'modify_item' => 'permission_modify',
                'modify_item_ajax' => 'permission_modify',
                'multiple_action' => 'permission_modify',
                'sort_save' => 'permission_modify',
                'delete' => 'permission_delete',
                'delete_item' => 'permission_delete',
                'delete_item_ajax' => 'permission_delete',
            ];
            $permissionCheck = (isset($permissionsCheck[$this->action])) ? $permissionsCheck[$this->action] : '';
            if ($permissionCheck == '') {
                return false;
            }
            $userAdminType = (new UserAdminType)->read($this->login->user()->get('id_user_admin_type'));
            $permission = (new Permission)->readFirst(['where' => 'object_name="' . $this->type . '" AND id_user_admin_type="' . $userAdminType->id() . '" AND ' . $permissionCheck . '="1"']);
            if ($permission->id() == '') {
                if ($this->mode == 'ajax' || $this->mode == 'json') {
                    return false;
                } else {
                    header('Location: ' . url('navigation_admin/permissions', true));
                    exit();
                }
            }
        }
        return true;
    }

    /**
     * Get the type of the request.
     */
    public function getContentType()
    {
        if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json') {
            return 'json';
        }
    }

    /**
     * Allow actions for specific object
     */
    public function allowFilterByUser($object)
    {
        $filterByUser = $object->infoFilterByUser();
        $user = $this->login->user();
        return ($filterByUser != '' && !$user->managesPermissions() && $user->id() != $object->get($filterByUser)) ? false : true;
    }

}
