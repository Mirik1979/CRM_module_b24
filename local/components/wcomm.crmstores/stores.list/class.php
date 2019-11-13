<?php
defined('B_PROLOG_INCLUDED') || die;

use Wcomm\CrmStores\Entity\StoreTable;
use WComm\CrmStores\BizProc\StoreDocument;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UserTable;
use Bitrix\Main\Grid;
use Bitrix\Main\UI\Filter;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;

\Bitrix\Main\Diag\Debug::writeToFile($_REQUEST, "rqstclass", "__miros.log");


class CWcommCrmStoresStoresListComponent extends CBitrixComponent
{
    const GRID_ID = 'CRMSTORES_LIST';


    //const SORTABLE_FIELDS = array('ID', 'NAME', 'ASSIGNED_BY_ID', 'ADDRESS');
    const FILTERABLE_FIELDS = array('ID', 'NAME', 'ASSIGNED_BY_ID', 'ADDRESS', 'UF_CRM_1571643566',
        'UF_CRM_1571643637', 'UF_CRM_1571643659', 'UF_CRM_1571643700', 'UF_CRM_1571643732', 'UF_CRM_1571643763',
        'UF_CRM_1571643763', 'UF_CRM_1571643779', 'UF_CRM_1571643790', 'UF_CRM_1571643803', 'UF_CRM_1571643821',
        'UF_CRM_1571643839', 'UF_CRM_1572340128', 'UF_CRM_1572343737', 'UF_CRM_1572343737', 'UF_CRM_1572343845', 'UF_CRM_1572343864',
        'UF_CRM_1572343891', 'UF_CRM_1572343927',  'UF_CRM_1571643700');
    const SUPPORTED_ACTIONS = array('delete', 'assign_to');
    const SUPPORTED_SERVICE_ACTIONS = array('GET_ROW_COUNT');

    private static $headers;
    private static $filterFields;
    private static $filterPresets;
    private static $supportedFields;

