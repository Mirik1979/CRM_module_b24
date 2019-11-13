<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;
use Bitrix\Crm\Tracking;

/** @var CBitrixComponentTemplate $this */

if (!Loader::includeModule('crm')) {
    ShowError(Loc::getMessage('CRMSTORES_NO_CRM_MODULE'));
    return;
}

if (CModule::IncludeModule('bitrix24') && !\Bitrix\Crm\CallList\CallList::isAvailable())
{
    CBitrix24::initLicenseInfoPopupJS();
}

//$context = Context::getCurrent();
//$request = $context->getRequest();
//print_r($request);

\Bitrix\Main\Diag\Debug::writeToFile($_REQUEST, "rqsttemp", "__miros.log");

$asset = Asset::getInstance();
$asset->addJs('/bitrix/js/crm/interface_grid.js');
$asset->addJs('/bitrix/js/crm/activity.js');
$asset->addJs('/bitrix/js/crm/interface_grid.js');
$asset->addJs('/bitrix/js/crm/analytics.js');
$asset->addJs('/bitrix/js/crm/autorun_proc.js');
$asset->addCss('/bitrix/js/crm/css/autorun_proc.css');
$asset->addJs('/bitrix/js/crm/batch_deletion.js');
$asset->addJs('/bitrix/js/crm/dialog.js');

if (CModule::IncludeModule('bitrix24') && !\Bitrix\Crm\CallList\CallList::isAvailable())
{
    CBitrix24::initLicenseInfoPopupJS();
}

$activityEditorID = "{$arResult['GRID_ID']}_activity_editor";
$APPLICATION->IncludeComponent(
    'bitrix:crm.activity.editor',
    '',
    array(
        'EDITOR_ID' => $activityEditorID,
        'PREFIX' => $arResult['GRID_ID'],
        'OWNER_TYPE' => 'COMPANY',
        'OWNER_ID' => 0,
        'READ_ONLY' => false,
        'ENABLE_UI' => false,
        'ENABLE_TOOLBAR' => false
    ),
    null,
    array('HIDE_ICONS' => 'Y')
);


$gridManagerId = $arResult['GRID_ID'] . '_MANAGER';
$gridManagerId1 = 'CRM_COMPANY_LIST_V12_MANAGER';
$gridManagerCfg = array(
    'ownerType' => 'STORES',
    'gridId' => $arResult['GRID_ID'],
    'formName' => "form_{$arResult['GRID_ID']}",
    'allRowsCheckBoxId' => "actallrows_{$arResult['GRID_ID']}",
    'activityEditorId' => $activityEditorID,
    'serviceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
    'filterFields' => array()
);
$prefix = $arResult['GRID_ID'];

$prefix1 = 'CRM_COMPANY_LIST_V12';

$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
$applyButton = $snippet->getApplyButton(
    array(
        'ONCHANGE' => array(
            array(
                'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
                'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processApplyButtonClick('{$gridManagerId}')"))
            )
        )
    )
);
// оипсываем выпадающий список
$controlPanel = array('GROUPS' => array(array('ITEMS' => array())));
$button = $snippet->getRemoveButton();
$snippet->setButtonActions(
    $button,
    array(
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
            'CONFIRM' => false,
            'DATA' => array(array('JS' => "BX.CrmUIGridExtension.applyAction('{$gridManagerId}', 'delete')"))
        )
    )
);

$actionList = array(array('NAME' => GetMessage('CRM_DEAL_LIST_CHOOSE_ACTION'), 'VALUE' => 'none'));

/*$actionList[] = array(
    'NAME' => GetMessage('CRMSTORES_ADDTASK'),
    'VALUE' => 'tasks',
    'ONCHANGE' => array(
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CREATE,
            'DATA' => array($applyButton)
        ),
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
            'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processActionChange('{$gridManagerId}', 'tasks')"))
        )
    )
); */

$APPLICATION->IncludeComponent(
        'bitrix:intranet.user.selector.new',
        '',
        array(
            'MULTIPLE' => 'N',
            'NAME' => "{$prefix}_ACTION_ASSIGNED_BY",
            'INPUT_NAME' => 'action_assigned_by_search_control',
            'SHOW_EXTRANET_USERS' => 'NONE',
            'POPUP' => 'Y',
            'SITE_ID' => SITE_ID,
            'NAME_TEMPLATE' => $arResult['NAME_TEMPLATE']
        ),
        null,
        array('HIDE_ICONS' => 'Y')
);

