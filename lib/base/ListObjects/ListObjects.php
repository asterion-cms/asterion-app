<?php
/**
 * @class ListObjects
 *
 * This is a helper class to create and render lists of objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class ListObjects
{

    public $object;
    public $object_name;
    public $options;
    public $values;
    public $message;
    public $query;
    public $queryCount;
    public $queryValues;
    public $results;
    public $list;
    public $page;
    public $pagerHtml;
    public $countTotal;

    /**
     * The constructor of the object.
     */
    public function __construct($objectName, $options = [], $values = [])
    {
        $this->object_name = $objectName;
        $this->object = new $objectName();
        $this->options = $options;
        $this->values = $values;
        $this->message = (isset($options['message'])) ? $options['message'] : '';
        $this->query = (isset($options['query'])) ? $options['query'] : '';
        $this->queryCount = (isset($options['queryCount'])) ? $options['queryCount'] : '';
        $this->queryValues = (isset($options['queryValues'])) ? $options['queryValues'] : [];
        $this->results = (isset($this->options['results']) && $this->options['results'] > 0) ? intval($this->options['results']) : '';
        $this->page = (isset($options['page'])) ? $options['page'] : null;
        if (!isset($this->options['dontPopulate'])) {
            $this->populate();
        }
    }

    /**
     * Create a list of objects from an array.
     */
    static public function createFromArray($objectName, $array)
    {
        $list = new ListObjects($objectName, ['dontPopulate' => true]);
        $list->list = $array;
        $list->results = count($array);
        return $list;
    }

    /**
     * Gets an attribute value.
     */
    public function get($value)
    {
        return (isset($this->$value)) ? $this->$value : '';
    }

    /**
     * Get the first element of the list.
     */
    public function first()
    {
        return (count($this->list) > 0) ? $this->list[0] : '';
    }

    /**
     * Get the number of items in the actual page.
     */
    public function count()
    {
        return (count($this->list));
    }

    /**
     * Count the total number of elements in the list.
     */
    public function countTotal()
    {
        if (!isset($this->countTotal)) {
            if ($this->queryCount != '') {
                $result = Db::returnSingle($this->queryCount);
                $this->countTotal = $result['count_results'];
            } else {
                if (isset($this->options['fields']) && is_array($this->options['fields'])) {
                    $optionsCount = $this->options;
                    $optionsCount['fields'] = 'DISTINCT ' . (new $this->options['fields'][0])->tableName . '.' . (new $this->options['fields'][0])->primary;
                    $this->countTotal = $this->object->countResults($optionsCount, $this->values);
                } else {
                    $this->countTotal = $this->object->countResults($this->options, $this->values);
                }
            }
        }
        return $this->countTotal;
    }

    /**
     * Check if the list is empty.
     */
    public function isEmpty()
    {
        return (count($this->list) > 0) ? false : true;
    }

    /**
     * Populate the list.
     */
    public function populate()
    {
        $page = $this->page() - 1;
        $query = $this->query;
        if ($query != '') {
            if ($this->results != '') {
                $query .= ' LIMIT ' . ($page * $this->results) . ', ' . $this->results;
            }
            $this->list = $this->object->readListQuery($query, $this->queryValues);
        } else {
            if ($this->results != '') {
                $this->options['limit'] = ($page * $this->results) . ', ' . $this->results;
            }
            $this->list = $this->object->readList($this->options, $this->values);
        }
    }

    /**
     * Render a pager for the list.
     */
    public function pager($options = [])
    {
        if (!isset($this->pagerHtml)) {
            $this->pagerHtml = '';
            $page = $this->page();
            $delta = (isset($options['delta'])) ? intval($options['delta']) : 5;
            $midDelta = ceil($delta / 2);
            if ($this->results > 0 && $this->countTotal() > $this->results) {
                $totalPages = $this->totalPages();
                if ($totalPages <= $delta) {
                    //The number of pages is equal or less than delta
                    $listFrom = 0;
                    $listTo = $totalPages - 1;
                    $listStart = false;
                    $listEnd = false;
                } else {
                    if ($page < $midDelta + 1) {
                        //The first pages of the list
                        $listFrom = 0;
                        $listTo = $delta;
                        $listStart = false;
                        $listEnd = ($totalPages > $delta + 1) ? true : false;
                    } else {
                        if ($page + $midDelta >= $totalPages - 1) {
                            //The last pages of the list
                            $listFrom = $totalPages - $delta;
                            $listTo = $totalPages - 1;
                            $listStart = true;
                            $listEnd = false;
                        } else {
                            //The middle pages of the list
                            $listFrom = $page - $midDelta;
                            $listTo = $page + $midDelta;
                            $listStart = true;
                            $listEnd = true;
                        }
                    }
                }
                $html = '';
                for ($i = $listFrom; $i <= $listTo; $i++) {
                    $class = ($i + 1 == $page) ? 'pager_active' : 'pager';
                    $class = ($i == 0 && $page == 0) ? 'pager_active' : $class;
                    $html .= '<div class="' . $class . '">
                                <a href="' . Url::urlPage($i + 1) . '">' . ($i + 1) . '</a>
                            </div>';
                }
                $htmlListStart = '';
                if ($listStart) {
                    $htmlListStart = '<div class="pager pagerStart">
                                        <a href="' . Url::urlPage(1) . '">1</a>
                                    </div>
                                    <div class="pager pagerStart"><span>...</span></div>';
                };
                $htmlListEnd = '';
                if ($listEnd) {
                    $htmlListEnd = '<div class="pager pagerEnd"><span>...</span></div>
                                    <div class="pager pagerEnd">
                                        <a href="' . Url::urlPage($totalPages) . '">' . $totalPages . '</a>
                                    </div>';
                };
                $this->pagerHtml = '<div class="pager_all">
                                        ' . $htmlListStart . '
                                        ' . $html . '
                                        ' . $htmlListEnd . '
                                    </div>';
            }
        }
        return $this->pagerHtml;
    }

    /**
     * Show the list using the Ui object.
     */
    public function showList($options = [], $parameters = [])
    {
        $sizeList = count($this->list);
        $classContainer = (isset($options['classContainer'])) ? $options['classContainer'] : '';
        $message = (isset($options['message'])) ? $options['message'] : $this->message;
        $function = (isset($this->options['function'])) ? $this->options['function'] : 'Public';
        $function = (isset($options['function'])) ? $options['function'] : $function;
        $middle = (isset($options['middle'])) ? $options['middle'] : '';
        $middleRepetitions = (isset($options['middleRepetitions'])) ? intval($options['middleRepetitions']) + 1 : 2;
        $html = '';
        if ($sizeList > 0) {
            $middleRepetitions = ceil($sizeList / $middleRepetitions);
            $counter = 0;
            foreach ($this->list as $item) {
                $itemUiName = $this->object_name . '_Ui';
                $functionName = 'render' . ucwords($function);
                $itemUi = new $itemUiName($item);
                if ($counter > 0 && $middleRepetitions > 0 && $middle != '' && $sizeList > $middleRepetitions && ($counter % $middleRepetitions) == 0) {
                    $html .= $middle;
                }
                $parameters['counter'] = $counter + 1;
                $html .= $itemUi->$functionName($parameters);
                $counter++;
            }
        } else {
            $html = $message;
        }
        return ($classContainer != '') ? '<div class="' . $classContainer . '">' . $html . '</div>' : $html;
    }

    /**
     * Returns the list with a pager on top and another in the bottom.
     */
    public function showListPager($options = [], $parameters = [])
    {
        $pager = $this->pager($options);
        $showResults = (isset($options['showResults'])) ? $options['showResults'] : true;
        $list_results = ($showResults) ? '<div class="list_results">' . str_replace('#RESULTS', $this->countTotal(), __('list_total')) . '</div>' : '';
        return '<div class="list_wrapper">
                    ' . $list_results . '
                    ' . ((isset($options['pagerTop']) && $options['pagerTop']) ? $pager : '') . '
                    <div class="list_content">
                        ' . $this->showList($options, $parameters) . '
                    </div>
                    ' . $pager . '
                </div>';
    }

    /**
     * Returns actual page
     */
    public function page()
    {
        if (isset($this->page) && $this->page) {
            return $this->page;
        }
        $pageUrl = (__('page_url_string') != 'page_url_string') ? __('page_url_string') : ASTERION_PAGER_URL_STRING;
        return (isset($_GET[$pageUrl])) ? intval($_GET[$pageUrl]) : 1;
    }

    /**
     * Return the total number of pages
     */
    public function totalPages()
    {
        return ceil($this->countTotal() / $this->results);
    }

    /**
     * Return the meta tags next and previous
     */
    public function metaNavigation()
    {
        $page = $this->page();
        return '
            ' . (($page < $this->totalPages()) ? '<link rel="next" href="' . Url::urlPage($page + 1) . '"/>' : '') . '
            ' . (($page > 1) ? '<link rel="prev" href="' . Url::urlPage($page - 1) . '"/>' : '');
    }

}
