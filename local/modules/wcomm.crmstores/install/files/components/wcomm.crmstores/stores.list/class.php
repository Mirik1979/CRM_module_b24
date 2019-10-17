<?php
defined('B_PROLOG_INCLUDED') || die;

use Wcomm\CrmStores\Entity\StoreTable;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserTable;
use Bitrix\Main\Grid;
use Bitrix\Main\UI\Filter;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;


class CWcommCrmStoresStoresListComponent extends CBitrixComponent
{
    const GRID_ID = 'CRMSTORES_LIST';
    const SORTABLE_FIELDS = array('ID', 'NAME', 'ASSIGNED_BY_ID', 'ADDRESS');
    const FILTERABLE_FIELDS = array('ID', 'NAME', 'ASSIGNED_BY_ID', 'ADDRESS');
    const SUPPORTED_ACTIONS = array('delete');
    const SUPPORTED_SERVICE_ACTIONS = array('GET_ROW_COUNT');

    private static $headers;
    private static $filterFields;
    private static $filterPresets;

    public function __construct(CBitrixComponent $component = null)
    {
        global $USER;

        parent::__construct($component);

        self::$headers = array(
            array(
                'id' => 'ID',
                'name' => Loc::getMessage('CRMSTORES_HEADER_ID'),
                'sort' => 'ID',
                'first_order' => 'desc',
                'type' => 'int',
            ),
            array(
                'id' => 'NAME',
                'name' => Loc::getMessage('CRMSTORES_HEADER_NAME'),
                'sort' => 'NAME',
                'default' => true,
            ),
            array(
                'id' => 'ASSIGNED_BY',
                'name' => Loc::getMessage('CRMSTORES_HEADER_ASSIGNED_BY'),
                'sort' => 'ASSIGNED_BY_ID',
                'default' => true,
            ),
            array(
                'id' => 'ADDRESS',
                'name' => Loc::getMessage('CRMSTORES_HEADER_ADDRESS'),
                'sort' => 'ADDRESS',
                'default' => true,
            ),
        );

        self::$filterFields = array(
            array(
                'id' => 'ID',
                'name' => Loc::getMessage('CRMSTORES_FILTER_FIELD_ID')
            ),
            array(
                'id' => 'NAME',
                'name' => Loc::getMessage('CRMSTORES_FILTER_FIELD_NAME'),
                'default' => true,
            ),
            array(
                'id' => 'ASSIGNED_BY_ID',
                'name' => Loc::getMessage('CRMSTORES_FILTER_FIELD_ASSIGNED_BY'),
                'type' => 'custom_entity',
                'params' => array(
                    'multiple' => 'Y'
                ),
                'selector' => array(
                    'TYPE' => 'user',
                    'DATA' => array(
                        'ID' => 'ASSIGNED_BY',
                        'FIELD_ID' => 'ASSIGNED_BY_ID'
                    )
                ),
                'default' => true,
            ),
            array(
                'id' => 'ADDRESS',
                'name' => Loc::getMessage('CRMSTORES_FILTER_FIELD_ADDRESS'),
                'default' => true,
            ),
        );

        self::$filterPresets = array(
            'my_stores' => array(
                'name' => Loc::getMessage('CRMSTORES_FILTER_PRESET_MY_STORES'),
                'fields' => array(
                    'ASSIGNED_BY_ID' => $USER->GetID(),
                    'ASSIGNED_BY_ID_name' => $USER->GetFullName(),
                )
            )
        );
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('wcomm.crmstores')) {
            ShowError(Loc::getMessage('CRMSTORES_NO_MODULE'));
            return;
        }

        $context = Context::getCurrent();
        $request = $context->getRequest();

        $grid = new Grid\Options(self::GRID_ID);

        //region Sort
        $gridSort = $grid->getSorting();
        $sort = array_filter(
            $gridSort['sort'],
            function ($field) {
                return in_array($field, self::SORTABLE_FIELDS);
            },
            ARRAY_FILTER_USE_KEY
        );
        if (empty($sort)) {
            $sort = array('NAME' => 'asc');
        }
        //endregion

        //region Filter
        $gridFilter = new Filter\Options(self::GRID_ID, self::$filterPresets);
        $gridFilterValues = $gridFilter->getFilter(self::$filterFields);
        $gridFilterValues = array_filter(
            $gridFilterValues,
            function ($fieldName) {
                return in_array($fieldName, self::FILTERABLE_FIELDS);
            },
            ARRAY_FILTER_USE_KEY
        );
        //endregion