$actionList[] = array(
    'NAME' => GetMessage('CRMSTORES_ASSIGN_TO'),
    'VALUE' => 'assign_to',
    'ONCHANGE' => array(
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CREATE,
            'DATA' => array(
                array(
                    'TYPE' => Bitrix\Main\Grid\Panel\Types::TEXT,
                    'ID' => 'action_assigned_by_search',
                    'NAME' => 'ACTION_ASSIGNED_BY_SEARCH'
                ),
                array(
                    'TYPE' => Bitrix\Main\Grid\Panel\Types::HIDDEN,
                    'ID' => 'action_assigned_by_id',
                    'NAME' => 'ACTION_ASSIGNED_BY_ID'
                ),
                $applyButton
            )
        ),
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
            'DATA' => array(
                array('JS' => "BX.CrmUIGridExtension.prepareAction('{$gridManagerId}', 'assign_to',  { searchInputId: 'action_assigned_by_search_control', dataInputId: 'action_assigned_by_id_control', componentName: '{$prefix}_ACTION_ASSIGNED_BY' })")
            )
        ),
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
            'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processActionChange('{$gridManagerId}', 'assign_to')"))
        )
    )
);
/*$actionList[] = array(
    'NAME' => GetMessage('CRMSTORES_ACTION_DELETE_TEXT'),
    'VALUE' => 'delete',
    'ONCHANGE' => array(
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
            'DATA' => array(array('JS' => "BX.CrmUIGridExtension.applyAction('{$gridManagerID}', 'delete')"))
        )
    )
); */

$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getRemoveButton();
//$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getEditButton();
$controlPanel['GROUPS'][0]['ITEMS'][] = array(
    "TYPE" => \Bitrix\Main\Grid\Panel\Types::DROPDOWN,
    "ID" => "action_button_{$prefix}",
    "NAME" => "action_button_{$prefix}",
    "ITEMS" => $actionList
);
$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getForAllCheckbox();

//echo "<pre>";
//print_r($arResult);
//echo "</pre>";
//return;