    public function __construct(CBitrixComponent $component = null)
    {
        global $USER;

        parent::__construct($component);

        if (!Loader::includeModule('wcomm.crmstores')) {
            ShowError(Loc::getMessage('CRMSTORES_NO_MODULE'));
            return;
        }

        self::$headers = $this->getHeaders();
        self::$filterFields = $this->getFilters();
        self::$supportedFields = $this->getallFields();

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
        $userID = CCrmSecurityHelper::GetCurrentUserID();
        $context = Context::getCurrent();
        $request = $context->getRequest();

        $grid = new Grid\Options(self::GRID_ID);

        //region Sort
        //сортировки смотрим здесь
        $gridSort = $grid->getSorting();

        $sort = array_filter(
            $gridSort['sort'],
            function ($field) {
                //return in_array($field, $supportedFields);
                return $field;
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
        $gridFilterValues =  $this->refineFilter($gridFilterValues);
        //\Bitrix\Main\Diag\Debug::writeToFile(self::FILTERABLE_FIELDS, "const", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile(self::$supportedFields, "var", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($gridFilterValues, "grf0+", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($gridFilterValues1, "grf0", "__miros.log");

        $gridFilterValues = array_filter(
            $gridFilterValues,
            function ($fieldName) {
                //return in_array($fieldName, self::FILTERABLE_FIELDS);
                return in_array($fieldName, self::$supportedFields);
                //return $fieldName;
            },
            ARRAY_FILTER_USE_KEY
        );
        //endregion
        //\Bitrix\Main\Diag\Debug::writeToFile($_REQUEST, "req", "__miros.log");
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
        //\Bitrix\Main\Diag\Debug::writeToFile($_REQUEST, "req", "__miros.log");
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
            'TASK_CREATE_URL' => CHTTP::urlAddParams(
                CComponentEngine::MakePathFromTemplate(
                    COption::GetOptionString('tasks', 'paths_task_user_edit', ''),
                    array(
                        'task_id' => 0,
                        'user_id' => $userID
                    )
                ),
                array(
                    //'UF_CRM_TASK' => '#ENTITY_KEYS#',
                    'UF_CRM_TASK' => '_1;_33',
                    'TITLE' => urlencode('CRM'),
                    'TAGS' => urlencode('CRM'),
                    'back_url' => urlencode()
                )
            )

        );


        $this->includeComponentTemplate();
    }

    private function getallFields() {

        global $USER_FIELD_MANAGER;
        $arr = array ('ID', 'NAME', 'ASSIGNED_BY_ID', '>=UF_CRM_1571643821', '<=UF_CRM_1571643821',
            '>=UF_CRM_1571643839', '<=UF_CRM_1571643839', '>=UF_CRM_1571643779', '<=UF_CRM_1571643779',
            '>=UF_CRM_1571643790', '<=UF_CRM_1571643790', '>=UF_CRM_1571643803', '<=UF_CRM_1571643803');
        $arUserFields = $USER_FIELD_MANAGER->GetUserFields(StoreTable::getUfId());
        foreach ($arUserFields as $key => $fval) {
            array_push($arr, $key);
        }
        return $arr;
    }

    private function refineFilter($params = array())
    {
        //\Bitrix\Main\Diag\Debug::writeToFile('begin', "usrf", "__miros.log");
        $arUserFieldsdouble = $this->prepareEntityUserFieldInfos();
        //\Bitrix\Main\Diag\Debug::writeToFile($arUserFieldsdouble, "usrf", "__miros.log");
        $fields = $params;

        foreach ($params as $key => $param) {
            //UF_CRM_1571643821_datesel
            $clkey = str_replace('_datesel', '', $key);
            $clkey = str_replace('_numsel', '', $clkey);
            \Bitrix\Main\Diag\Debug::writeToFile($key, "usrfkey", "__miros.log");
            if ($arUserFieldsdouble[$clkey]['data']['fieldInfo']['USER_TYPE_ID']=='crm' &&
                $arUserFieldsdouble[$clkey]['data']['fieldInfo']['MULTIPLE']=='N') {
                //\Bitrix\Main\Diag\Debug::writeToFile('match', "usrfkey", "__miros.log");
                $str = preg_replace("/[^0-9]/", '', $param);
                $fields[$key] = $str;
            } elseif($arUserFieldsdouble[$clkey ]['data']['fieldInfo']['USER_TYPE_ID']=='crm' &&
                $arUserFieldsdouble[$clkey]['data']['fieldInfo']['MULTIPLE']=='Y') {
                //\Bitrix\Main\Diag\Debug::writeToFile($param, "prmv", "__miros.log");
                $str = preg_replace("/[^0-9]/", '', $param);
                $newvalarr = array($str);
                $fields[$key] = $newvalarr;
            } elseif($arUserFieldsdouble[$clkey]['data']['fieldInfo']['USER_TYPE_ID']=='date') {
                $datebegin = $fields[$clkey."_from"];
                $dateend = $fields[$clkey."_to"];
                $fields[">=".$clkey] = ConvertDateTime($datebegin, "DD.MM.YYYY")." 00:00:00";
                $fields["<=".$clkey] = ConvertDateTime($dateend, "DD.MM.YYYY")." 23:59:00";
            } elseif($arUserFieldsdouble[$clkey]['data']['fieldInfo']['USER_TYPE_ID']=='double') {
                if($fields[$clkey."_from"]) {
                    $fields[">=".$clkey] = $fields[$clkey."_from"];
                }
                if($fields[$clkey."_to"]) {
                    $fields["<=".$clkey] = $fields[$clkey."_to"];
                }

            }
        }
        //\Bitrix\Main\Diag\Debug::writeToFile($fields, "usrfkey", "__miros.log");
        return $fields;
    }


    private function getHeaders()
    {
        global $USER_FIELD_MANAGER;
        $headers = array (
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
                'id' => 'ACTIVITY',
                'name' => Loc::getMessage('CRMSTORES_HEADER_ADDRESS'),
                'sort' => 'ACTIVITY',
                'default' => true,
            )
        );
        if (Bitrix\Main\Loader::includeModule('crm')) {
            $CCrmFields = new CCrmFields($USER_FIELD_MANAGER, StoreTable::getUfId());
        }
        $arUserFields = $CCrmFields->GetFields();
        //\Bitrix\Main\Diag\Debug::writeToFile($arUserFields, "UFFF", "__miros.log");
        //$arUserFields = $USER_FIELD_MANAGER->GetUserFields(self::getUfId());
        //echo "<pre>";
        //print_r($arUserFields);
        //echo "</pre>";

        foreach ($arUserFields as $FIELD_ID => $arField)
        {
            if($arField['SHOW_IN_LIST']=='Y') {
                $newheader = array(
                    'id' => $FIELD_ID,
                    'name' => $arField['EDIT_FORM_LABEL'],
                    'sort' => $FIELD_ID,
                    'default' => $arField['SHOW_IN_LIST'],
                );
                array_push($headers, $newheader);

            }
        }

        return $headers;
    }