        $this->processGridActions($gridFilterValues);
        $this->processServiceActions($gridFilterValues);

        //region Pagination
        $gridNav = $grid->GetNavParams();
        $pager = new PageNavigation('');
        $pager->setPageSize($gridNav['nPageSize']);
        $pager->setRecordCount(StoreTable::getCount($gridFilterValues));
        if ($request->offsetExists('page')) {
            $currentPage = $request->get('page');
            $pager->setCurrentPage($currentPage > 0 ? $currentPage : $pager->getPageCount());
        } else {
            $pager->setCurrentPage(1);
        }
        //endregion

        $stores = $this->getStores(array(
            'filter' => $gridFilterValues,
            'limit' => $pager->getLimit(),
            'offset' => $pager->getOffset(),
            'order' => $sort
        ));

        $requestUri = new Uri($request->getRequestedPage());
        $requestUri->addParams(array('sessid' => bitrix_sessid()));

        $this->arResult = array(
            'GRID_ID' => self::GRID_ID,
            'STORES' => $stores,
            'HEADERS' => self::$headers,
            'PAGINATION' => array(
                'PAGE_NUM' => $pager->getCurrentPage(),
                'ENABLE_NEXT_PAGE' => $pager->getCurrentPage() < $pager->getPageCount(),
                'URL' => $request->getRequestedPage(),
            ),
            'SORT' => $sort,
            'FILTER' => self::$filterFields,
            'FILTER_PRESETS' => self::$filterPresets,
            'ENABLE_LIVE_SEARCH' => false,
            'DISABLE_SEARCH' => true,
            'SERVICE_URL' => $requestUri->getUri(),
        );

        $this->includeComponentTemplate();
    }

    private function getStores($params = array())
    {
        $dbStores = StoreTable::getList($params);
        $stores = $dbStores->fetchAll();

        $userIds = array_column($stores, 'ASSIGNED_BY_ID');
        $userIds = array_unique($userIds);
        $userIds = array_filter(
            $userIds,
            function ($userId) {
                return intval($userId) > 0;
            }
        );

        $dbUsers = UserTable::getList(array(
            'filter' => array('=ID' => $userIds)
        ));
        $users = array();
        foreach ($dbUsers as $user) {
            $users[$user['ID']] = $user;
        }

        foreach ($stores as &$store) {
            if (intval($store['ASSIGNED_BY_ID']) > 0) {
                $store['ASSIGNED_BY'] = $users[$store['ASSIGNED_BY_ID']];
            }
        }

        return $stores;
    }

    private function processGridActions($currentFilter)
    {
        if (!check_bitrix_sessid()) {
            return;
        }

        $context = Context::getCurrent();
        $request = $context->getRequest();

        $action = $request->get('action_button_' . self::GRID_ID);

        if (!in_array($action, self::SUPPORTED_ACTIONS)) {
            return;
        }

        $allRows = $request->get('action_all_rows_' . self::GRID_ID) == 'Y';
        if ($allRows) {
            $dbStores = StoreTable::getList(array(
                'filter' => $currentFilter,
                'select' => array('ID'),
            ));
            $storeIds = array();
            foreach ($dbStores as $store) {
                $storeIds[] = $store['ID'];
            }
        } else {
            $storeIds = $request->get('ID');
            if (!is_array($storeIds)) {
                $storeIds = array();
            }
        }

        if (empty($storeIds)) {
            return;
        }

        switch ($action) {
            case 'delete':
                foreach ($storeIds as $storeId) {
                    StoreTable::delete($storeId);
                }
            break;

            default:
            break;
        }
    }

    private function processServiceActions($currentFilter)
    {
        global $APPLICATION;

        if (!check_bitrix_sessid()) {
            return;
        }

        $context = Context::getCurrent();
        $request = $context->getRequest();

        $params = $request->get('PARAMS');

        if (empty($params['GRID_ID']) || $params['GRID_ID'] != self::GRID_ID) {
            return;
        }

        $action = $request->get('ACTION');

        if (!in_array($action, self::SUPPORTED_SERVICE_ACTIONS)) {
            return;
        }

        $APPLICATION->RestartBuffer();
        header('Content-Type: application/json');

        switch ($action) {
            case 'GET_ROW_COUNT':
                $count = StoreTable::getCount($currentFilter);
                echo Json::encode(array(
                    'DATA' => array(
                        'TEXT' => Loc::getMessage('CRMSTORES_GRID_ROW_COUNT', array('#COUNT#' => $count))
                    )
                ));
            break;

            default:
            break;
        }

        die;
    }
}