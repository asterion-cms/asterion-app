<?php
/**
 * @class Ui
 *
 * This is the main class for the UserAdmin Interface.
 * It is used mainly to render HTML blocks for the different objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Ui
{

    /**
     * The constructor of the object.
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Render a div with the basic information.
     */
    public function renderPublic()
    {
        return '
            <div class="item item_' . $this->object->className . '">
                ' . $this->object->getBasicInfo() . '
            </div>';
    }

    /**
     * Render a link.
     */
    public function renderLink()
    {
        return $this->object->link();
    }

    /**
     * Render the simple information for a CSV format.
     */
    public function renderCsv()
    {
        return $this->object->getBasicInfo() . ',';
    }

    /**
     * Render the object to send it within an email.
     */
    public function renderEmail()
    {
        $content = '';
        foreach ($this->object->info->attributes->attribute as $item) {
            $label = (string) $item->label;
            $name = (string) $item->name;
            $type = (string) $item->type;
            switch (Db_ObjectType::baseType($type)) {
                case 'text':
                    $content .= '<strong>' . __($label) . '</strong>: ' . $this->object->get($name) . '<br/>';
                    break;
                case 'textarea':
                    $content .= '<strong>' . __($label) . '</strong>: ' . nl2br($this->object->get($name)) . '<br/>';
                    break;
                case 'select':
                case 'radio':
                    $content .= '<strong>' . __($label) . '</strong>: ' . $this->object->label($name) . '<br/>';
                    break;
                case 'date':
                case 'select_date':
                    $content .= '<strong>' . __($label) . '</strong>: ' . Date::sqlText($this->object->get($name), true) . '<br/>';
                    break;
                case 'checkbox':
                    $value = ($this->object->get($name) == 1) ? __('yes') : __('no');
                    $content .= '<strong>' . __($label) . '</strong>: ' . $value . '<br/>';
                    break;
            }
        }
        return '<p>' . $content . '</p>';
    }

    /**
     * Render the object for the admin area.
     */
    public function renderAdmin($options = [])
    {
        $class = (isset($options['class'])) ? $options['class'] : '';
        $permissions = Permission::getAll($this->object->className);
        $canModify = ($permissions['permission_modify'] == '1') ? $this->modify() : '';
        $canDelete = ($permissions['permission_delete'] == '1') ? $this->delete(true) : '';
        $canModify = (isset($options['cannotModify'])) ? '' : $canModify;
        $canDelete = (isset($options['cannotDelete'])) ? '' : $canDelete;
        $dataOrd = ($permissions['permission_modify'] == '1') ? 'data-id="' . $this->object->id() . '"' : '';
        $viewPublic = ((string) $this->object->info->info->form->viewPublic == 'true') ? $this->view() : '';
        $layout = 'line_adminLayout' . (string) $this->object->info->info->form->layout;
        return '
            <div class="line_admin line_admin_' . $this->object->snakeName . ' ' . $class . '" ' . $dataOrd . '>
                <div class="line_admin_wrapper">
                    ' . $this->renderAdminInside($options) . '
                    <div class="line_admin_cell line_admin_options">
                        ' . $viewPublic . '
                        ' . $canModify . '
                        ' . $canDelete . '
                    </div>
                </div>
            </div>';
    }

    /**
     * Render the information of the object for the admin area.
     */
    public function renderAdminInside($options = [])
    {
        $permissions = Permission::getAll($this->object->className);
        $canOrder = ($permissions['permission_modify'] == '1' && $this->object->hasOrd()) ? $this->order() : '';
        $canOrder = ($canOrder != '') ? '<div class="line_admin_cell line_adminOrder">' . $canOrder . '</div>' : '';
        $multipleChoice = '';
        if (isset($options['multipleChoice']) && $options['multipleChoice'] == true) {
            $multipleChoice .= '<div class="line_admin_cell line_admin_checkbox">
                                    ' . FormField::show('checkbox_icon', ['name' => $this->object->id()]) . '
                                </div>';
        }
        $label = ($permissions['permission_modify'] == '1' && !isset($options['cannotModify'])) ? $this->label(true) : $this->label(false);
        return '
            ' . $multipleChoice . '
            ' . $canOrder . '
            <div class="line_admin_cell line_admin_label">
                ' . $label . '
            </div>';
    }

    /**
     * Return a div with the active actions.
     */
    public function renderActiveOptions()
    {
        return '
            <div class="active_wrapper active_wrapper_' . $this->object->get('active') . '">
                <div class="active_option active_option_inactive" data-url="' . url($this->object->snakeName . '/activate_item/' . $this->object->id(), true) . '" title="' . __('activate') . '">
                    <i class="fas fa-eye-slash"></i>
                </div>
                <div class="active_option active_option_active" data-url="' . url($this->object->snakeName . '/deactivate_item/' . $this->object->id(), true) . '" title="' . __('deactivate') . '">
                    <i class="fas fa-eye"></i>
                </div>
            </div>';
    }

    /**
     * Render the object as a sitemap url.
     */
    public function renderSitemap($options = [])
    {
        $changefreq = isset($options['changefreq']) ? $options['changefreq'] : 'weekly';
        $priority = isset($options['priority']) ? $options['priority'] : '1';
        $xml = '
            <url>
                <loc>' . $this->object->url() . '</loc>
                <lastmod>' . date('Y-m-d') . '</lastmod>
                <changefreq>' . $changefreq . '</changefreq>
                <priority>' . $priority . '</priority>
            </url>';
        return Text::minimize($xml);
    }

    /**
     * Render the object as a sitemap url.
     */
    public function renderRss($options = [])
    {
        $xml = '
            <item>
                <title>' . $this->object->getBasicInfo() . '</title>
                <link>' . $this->object->url() . '</link>
                <description><![CDATA[' . $this->object->get('description') . ']]></description>
            </item>';
        return Text::minimize($xml);
    }

    /**
     * Function with the JsonLD header of an object.
     */
    public function renderJsonHeader()
    {
    }

    /**
     * Create a label in the admin using the information in the XML file.
     */
    public function label($canModify = false)
    {
        $login = UserAdmin_Login::getInstance();
        $user = $login->user();
        if (isset($this->object->info->info->form->templateItemAdmin)) {
            $html = (string) $this->object->info->info->form->templateItemAdmin->asXML();
            $html = str_replace('<templateItemAdmin>', '', $html);
            $html = str_replace('</templateItemAdmin>', '', $html);
            $attributes = Text::arrayWordsStarting('##', $html);
            foreach ($attributes as $attribute) {
                $attribute = str_replace('##', '', $attribute);
                $info = $this->object->attributeInfo($attribute);
                $infoType = (isset($info->type)) ? $info->type : '';
                $labelAttribute = $this->object->get($attribute);
                $html = str_replace('##' . $attribute, $labelAttribute, $html);
            }
            $attributes = Text::arrayWordsStarting('#', $html);
            foreach ($attributes as $attribute) {
                $attribute = str_replace('#', '', $attribute);
                $info = $this->object->attributeInfo($attribute);
                $infoType = (isset($info->type)) ? $info->type : '';
                $managesPermissions = (isset($info->managesPermissions)) ? (boolean) $info->managesPermissions : false;
                switch ($infoType) {
                    default:
                        $labelAttribute = $this->object->get($attribute);
                        break;
                    case 'linkid_autoincrement':
                        $refObjectName = (string) $info->refObject;
                        if ($refObjectName != '') {
                            $refObject = new $refObjectName;
                            $refObject = $refObject->read($this->object->get($attribute));
                            $labelAttribute = $refObject->getBasicInfoAdmin();
                        }
                        break;
                    case 'text_icon':
                        $labelAttribute = '<i class="' . $this->object->get($attribute) . '"></i>';
                        break;
                    case 'textarea_code':
                        $labelAttribute = htmlentities($this->object->get($attribute));
                        break;
                    case 'hidden_login':
                    case 'hidden_user_admin':
                        $userAdmin = (new UserAdmin)->read($this->object->get($attribute));
                        $labelAttribute = ($userAdmin->id() != '') ? $userAdmin->getBasicInfo() : '';
                        break;
                    case 'select':
                    case 'select_varchar':
                        $labelAttribute = $this->object->label($attribute);
                        break;
                    case 'checkbox':
                        $labelAttribute = '';
                        if ((string) $info->name == 'active') {
                            if (!$managesPermissions || ($managesPermissions && $user->managesPermissions())) {
                                $labelAttribute = $this->renderActiveOptions();
                            }
                        } else {
                            $labelAttribute = ($this->object->get($attribute) == '1') ? __('yes') : __('no');
                        }
                        break;
                    case 'date_text':
                        $date = $this->object->get($attribute);
                        $difference = Date::difference($date, date('Y-m-d'));
                        if ((string) $info->layout == 'publish_date' && $difference['difference'] < 0) {
                            $labelAttribute = '<i class="fa fa-exclamation-triangle"></i> ' . Date::sqlText($date) . ' (' . str_replace('#DAYS', $difference['days'], __('will_be_published_in_days')) . ')';
                        } else {
                            $labelAttribute = Date::sqlText($date);
                        }
                        break;
                    case 'file':
                    case 'file_drag':
                        if ((string) $info->mode == 'image' || (string) $info->layout == 'image') {
                            $labelAttribute = '<div class="image">' . $this->object->getImageIcon($attribute) . '</div>';
                        } else {
                            $labelAttribute = $this->object->getFileLink($attribute);
                        }
                        break;
                    case 'multiple_object':
                        if ((string) $info->mode == 'count') {
                            $refObject = (string) $info->refObject;
                            $linkAttribute = (string) $info->linkAttribute;
                            $refObjectInstance = new $refObject();
                            $count = $refObjectInstance->countResults(['where' => $linkAttribute . '=:id'], ['id' => $this->object->id()]);
                            $labelAttribute = str_replace('#RESULTS', '<strong>' . $count . '</strong>', __('list_total'));
                        }
                        break;
                }
                $html = str_replace('#' . $attribute, $labelAttribute, $html);
            }
            $wordsTranslate = Text::arrayWordsStarting('_', $html);
            foreach ($wordsTranslate as $wordTranslate) {
                $html = str_replace($wordTranslate, __(substr($wordTranslate, 1)), $html);
            }
        } else {
            $html = $this->object->getBasicInfoAdmin();
        }
        $html = ($canModify == '1') ? '<a href="' . $this->object->urlModify() . '">' . $html . '</a>' : $html;
        return '<div class="label">' . $html . '</div>';
    }

    /**
     * Render the label text when multiple is active.
     */
    public function labelMultiple($objectName, $objectNameConnector, $separator = ', ')
    {
        $object = new $objectName();
        $objectConnector = new $objectNameConnector();
        $query = 'SELECT DISTINCT o.*
                    FROM ' . $object->tableName . ' o
                    JOIN ' . $objectConnector->tableName . ' bo
                    ON (bo.' . $this->object->primary . '="' . $this->object->id() . '" AND bo.' . $object->primary . '=o.' . $object->primary . ')';
        $objects = $object->readListQuery($query);
        $html = '';
        foreach ($objects as $object) {
            $html .= $object->getBasicInfo() . $separator;
        }
        $html = substr($html, 0, -1 * strlen($separator));
        return $html;
    }

    /**
     * Return a div with the delete link.
     */
    public function delete($ajax = false)
    {
        return '
            <div class="icon_side icon_delete ' . (($ajax) ? 'icon_delete_item_ajax' : '') . '">
                <a href="' . $this->object->urlDelete($ajax) . '" data-confirm="' . __('are_you_sure_delete') . '">
                    <i class="fa fa-trash"></i>
                    <span>' . __('delete') . '</span>
                </a>
            </div>';
    }

    /**
     * Return a div with the modify link.
     */
    public function modify()
    {
        $layout = (string) $this->object->info->info->form->layout;
        if ($layout == 'modal') {
            return '
                <div class="icon_side icon_modify">
                    <div class="icon_side_ins" data-modal="' . htmlspecialchars($this->object->urlModifyAjax()) . '">
                        <i class="fa fa-edit"></i>
                        <span>' . __('modify') . '</span>
                    </div>
                </div>';
        }
        return '
            <div class="icon_side icon_modify">
                <a class="icon_side_ins" href="' . $this->object->urlModify() . '">
                    <i class="fa fa-edit"></i>
                    <span>' . __('modify') . '</span>
                </a>
            </div>';
    }

    /**
     * Return a div with the view public link.
     */
    public function view()
    {
        return '
            <div class="icon_side icon_view">
                <a href="' . $this->object->url() . '" target="_blank">
                    <i class="fa fa-eye"></i>
                    <span>' . __('view') . '</span>
                </a>
            </div>';
    }

    /**
     * Return a div with the move handle.
     */
    public function order()
    {
        return '
            <div class="icon_side icon_handle">
                <i class="fa fa-arrows-alt"></i>
            </div>';
    }

    public static function menuAdminInside($url, $icon, $label)
    {
        return '
            <div class="menu_simple_item menu_simple_item_' . $label . '">
                <a href="' . url($url, true) . '" class="menu_simple_item_ins">
                    <i class="fa fa-' . $icon . '"></i>
                    <span>' . __($label) . '</span>
                </a>
            </div>';
    }

    public static function menuAdminInsideAjax($url, $icon, $label)
    {
        return '
            <div class="menu_simple_item menu_simple_item_' . $label . '">
                <div class="menu_simple_item_ins" data-modal="' . url($url, true) . '">
                    <i class="fa fa-' . $icon . '"></i>
                    <span>' . __($label) . '</span>
                </div>
            </div>';
    }

    /**
     * Return a div with the share and print elements.
     */
    public function share($options = [])
    {
        $title = (isset($options['title'])) ? '<div class="share_options_title">' . $options['title'] . '</div>' : '';
        $content = '';
        $options['share'] = (isset($options['share'])) ? $options['share'] : [];
        foreach ($options['share'] as $share) {
            $shareKey = (is_array($share)) ? $share['key'] : $share;
            $shareIcon = (isset($share['icon'])) ? $share['icon'] : '';
            switch ($shareKey) {
                default:
                    $content .= $shareKey;
                    break;
                case 'facebook':
                    $link = 'http://www.facebook.com/sharer/sharer.php?u=' . urlencode($this->object->url());
                    $content .= '
                        <a href="' . $link . '" target="_blank" class="share_option share_option_facebook">
                            ' . (($shareIcon != '') ? $shareIcon : '<i class="fa fa-facebook-f"></i>') . '
                            <span>Facebook</span>
                        </a>';
                    break;
                case 'twitter':
                    $link = 'http://www.twitter.com/share?text=' . urlencode($this->object->getBasicInfo()) . '&url=' . urlencode($this->object->url());
                    $content .= '
                        <a href="' . $link . '" target="_blank" class="share_option share_option_twitter">
                            ' . (($shareIcon != '') ? $shareIcon : '<i class="fa fa-twitter"></i>') . '
                            <span>Twitter</span>
                        </a>';
                    break;
                case 'linkedin':
                    $link = 'https://www.linkedin.com/cws/share?url=' . urlencode($this->object->url());
                    $content .= '
                        <a href="' . $link . '" target="_blank" class="share_option share_option_linkedin">
                            ' . (($shareIcon != '') ? $shareIcon : '<i class="fa fa-linkedin"></i>') . '
                            <span>LinkedIn</span>
                        </a>';
                    break;
                case 'email':
                    $link = 'mailto:?body=' . urlencode($this->object->url());
                    $content .= '
                        <a href="' . $link . '" target="_blank" class="share_option share_option_email">
                            ' . (($shareIcon != '') ? $shareIcon : '<i class="fa fa-envelope"></i>') . '
                            <span>Email</span>
                        </a>';
                    break;
                case 'print':
                    $link = 'javascript:window.print()';
                    $content .= '
                        <a href="' . $link . '" class="share_option share_option_print">
                            ' . (($shareIcon != '') ? $shareIcon : '<i class="fa fa-print"></i>') . '
                            <span>' . __('print') . '</span>
                        </a>';
                    break;
            }
        }
        return '
            <div class="share_options">
                ' . $title . '
                <div class="share_options_buttons">
                    ' . $content . '
                </div>
            </div>';
    }

}