    private function getFilters()
    {
        global $USER_FIELD_MANAGER;
        $filter = array (
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
                'type' => 'dest_selector',
                'default' => true,
                'params' => array(
                    'apiVersion' => 3,
                    'context' => 'CRM_STORES_FILTER_ASSIGNED_BY_ID',
                    'multiple' => 'Y',
                    'contextCode' => 'U',
                    'enableAll' => 'N',
                    'enableSonetgroups' => 'N',
                    'allowEmailInvitation' => 'N',
                    'allowSearchEmailUsers' => 'N',
                    'departmentSelectDisable' => 'Y',
                    'isNumeric' => 'Y',
                    'prefix' => 'U'
                )
            ) /*,
            array(
                'id' => 'ADDRESS',
                'name' => Loc::getMessage('CRMSTORES_FILTER_FIELD_ADDRESS'),
                'type' => 'list',
                'params' => array('multiple'=>'N'),
                'default' => 'N',
                'items' => array("Да","Нет")
            )  */
        );
        if (\Bitrix\Main\Loader::includeModule('crm')) {
            $CCrmFields = new CCrmFields($USER_FIELD_MANAGER, StoreTable::getUfId());
        }
        $arUserFields = $CCrmFields->GetFields();
        $arUserFieldsdouble = $this->prepareEntityUserFieldInfos();

        foreach ($arUserFields as $FIELD_ID => $arField) {

            if($arField['SHOW_FILTER']!='N') {
                if ($arUserFieldsdouble[$FIELD_ID]['data']['fieldInfo']['USER_TYPE_ID'] == 'enumeration') {
                    foreach ($arUserFieldsdouble[$FIELD_ID]['data']['fieldInfo']['ENUM'] as $enumval) {
                        $enumvalues[$enumval['ID']] = $enumval['VALUE'];
                    }
                    $newfilter = array(
                        'id' => $FIELD_ID,
                        'name' => $arField['EDIT_FORM_LABEL'],
                        'type' => 'list',
                        'params' => array('multiple' => 'Y'),
                        'default' => 'Y',
                        'items' => $enumvalues
                    );

                } elseif($arUserFieldsdouble[$FIELD_ID]['data']['fieldInfo']['USER_TYPE_ID'] == 'crm' &&
                    $arUserFieldsdouble[$FIELD_ID]['data']['fieldInfo']['SETTINGS']['COMPANY']=='Y') {

                    $newfilter = array(
                        'id' => $FIELD_ID,
                        'name' => $arField['EDIT_FORM_LABEL'],
                        'type' => 'dest_selector',
                        'params' => array(
                            'apiVersion' => 3,
                            'context' => 'CRM_UF_FILTER_ENTITY',
                            'contextCode' => 'CRM',
                            'useClientDatabase' => 'N',
                            'enableAll' => 'N',
                            'enableDepartments' => 'N',
                            'enableUsers' => 'N',
                            'enableSonetgroups' => 'N',
                            'allowEmailInvitation' => 'N',
                            'allowSearchEmailUsers' => 'N',
                            'departmentSelectDisable' => 'Y',
                            'enableCrm' => 'Y',
                            'multiple' => 'N',
                            'convertJson' => 'Y',
                            'enableCrmCompanies' => 'Y',
                            'addTabCrmCompanies' => 'N',
                            'addTabCrmLeads' => 'N',
                            'addTabCrmDeals' => 'N',
                            'addTabCrmContacts' => 'N'
                    ));

                } elseif($arUserFieldsdouble[$FIELD_ID]['data']['fieldInfo']['USER_TYPE_ID'] == 'crm' &&
                    $arUserFieldsdouble[$FIELD_ID]['data']['fieldInfo']['SETTINGS']['CONTACT']=='Y') {
                    //
                    $newfilter = array(
                        'id' => $FIELD_ID,
                        'name' => $arField['EDIT_FORM_LABEL'],
                        'type' => 'dest_selector',
                        'params' => array(
                            'apiVersion' => 3,
                            'context' => 'CRM_UF_FILTER_ENTITY',
                            'contextCode' => 'CRM',
                            'useClientDatabase' => 'N',
                            'enableAll' => 'N',
                            'enableDepartments' => 'N',
                            'enableUsers' => 'N',
                            'enableSonetgroups' => 'N',
                            'allowEmailInvitation' => 'N',
                            'allowSearchEmailUsers' => 'N',
                            'departmentSelectDisable' => 'Y',
                            'enableCrm' => 'Y',
                            'multiple' => 'N',
                            'convertJson' => 'Y',
                            'enableCrmContacts' => 'Y',
                            'addTabCrmContacts' => 'N',
                            'addTabCrmLeads' => 'N',
                            'addTabCrmDeals' => 'N',
                            'addTabCrmCompanies' => 'N'
                        ));


                } elseif($arUserFieldsdouble[$FIELD_ID]['data']['fieldInfo']['USER_TYPE_ID'] == 'date') {
                    $newfilter = array(
                        'id' => $FIELD_ID,
                        'name' => $arField['EDIT_FORM_LABEL'],
                        'type' => 'date',
                        'default' => $arField['SHOW_IN_LIST']
                    );
                } elseif($arUserFieldsdouble[$FIELD_ID]['data']['fieldInfo']['USER_TYPE_ID'] == 'double' /*||
                    $arUserFieldsdouble[$FIELD_ID]['data']['fieldInfo']['USER_TYPE_ID'] == 'money' */) {
                    $newfilter = array(
                        'id' => $FIELD_ID,
                        'name' => $arField['EDIT_FORM_LABEL'],
                        'type' => 'number',
                        'default' => $arField['SHOW_IN_LIST']
                    );
                }
                else {
                    $newfilter = array(
                        'id' => $FIELD_ID,
                        'name' => $arField['EDIT_FORM_LABEL'],
                        'default' => $arField['SHOW_IN_LIST']
                    );
                }
                array_push($filter, $newfilter);
            }
        }