$rows = array();
foreach ($arResult['STORES'] as $store) {

    $viewUrl = CComponentEngine::makePathFromTemplate(
        $arParams['URL_TEMPLATES']['DETAIL'],
        array('STORE_ID' => $store['ID'])
    );
    //$viewUrl = $viewUrl."?IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER";
    //print_r($viewUrl);
    $editUrl = CComponentEngine::makePathFromTemplate(
        $arParams['URL_TEMPLATES']['DETAIL'],
        array('STORE_ID' => $store['ID'])
    );
    $editUrl = $editUrl."?init_mode=edit";
    $deleteUrlParams = http_build_query(array(
        'action_button_' . $arResult['GRID_ID'] => 'delete',
        'ID' => array($store['ID']),
        'sessid' => bitrix_sessid()
    ));
    $deleteUrl = $arParams['SEF_FOLDER'] . '?' . $deleteUrlParams;
    // здесь возможно надо будет поправить чтобы объект тянулся в сделку
    $dealurl = '/crm/deal/details/0/?UF_STORE='.$store['ID'];

    $arEntitySubMenuItems[] = array(
        'TITLE' => GetMessage('CRMSTORES_DEAL_ADD_TITLE'),
        'TEXT' => GetMessage('CRMSTORES_DEAL_ADD_SHORT'),
        'ONCLICK' => "BX.Crm.Page.open('".CUtil::JSEscape($dealurl)."')"
    );
    if(IsModuleInstalled(CRM_MODULE_CALENDAR_ID)) {
        $arActivityMenuItems[] = array(
            'TITLE' => GetMessage('CRMSTORES_ADD_CALL_TITLE'),
            'TEXT' => GetMessage('CRMSTORES_ADD_CALL'),
            'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
                            '{$gridManagerId}', 
                            BX.CrmUIGridMenuCommand.createActivity, 
                            { typeId: BX.CrmActivityType.call, settings: { ownerID: {$store['ID']} } }
                        )"
        );

        $arActivityMenuItems[] = array(
            'TITLE' => GetMessage('CRMSTORES_ADD_MEETING_TITLE'),
            'TEXT' => GetMessage('CRMSTORES_ADD_MEETING'),
            'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
                            '{$gridManagerId}', 
                            BX.CrmUIGridMenuCommand.createActivity, 
                            { typeId: BX.CrmActivityType.meeting, settings: { ownerID: {$store['ID']} } }
                        )"
        );
    }

    if(IsModuleInstalled('tasks')) {
        $arActivityMenuItems[] = array(
            'TITLE' => GetMessage('CRMSTORES_TASK_TITLE'),
            'TEXT' => GetMessage('CRMSTORES_TASK'),
            'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
                            '{$gridManagerId}', 
                            BX.CrmUIGridMenuCommand.createActivity, 
                            { typeId: BX.CrmActivityType.task, settings: { ownerID: {$store['ID']} } }
                        )"
        );
    }
    if ($store['ACTIVITY']) {
        $store['ACTIVITY'] = CCrmViewHelper::RenderNearestActivity(
            array(
                //'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName(CCrmOwnerType::Company),
                'ENTITY_TYPE_NAME' => 'STORE',
                'ENTITY_ID' => $store['ID'],
                'ENTITY_RESPONSIBLE_ID' => $store['ASSIGNED_BY'],
                'GRID_MANAGER_ID' => $gridManagerId,
                'ACTIVITY_ID' => $store['ACTIVITY'],
                'ACTIVITY_SUBJECT' => $store['SUBJECT'],
                'ACTIVITY_TIME' => $store['DEADLINE'],
                'ACTIVITY_EXPIRED' => $store['DEADLINE'],
                'ALLOW_EDIT' => true,
                'MENU_ITEMS' => $arActivityMenuItems,
                'USE_GRID_EXTENSION' => true
            )
        );

        $counterData = array(
            'CURRENT_USER_ID' =>  $store['ASSIGNED_BY'],
            'ENTITY' => $store['ID'],
            'ACTIVITY' => array(
                'RESPONSIBLE_ID' => $store['ASSIGNED_BY'],
                'TIME' => $store['DEADLINE'],
                'IS_CURRENT_DAY' => true
            )
        );
        //if(CCrmUserCounter::IsReckoned(CCrmUserCounter::CurrentDealActivies, $counterData))
       // {
        $store['GRID_DATA']['columnClasses'] = array('ACTIVITY' => 'crm-list-deal-today');
        //}
    } else {
        //print_r($store['ID']);
        $store['ACTIVITY'] = CCrmViewHelper::RenderNearestActivity(
            array(
                'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName(CCrmOwnerType::Company),
                'ENTITY_ID' => $store['ID'],
                'ENTITY_RESPONSIBLE_ID' => $store['ASSIGNED_BY'],
                'GRID_MANAGER_ID' => $gridManagerId,
                'ALLOW_EDIT' => true,
                'MENU_ITEMS' => $arActivityMenuItems,
                'USE_GRID_EXTENSION' => true
            )
        );

        //$store['ACTIVITY'] = 'Дела отсутствуют';
    }

    $rows[] = array(
        'id' => $store['ID'],
        'actions' => array(
            array(
                'TITLE' => Loc::getMessage('CRMSTORES_ACTION_VIEW_TITLE'),
                'TEXT' => Loc::getMessage('CRMSTORES_ACTION_VIEW_TEXT'),
                //'ONCLICK' => 'BX.Crm.Page.open(' . Json::encode($viewUrl) . ')',
                'ONCLICK' =>'BX.Crm.Page.open(' . Json::encode($viewUrl) . ')',
                'DEFAULT' => true
            ),
            array(
                'TITLE' => Loc::getMessage('CRMSTORES_ACTION_EDIT_TITLE'),
                'TEXT' => Loc::getMessage('CRMSTORES_ACTION_EDIT_TEXT'),
                'ONCLICK' => 'BX.Crm.Page.open(' . Json::encode($editUrl) . ')',
            ),
            array(
                'TITLE' => Loc::getMessage('CRMSTORES_ACTION_DELETE_TITLE'),
                'TEXT' => Loc::getMessage('CRMSTORES_ACTION_DELETE_TEXT'),
                'ONCLICK' => 'BX.CrmUIGridExtension.processMenuCommand(' . Json::encode($gridManagerId) . ', BX.CrmUIGridMenuCommand.remove, { pathToRemove: ' . Json::encode($deleteUrl) . ' })',
            ),
            array(
                'TITLE' => GetMessage('CRMSTORES_ADD_ENTITY_TITLE'),
                'TEXT' => GetMessage('CRMSTORES_ADD_ENTITY'),
                'MENU' => $arEntitySubMenuItems
            ),
            array(
                'TITLE' => GetMessage('CRMSTORES_ADD_ACTIVITY_TITLE'),
                'TEXT' => GetMessage('CRMSTORES_ADD_ACTIVITY'),
                'MENU' => $arActivityMenuItems
            )
        ),
        'data' => $store,
        'columns' => array(
            'ID' => $store['ID'],

            'NAME' => '<a href="' . $viewUrl . '" target="_self">' . $store['NAME'] . '</a><p style="font-size: 12px; margin-top: -2px">'.$store['UF_CRM_1572591277'].'</p>',
            //'NAME' => CCrmViewHelper::RenderClientSummary(
            //    $viewUrl,
            //    $store['NAME'],
            //    Tracking\UI\Grid::enrichSourceName(
            //        "test"
            //    )
            //),

            /* 'NAME' => CCrmViewHelper::RenderClientSummary(
                $arCompany['PATH_TO_COMPANY_SHOW'],
                $arCompany['TITLE'],
                Tracking\UI\Grid::enrichSourceName(
                    \CCrmOwnerType::Company,
                    $arCompany['ID'],
                    $arCompany['COMPANY_TYPE_NAME']
                ), */


                'ASSIGNED_BY' => empty($store['ASSIGNED_BY']) ? '' : CCrmViewHelper::PrepareUserBaloonHtml(
                array(
                    'PREFIX' => "STORE_{$store['ID']}_RESPONSIBLE",
                    'USER_ID' => $store['ASSIGNED_BY_ID'],
                    'USER_NAME'=> CUser::FormatName(CSite::GetNameFormat(), $store['ASSIGNED_BY']),
                    'USER_PROFILE_URL' => Option::get('intranet', 'path_user', '', SITE_ID) . '/'
                )
            ),
            'columnClasses' => array('ACTIVITY' => 'crm-list-deal-today')
        ),

    );
    $arEntitySubMenuItems = array();
    $arActivityMenuItems = array();
}

