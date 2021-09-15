<?php
/**
 * @class NavigationAdmin_Ui
 *
 * This class manages the UI for the NavigationAdmin object.
 * Here we render the template for the administration area.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class NavigationAdmin_Ui extends Ui
{

    /**
     * Render the page using different layouts
     */
    public function render()
    {
        $layout_page = (isset($this->object->layout_page)) ? $this->object->layout_page : '';
        $title = (isset($this->object->title_page) && $this->object->title_page != '') ? '<h1>' . $this->object->title_page . '</h1>' : '';
        $message = (isset($this->object->message) && $this->object->message != '') ? $this->object->message : Session::getFlashInfo();
        $message_alert = (isset($this->object->message_alert) && $this->object->message_alert != '') ? $this->object->message_alert : Session::getFlashAlert();
        $message_error = (isset($this->object->message_error) && $this->object->message_error != '') ? $this->object->message_error : Session::getFlashError();
        $message = ($message != '') ? '<div class="message">' . $message . '</div>' : '';
        $message_alert = ($message_alert != '') ? '<div class="message message_alert">' . $message_alert . '</div>' : '';
        $message_error = ($message_error != '') ? '<div class="message message_error">' . $message_error . '</div>' : '';
        $menu_inside = (isset($this->object->menu_inside)) ? $this->object->menu_inside : '';
        $content = (isset($this->object->content)) ? $this->object->content : '';
        switch ($layout_page) {
            default:
                return '
                    <div class="content_wrapper content_wrapper-' . $this->object->type . '">
                        ' . $this->header() . '
                        <div class="content_ins">
                            <div class="content_menu">
                                ' . $this->renderMenu() . '
                            </div>
                            <div class="content_ins_wrapper">
                                <div class="content">
                                    <div class="content_top">
                                        <div class="content_top_left">
                                            ' . $title . '
                                        </div>
                                        <div class="content_top_right">
                                            ' . $menu_inside . '
                                        </div>
                                    </div>
                                    ' . $message_error . '
                                    ' . $message_alert . '
                                    ' . $message . '
                                    ' . $content . '
                                    ' . $this->footer() . '
                                </div>
                            </div>
                        </div>
                    </div>
                    ' . $this->jsInfo();
                break;
            case 'simple':
                return '
                    <div class="content_wrapper content_wrapper-' . $this->object->type . '">
                        ' . $this->header() . '
                        <div class="content_' . $layout_page . '">
                            ' . $message_error . '
                            ' . $message_alert . '
                            ' . $message . '
                            ' . $content . '
                            ' . $this->footer() . '
                        </div>
                    </div>
                    ' . $this->jsInfo();
                break;
            case 'clear':
                return '
                    <div class="content_wrapper content_wrapper-' . $this->object->type . '">
                        ' . $this->headerSimple() . '
                        <div class="content_' . $layout_page . '">
                            ' . $title . '
                            ' . $message_error . '
                            ' . $message_alert . '
                            ' . $message . '
                            ' . $content . '
                        </div>
                    </div>
                    ' . $this->jsInfo();
                break;
        }
    }

    /**
     * Render the header for the page
     */
    public function header()
    {
        return '
            <header class="header_wrapper">
                <div class="header_ins">
                    <div class="header_left">
                        <div class="logo">
                            <a href="' . url('', true) . '">' . Parameter::code('meta_title_page') . '</a>
                        </div>
                    </div>
                    <div class="header_right">
                        ' . Language_Ui::showLanguages(true) . '
                        ' . UserAdmin_Ui::infoHtml() . '
                    </div>
                </div>
            </header>';
    }

    /**
     * Render the simple header for the page
     */
    public function headerSimple($complete = true)
    {
        return '
            <header class="header_wrapper">
                <div class="header_ins">
                    <div class="logo"><span>' . ASTERION_TITLE . '</span></div>
                </div>
            </header>';
    }

    /**
     * Render the footer for the page
     */
    public function footer()
    {
        return '
            <footer class="footer">
                ' . HtmlSectionAdmin::show('footer') . '
            </footer>';
    }

    /**
     * Render the menu for the page based on the user admin type.
     */
    public function renderMenu()
    {
        $this->login = UserAdmin_Login::getInstance();
        $this->user_admin_type = (new UserAdminType)->read($this->login->get('id_user_admin_type'));
        if ($this->user_admin_type->id() != '') {
            $menuItems = '';
            $menuItemsBase = $this->renderMenuObjects(File::scanDirectoryObjectsBase(), 'menu_side_item_base');
            $menuItems .= ($menuItemsBase != '') ? '
                <div class="menu_side_block">
                    <div class="menu_side_block_title">' . __('site') . '</div>
                    <div class="menu_side_block_items">' . $menuItemsBase . '</div>
                </div>' : '';
            $menuItemsApp = $this->renderMenuObjects(File::scanDirectoryObjectsApp(), 'menu_side_item_app');
            $menuItems .= ($menuItemsApp != '') ? '
                <div class="menu_side_block">
                    <div class="menu_side_block_title">' . __('application') . '</div>
                    <div class="menu_side_block_items">' . $menuItemsApp . '</div>
                </div>' : '';
            if ($this->user_admin_type->get('manages_permissions') == '1') {
                $menuItems .= '
                    <div class="menu_side_block">
                        <div class="menu_side_block_title">' . __('development') . '</div>
                        <div class="menu_side_block_items">
                            <div class="menu_side_item menu_side_item_admin">
                                <a href="' . url('language/list_items', true) . '">
                                    <i class="fa fa-language"></i>
                                    <span>' . __('languages') . '</span>
                                </a>
                            </div>
                            <div class="menu_side_item menu_side_item_admin">
                                <a href="' . url('translation/list_items', true) . '">
                                    <i class="fa fa-comment-alt"></i>
                                    <span>' . __('translations') . '</span>
                                </a>
                            </div>
                            <div class="menu_side_item menu_side_item_admin">
                                <a href="' . url('permission/list_items', true) . '">
                                    <i class="fa fa-users"></i>
                                    <span>' . __('permissions') . '</span>
                                </a>
                            </div>
                            <div class="menu_side_item menu_side_item_admin">
                                <a href="' . url('backup', true) . '">
                                    <i class="fa fa-download"></i>
                                    <span>' . __('backup') . '</span>
                                </a>
                            </div>
                            <div class="menu_side_item menu_side_item_admin">
                                <a href="' . url('cache', true) . '">
                                    <i class="fa fa-download"></i>
                                    <span>' . __('cache') . '</span>
                                </a>
                            </div>
                            <div class="menu_side_item menu_side_item_admin">
                                <a href="' . url('log_admin/list_items', true) . '">
                                    <i class="fa fa-laptop"></i>
                                    <span>' . __('logs') . '</span>
                                </a>
                            </div>
                        </div>
                    </div>';
            }
            return '
                <nav class="menu_side">
                    ' . $menuItems . '
                    <div class="menu_side_item menu_side_item_logout">
                        <a href="' . url('user_admin/logout', true) . '">
                            <i class="fa fa-power-off"></i>
                            <span>' . __('logout') . '</span>
                        </a>
                    </div>
                </nav>';
        }
    }

    /**
     * Render the menu for a list of objects.
     */
    public function renderMenuObjects($objectNames, $class)
    {
        $html = '';
        $menuItems = [];
        foreach ($objectNames as $objectName) {
            $object = new $objectName();
            $objectHidden = (string) $object->info->info->form->hiddenAdminMenu;
            $objectGroupMenu = (string) $object->info->info->form->groupMenu;
            $objectGroupMenu = ($objectGroupMenu != '') ? $objectGroupMenu : '';
            if ($objectHidden != 'true') {
                if (!isset($menuItems[$objectGroupMenu])) {
                    $menuItems[$objectGroupMenu] = [];
                }
                array_push($menuItems[$objectGroupMenu], [
                    'name' => (string) $object->info->name,
                    'icon' => ((string) $object->info->info->form->icon != '') ? $object->info->info->form->icon : 'database',
                    'title' => __((string) $object->info->info->form->title),
                ]);
            }
        }
        ksort($menuItems);
        foreach ($menuItems as $menuItemGroupKey => $menuItemGroup) {
            usort($menuItemGroup, function ($a, $b) {return strcmp($a['title'], $b['title']);});
            $htmlGroup = '';
            foreach ($menuItemGroup as $menuItem) {
                $permission = (new Permission)->readFirst(['where' => 'object_name="' . $menuItem['name'] . '" AND id_user_admin_type="' . $this->user_admin_type->id() . '"']);
                if ($this->user_admin_type->get('manages_permissions') == '1' || $permission->get('permission_list_items') == '1') {
                    $htmlGroup .= '
                        <div class="menu_side_item menu_side_item-' . $menuItem['name'] . ' ' . $class . '">
                            <a href="' . url(camelToSnake($menuItem['name']) . '/list_items', true) . '">
                                <i class="fa fa-' . $menuItem['icon'] . '"></i>
                                <span>' . __($menuItem['title']) . '</span>
                            </a>
                        </div>';
                }
            }
            $html .= ($htmlGroup != '' && $menuItemGroupKey != '') ? '
                <div class="menu-side-wrapper">
                    <div class="menu-side-title">' . __($menuItemGroupKey) . '</div>
                    ' . $htmlGroup . '
                </div>' : $htmlGroup;
        }
        return $html;
    }

    public function jsInfo()
    {
        $info = [
            'base_url' => ASTERION_LOCAL_URL,
            'base_file' => ASTERION_LOCAL_FILE,
            'app_url' => ASTERION_APP_URL,
            'app_folder' => APP_FOLDER,
            'site_url' => url(''),
            'lang' => Language::active(),
        ];
        $editorialCssFile = ASTERION_BASE_FILE . 'visual/css/stylesheets/editorial.css';
        if (is_file($editorialCssFile)) {
            $info['editorial_css'] = str_replace(ASTERION_BASE_FILE, ASTERION_BASE_URL, $editorialCssFile);
        }
        $variablesSassFile = ASTERION_BASE_FILE . 'visual/css/sass/_variables.scss';
        if (is_file($variablesSassFile)) {
            $sassFileLines = File::textFileToArray($variablesSassFile);
            // Font families
            $fontFamilies = '';
            $fontFamiliesArray = array_filter($sassFileLines, function ($value, $key) {return (strpos($value, '$fontFamily') !== false);}, ARRAY_FILTER_USE_BOTH);
            foreach ($fontFamiliesArray as $fontFamily) {
                $fontFamily = explode(':', $fontFamily);
                $fontFamilies .= (isset($fontFamily[1])) ? $fontFamily[1] . ' ' : '';
            }
            $info['font_families'] = $fontFamilies;
            // Font sizes
            $fontSizes = '';
            $fontSizesArray = array_filter($sassFileLines, function ($value, $key) {return (strpos($value, '$fontSize') !== false);}, ARRAY_FILTER_USE_BOTH);
            foreach ($fontSizesArray as $fontSize) {
                $fontSize = explode(':', $fontSize);
                $fontSizes .= (isset($fontSize[1])) ? $fontSize[1] . ' ' : '';
            }
            $info['font_sizes'] = $fontSizes;
            // Colors
            $colors = [];
            $colorsArray = array_filter($sassFileLines, function ($value, $key) {return (strpos($value, '$color') !== false);}, ARRAY_FILTER_USE_BOTH);
            foreach ($colorsArray as $color) {
                $color = explode(':', $color);
                if (isset($color[1])) {
                    $colors[] = trim(str_replace(';', '', str_replace('#', '', $color[1])));
                }
            }
            if (count($colors) > 0) {
                $info['colors'] = implode(',', $colors);
            }
        }
        // Templates
        $templatesFile = ASTERION_BASE_FILE . 'libjs/ckeditor_templates.js';
        if (is_file($templatesFile)) {
            $info['ckeditor_templates'] = str_replace(ASTERION_BASE_FILE, ASTERION_BASE_URL, $templatesFile);
        }
        return '<div class="js_info" data-info="' . htmlspecialchars(json_encode($info)) . '"></div>';
    }

}