        return $filter;
    }


    private function getStores($params = array())
    {

        global $USER_FIELD_MANAGER;
        //\Bitrix\Main\Diag\Debug::writeToFile($params, "searchparams", "__miros.log");
        $stores = StoreTable::getListEx($params);

        
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
        //\Bitrix\Main\Diag\Debug::writeToFile($stores, "array", "__miros.log");

        $userFieldsadd = $this->prepareEntityUserFieldInfos();
        //\Bitrix\Main\Diag\Debug::writeToFile($userFieldsadd, "array", "__miros.log");

        foreach ($stores as &$store) {
            $dbResult = CCrmActivity::GetList(array("DEADLINE" => ASC), array("URN" => $store['ID'], "COMPLETED" => 'N'), false, false,
                array('ID', 'OWNER_ID', 'OWNER_TYPE_ID', 'URN',
                    'TYPE_ID', 'PROVIDER_ID', 'PROVIDER_TYPE_ID', 'ASSOCIATED_ENTITY_ID', 'DIRECTION',
                    'SUBJECT', 'STATUS', 'DESCRIPTION', 'DESCRIPTION_TYPE',
                    'DEADLINE', 'RESPONSIBLE_ID'), array('QUERY_OPTIONS' => array('LIMIT' => 1, 'OFFSET' => 0)));
            while($fields = $dbResult->Fetch())
            {

                    $store['ACTIVITY'] = $fields['ID'];
                    $store['SUBJECT'] = $fields['SUBJECT'];
                    $store['DEADLINE'] = $fields['DEADLINE'];


            }
            // выводим ближайшее дело
            if (intval($store['ASSIGNED_BY_ID']) > 0) {
                $store['ASSIGNED_BY'] = $users[$store['ASSIGNED_BY_ID']];
            }
            foreach ($userFieldsadd as $field => $fieldval) {
                //\Bitrix\Main\Diag\Debug::writeToFile($field, "array", "__miros.log");
                if ($store[$field]==array()) {
                    $store[$field] = "";
                }
                if ($store[$field]) {
                    if($fieldval['data']['fieldInfo']['USER_TYPE_ID']=='enumeration' &&
                        $fieldval['data']['fieldInfo']['MULTIPLE']=='Y') {
                        $text ='';
                        foreach($store[$field] as $storeval) {
                            foreach($fieldval['data']['fieldInfo']['ENUM'] as $enumval) {
                                if($enumval['ID']==$storeval) {
                                    if ($text) {
                                        $comma = ',';
                                    } else {
                                        $comma = '';
                                    }
                                    $text = $text.$comma.$enumval['VALUE'];
                                }
                            }
                        }
                        $store[$field]=$text;
                    } elseif($fieldval['data']['fieldInfo']['USER_TYPE_ID']=='enumeration' &&
                        $fieldval['data']['fieldInfo']['MULTIPLE']=='N') {
                        foreach($fieldval['data']['fieldInfo']['ENUM'] as $enumval) {
                            if($enumval['ID']==$store[$field]) {
                                $store[$field] = $enumval['VALUE'];
                            }
                        }
                    } elseif($fieldval['data']['fieldInfo']['USER_TYPE_ID']=='crm' &&
                        $fieldval['data']['fieldInfo']['MULTIPLE']=='Y') {
                        if ($fieldval['data']['fieldInfo']['SETTINGS']['COMPANY']=='Y') {
                            $text ='';
                            foreach($store[$field] as $storeval) {
                                $res = CCrmCompany::GetbyID($storeval);
                                $newtext = "<a href=\"/crm/company/show/".$storeval."/\" target=\"_blank\" bx-tooltip-user-id=\"".$storeval."\" bx-tooltip-loader=\"/bitrix/components/bitrix/crm.company.show/card.ajax.php\" bx-tooltip-classname=\"crm_balloon_company\">".$res['TITLE']."</a>";
                                if ($text) {
                                    $comma = ',';
                                } else {
                                    $comma = '';
                                }
                                $text = $text.$comma.$newtext;

                            }
                            $store[$field] = $text;
                        } elseif($fieldval['data']['fieldInfo']['SETTINGS']['CONTACT']=='Y') {
                            //\Bitrix\Main\Diag\Debug::writeToFile($store[$field], "ffff", "__miros.log");
                            $text ='';
                            foreach($store[$field] as $storeval) {
                                $res = CCrmContact::GetbyID($storeval);
                                $newtext =  "<a href=\"/crm/contact/show/".$storeval."/\" target=\"_blank\" bx-tooltip-user-id=\"".$storeval."\" bx-tooltip-loader=\"/bitrix/components/bitrix/crm.contact.show/card.ajax.php\" bx-tooltip-classname=\"crm_balloon_contact\">".$res['FULL_NAME']."</a>";
                                if ($text) {
                                    $comma = ',';
                                } else {
                                    $comma = '';
                                }
                                $text = $text.$comma.$newtext;
                            }
                            $store[$field] = $text;
                        }
                    } elseif($fieldval['data']['fieldInfo']['USER_TYPE_ID']=='crm' &&
                        $fieldval['data']['fieldInfo']['MULTIPLE']=='N') {
                        if ($fieldval['data']['fieldInfo']['SETTINGS']['COMPANY']=='Y') {
                            $res = CCrmCompany::GetbyID($store[$field]);
                            $store[$field] = "<a href=\"/crm/company/show/".$store[$field]."/\" target=\"_blank\" bx-tooltip-user-id=\"".$store[$field]."\" bx-tooltip-loader=\"/bitrix/components/bitrix/crm.company.show/card.ajax.php\" bx-tooltip-classname=\"crm_balloon_company\">".$res['TITLE']."</a>";
                        } elseif($fieldval['data']['fieldInfo']['SETTINGS']['CONTACT']=='Y') {
                            $res = CCrmContact::GetbyID($store[$field]);
                            $store[$field] = "<a href=\"/crm/contact/show/".$store[$field]."/\" target=\"_blank\" bx-tooltip-user-id=\"".$store[$field]."\" bx-tooltip-loader=\"/bitrix/components/bitrix/crm.contact.show/card.ajax.php\" bx-tooltip-classname=\"crm_balloon_contact\">".$res['FULL_NAME']."</a>";

                        }

                    }

                }
            }

        }

        return $stores;
    }

    public function prepareEntityUserFieldInfos()
    {
        global $USER_FIELD_MANAGER;

        if($this->userFieldInfos !== null)
        {
            return $this->userFieldInfos;
        }

        if (Bitrix\Main\Loader::includeModule('crm')) {
            $userFieldEntityID = 'CRM_STORES';
            $userType = new \CCrmUserType($USER_FIELD_MANAGER, $userFieldEntityID);
            $userFields = $userType->GetEntityFields($userType);
            //\Bitrix\Main\Diag\Debug::writeToFile($userFields, "array", "__miros.log");
        }

        $this->userFieldInfos = array();
        $enumerationFields = array();
        foreach($userFields as $userField)
        {
            $fieldName = $userField['FIELD_NAME'];
            $fieldInfo = array(
                'USER_TYPE_ID' => $userField['USER_TYPE_ID'],
                //'ENTITY_ID' => $this->userFieldEntityID,
                //'ENTITY_VALUE_ID' => $this->entityID,
                'FIELD' => $fieldName,
                'MULTIPLE' => $userField['MULTIPLE'],
                'MANDATORY' => $userField['MANDATORY'],
                'SETTINGS' => isset($userField['SETTINGS']) ? $userField['SETTINGS'] : null
                //'CONTEXT' => $this->guid
            );

            if($userField['USER_TYPE_ID'] === 'enumeration')
            {
                $enumerationFields[$fieldName] = $userField;
            }
            /*elseif($userField['USER_TYPE_ID'] === 'file')
            {
                // смотреть на файл
                $fieldInfo['ADDITIONAL'] = array(
                    'URL_TEMPLATE' => \CComponentEngine::MakePathFromTemplate(
                        '/bitrix/components/bitrix/crm.company.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#',
                        array(
                            'owner_id' => $this->entityID,
                            'field_name' => $fieldName
                        )
                    )
                );
            } */

            $this->userFieldInfos[$fieldName] = array(
                'name' => $fieldName,
                'title' => isset($userField['EDIT_FORM_LABEL']) ? $userField['EDIT_FORM_LABEL'] : $fieldName,
                'type' => 'userField',
                'data' => array('fieldInfo' => $fieldInfo)
            );

            if(isset($userField['MANDATORY']) && $userField['MANDATORY'] === 'Y')
            {
                $this->userFieldInfos[$fieldName]['required'] = true;
            }
        }

        if(!empty($enumerationFields))
        {
            $enumInfos = \CCrmUserType::PrepareEnumerationInfos($enumerationFields);
            foreach($enumInfos as $fieldName => $enums)
            {
                if(isset($this->userFieldInfos[$fieldName])
                    && isset($this->userFieldInfos[$fieldName]['data'])
                    && isset($this->userFieldInfos[$fieldName]['data']['fieldInfo'])
                )
                {
                    $this->userFieldInfos[$fieldName]['data']['fieldInfo']['ENUM'] = $enums;
                }
            }
        }

        return $this->userFieldInfos;
    }


    private function processGridActions($currentFilter)
    {
        //global $APPLICATION;



        //\Bitrix\Main\Diag\Debug::writeToFile('here', "post", "__miros.log");
        //$getAction = 'action_'.$arResult['GRID_ID'];
        //$actionData = array(
        //    'METHOD' => $_SERVER['REQUEST_METHOD'],
        //    'ACTIVE' => false
        //);

        //\Bitrix\Main\Diag\Debug::writeToFile('nnn', "name", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($_REQUEST, "name", "__miros.log");

        //$actionData['NAME'] = $_GET;
        //\Bitrix\Main\Diag\Debug::writeToFile($actionData['NAME'], "name", "__miros.log");

        if (!check_bitrix_sessid()) {
            return;
        }

        //\Bitrix\Main\Diag\Debug::writeToFile('here2', "post", "__miros.log");

        $context = Context::getCurrent();
        $request = $context->getRequest();

        $uriString = $request->getUserAgent();
        //$q = $request->getQueryList();
        $p = $request->getPostList();
        $method = $request->getRequestMethod();
        //\Bitrix\Main\Diag\Debug::writeToFile($p['controls']['action_button_CRMSTORES_LIST'], "post", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($method, "", "__miros.log");

        if ($p['controls']['action_button_CRMSTORES_LIST']=='assign_to') {
            $newassigned = $p['controls']['ACTION_ASSIGNED_BY_ID'];
            $action = $p['controls']['action_button_CRMSTORES_LIST'];
            //\Bitrix\Main\Diag\Debug::writeToFile($p['rows'], "", "__miros.log");
            foreach ($p['rows'] as $key => $param) {
                //\Bitrix\Main\Diag\Debug::writeToFile($param, "", "__miros.log");
                StoreTable::update($param, array('ASSIGNED_BY_ID' => $newassigned));
            }
        } elseif ($p['controls']['action_button_CRMSTORES_LIST']=='task') {
            //LocalRedirect('www.rbc.ru');
            //echo '<script> window.open("http://ya.ru");</script>';

        }







        //elseif ($p['controls']['action_button_CRMSTORES_LIST']=='task') {


        /* \Bitrix\Main\Diag\Debug::writeToFile($_REQUEST, "rqst2", "__miros.log");
            $arTaskID = array();
            foreach ($p['rows'] as $key => $param) {
                \Bitrix\Main\Diag\Debug::writeToFile($param, "prm", "__miros.log");
                $arTaskID[] = '_'.$param;
            }
            $APPLICATION->RestartBuffer();
            $userID = CCrmSecurityHelper::GetCurrentUserID();
            $taskUrl = CHTTP::urlAddParams(
                CComponentEngine::MakePathFromTemplate(
                    COption::GetOptionString('tasks', 'paths_task_user_edit', ''),
                    array(
                        'task_id' => 0,
                        'user_id' => $userID
                    )
                ),
                array(
                    'UF_CRM_TASK' => implode(';', $arTaskID),
                    'TITLE' => urlencode('CRM:'),
                    'TAGS' => ''
                    //'back_url' => ''
                )
            ); */
            //\Bitrix\Main\Diag\Debug::writeToFile($taskUrl, "url", "__miros.log");
            //LocalRedirect($taskUrl);
            //$APPLICATION->RestartBuffer();
            //return;
            //echo '<script> parent.window.location = "'.CUtil::JSEscape($taskUrl).'";</script>';
            //return;

        //}
        //\Bitrix\Main\Diag\Debug::writeToFile($key, "", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($newassigned, "", "__miros.log");


        if ($action=="") {
            $action = $request->get('action_button_' . self::GRID_ID);
        }

        //\Bitrix\Main\Diag\Debug::writeToFile($storeupdate, "", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile($newassigned, "", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile("ID".$request->get('ID'), "", "__miros.log");
        //\Bitrix\Main\Diag\Debug::writeToFile("assigned".$newassigned, "", "__miros.log");
        //

        if (!in_array($action, self::SUPPORTED_ACTIONS)) {
           return;
        }

        $allRows = $request->get('action_all_rows_' . self::GRID_ID) == 'Y';
        if ($allRows) {
            $dbStores = StoreTable::getListEx(array(
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
        //\Bitrix\Main\Diag\Debug::writeToFile($action, "", "__miros.log");
        /*if ($action == 'assign_to') {
            \Bitrix\Main\Diag\Debug::writeToFile("assign_to", "", "__miros.log");
            $arUpdateData = array(
                'ASSIGNED_BY_ID' => $newassigned
            );
            foreach ($storeIds as $storeId) {
                \Bitrix\Main\Diag\Debug::writeToFile($storeId, "", "__miros.log");
                \Bitrix\Main\Diag\Debug::writeToFile($param['ACTION_ASSIGNED_BY_ID'], "", "__miros.log");
                StoreTable::update(4, array('ASSIGNED_BY_ID' => 5));
            }

        } */


        switch ($action) {
            case 'delete':
                foreach ($storeIds as $storeId) {
                    StoreTable::delete($storeId);
                    if (Loader::includeModule('bizproc')) {
                        CBPDocument::OnDocumentDelete(StoreDocument::getComplexDocumentId($storeId), $bpErrors);
                    }

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