//$snippet = new Snippet();

$APPLICATION->IncludeComponent(
    'bitrix:crm.interface.grid',
    'titleflex',
    array(
        'GRID_ID' => $arResult['GRID_ID'],
        'HEADERS' => $arResult['HEADERS'],
        'ROWS' => $rows,
        'PAGINATION' => $arResult['PAGINATION'],
        'SORT' => $arResult['SORT'],
        'FILTER' => $arResult['FILTER'],
        'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
        'IS_EXTERNAL_FILTER' => false,
        'ENABLE_LIVE_SEARCH' => $arResult['ENABLE_LIVE_SEARCH'],
        'DISABLE_SEARCH' => $arResult['DISABLE_SEARCH'],
        'ENABLE_ROW_COUNT_LOADER' => true,
        'AJAX_ID' => '',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_LOADER' => null,
        'ACTION_PANEL' => $controlPanel,
            /*array(
            'GROUPS' => array(
                array(
                    'ITEMS' => array(
                        $snippet->getRemoveButton(),
                        $snippet->getEditAction(),
                        $snippet->getForAllCheckbox()
                    )
                )
            )
        ) */
        'EXTENSION' => array(
            'ID' => $gridManagerId,
            'CONFIG' => array(
                'ownerTypeName' => 'STORE',
                'gridId' => $arResult['GRID_ID'],
                'serviceUrl' => $arResult['SERVICE_URL'],
                'activityEditorId' => $activityEditorID,
                'activityServiceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID=' . SITE_ID . '&' . bitrix_sessid_get(),
                // надо будет исправить
                'taskCreateUrl' => $arResult['TASK_CREATE_URL'],


                //'taskCreateUrl' => '/company/personal/user/5/tasks/task/edit/0/?UF_CRM_TASK=#ENTITY_KEYS#&TITLE=CRM%3A+&TAGS=crm&back_url=%2Fcrm%2Fcompany%2Flist%2F'
            ),
            'MESSAGES' => array(
                'deletionDialogTitle' => Loc::getMessage('CRMSTORES_DELETE_DIALOG_TITLE'),
                'deletionDialogMessage' => Loc::getMessage('CRMSTORES_DELETE_DIALOG_MESSAGE'),
                'deletionDialogButtonTitle' => Loc::getMessage('CRMSTORES_DELETE_DIALOG_BUTTON'),
            )
        ),
    ),
    $this->getComponent(),
    array('HIDE_ICONS' => 'Y',)
);
?>
<script type="text/javascript">
    BX.ready(
        function()
        {
            BX.CrmActivityEditor.items['<?= CUtil::JSEscape($activityEditorID)?>'].addActivityChangeHandler(
                function()
                {
                    BX.Main.gridManager.reload('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
					}
            );
            BX.namespace('BX.Crm.Activity');
			if(typeof BX.Crm.Activity.Planner !== 'undefined')
			{
                BX.Crm.Activity.Planner.Manager.setCallback(
                    'onAfterActivitySave',
                    function()
                    {
                        BX.Main.gridManager.reload('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
					}
                );
            }
		}
    );
</script>
<script type="text/javascript">
    BX.ready(
        function()
        {
            BX.CrmLongRunningProcessDialog.messages =
                {
                    startButton: "<?=GetMessageJS('CRM_COMPANY_LRP_DLG_BTN_START')?>",
                    stopButton: "<?=GetMessageJS('CRM_COMPANY_LRP_DLG_BTN_STOP')?>",
                    closeButton: "<?=GetMessageJS('CRM_COMPANY_LRP_DLG_BTN_CLOSE')?>",
                    wait: "<?=GetMessageJS('CRM_COMPANY_LRP_DLG_WAIT')?>",
                    requestError: "<?=GetMessageJS('CRM_COMPANY_LRP_DLG_REQUEST_ERR')?>"
                };

            var gridId = "<?= CUtil::JSEscape($arResult['GRID_ID'])?>";
            BX.Crm.BatchDeletionManager.create(
                gridId,
                {
                    gridId: gridId,
                    entityTypeId: <?=CCrmOwnerType::Company?>,
                    container: "batchDeletionWrapper",
                    stateTemplate: "<?=GetMessageJS('CRM_COMPANY_STEPWISE_STATE_TEMPLATE')?>",
                    messages:
                        {
                            title: "<?=GetMessageJS('CRM_COMPANY_LIST_DEL_PROC_DLG_TITLE')?>",
                            confirmation: "<?=GetMessageJS('CRM_COMPANY_LIST_DEL_PROC_DLG_SUMMARY')?>",
                            summaryCaption: "<?=GetMessageJS('CRM_COMPANY_BATCH_DELETION_COMPLETED')?>",
                            summarySucceeded: "<?=GetMessageJS('CRM_COMPANY_BATCH_DELETION_COUNT_SUCCEEDED')?>",
                            summaryFailed: "<?=GetMessageJS('CRM_COMPANY_BATCH_DELETION_COUNT_FAILED')?>"
                        }
                }
            );

            BX.Crm.AnalyticTracker.config =
                {
                    id: "company_list",
                    settings: { params: <?=CUtil::PhpToJSObject($arResult['ANALYTIC_TRACKER'])?> }
                };
        }
    );
</script>
<script type="text/javascript">
    BX.ready(
        function()
        {
            var link = BX("rebuildCompanyAttrsLink");
            if(link)
            {
                BX.bind(
                    link,
                    "click",
                    function(e)
                    {
                        var msg = BX("rebuildCompanyAttrsMsg");
                        if(msg)
                        {
                            msg.style.display = "none";
                        }
                    }
                );
            }
        }
    );
</script>
<script type="text/javascript">
    BX.ready(
        function()
        {
            BX.CrmRequisitePresetSelectDialog.messages =
                {
                    title: "<?=GetMessageJS("CRM_COMPANY_RQ_TX_SELECTOR_TITLE")?>",
                    presetField: "<?=GetMessageJS("CRM_COMPANY_RQ_TX_SELECTOR_FIELD")?>"
                };

            BX.CrmRequisiteConverter.messages =
                {
                    processDialogTitle: "<?=GetMessageJS('CRM_COMPANY_RQ_TX_PROC_DLG_TITLE')?>",
                    processDialogSummary: "<?=GetMessageJS('CRM_COMPANY_RQ_TX_PROC_DLG_DLG_SUMMARY')?>"
                };

            var converter = BX.CrmRequisiteConverter.create(
                "converter",
                {
                    entityTypeName: "<?=CUtil::JSEscape(CCrmOwnerType::CompanyName)?>",
                    serviceUrl: "<?=SITE_DIR?>bitrix/components/bitrix/crm.company.list/list.ajax.php?&<?=bitrix_sessid_get()?>"
                }
            );

            BX.addCustomEvent(
                converter,
                'ON_COMPANY_REQUISITE_TRANFER_COMPLETE',
                function()
                {
                    var msg = BX("transferRequisitesMsg");
                    if(msg)
                    {
                        msg.style.display = "none";
                    }
                }
            );

            var transferLink = BX("transferRequisitesLink");
            if(transferLink)
            {
                BX.bind(
                    transferLink,
                    "click",
                    function(e)
                    {
                        converter.convert();
                        return BX.PreventDefault(e);
                    }
                );
            }

            var skipTransferLink = BX("skipTransferRequisitesLink");
            if(skipTransferLink)
            {
                BX.bind(
                    skipTransferLink,
                    "click",
                    function(e)
                    {
                        converter.skip();

                        var msg = BX("transferRequisitesMsg");
                        if(msg)
                        {
                            msg.style.display = "none";
                        }

                        return BX.PreventDefault(e);
                    }
                );
            }
        }
    );
</script>