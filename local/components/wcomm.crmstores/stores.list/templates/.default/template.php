<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;

/** @var CBitrixComponentTemplate $this */

if (!Loader::includeModule('crm')) {
    ShowError(Loc::getMessage('CRMSTORES_NO_CRM_MODULE'));
    return;
}

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

$snippet = new \Bitrix\Main\Grid\Panel\Snippet();
$applyButton = $snippet->getApplyButton(
    array(
        'ONCHANGE' => array(
            array(
                'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
                'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processApplyButtonClick('{$gridManagerID}')"))
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
            'DATA' => array(array('JS' => "BX.CrmUIGridExtension.applyAction('{$gridManagerID}', 'delete')"))
        )
    )
);

$actionList[] = array(
    'NAME' => GetMessage('CRMSTORES_ADDTASK'),
    'VALUE' => 'tasks',
    'ONCHANGE' => array(
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CREATE,
            'DATA' => array($applyButton)
        ),
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
            'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processActionChange('{$gridManagerID}', 'tasks')"))
        )
    )
);
if(!Bitrix\Main\Grid\Context::isInternalRequest())
{
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
}
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
                array('JS' => "BX.CrmUIGridExtension.prepareAction('{$gridManagerID}', 'assign_to',  { searchInputId: 'action_assigned_by_search_control', dataInputId: 'action_assigned_by_id_control', componentName: '{$prefix}_ACTION_ASSIGNED_BY' })")
            )
        ),
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
            'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processActionChange('{$gridManagerID}', 'assign_to')"))
        )
    )
);
$actionList[] = array(
    'NAME' => GetMessage('CRMSTORES_ACTION_DELETE_TEXT'),
    'VALUE' => 'delete',
    'ONCHANGE' => array(
        array(
            'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
            'DATA' => array(array('JS' => "BX.CrmUIGridExtension.applyAction('{$gridManagerID}', 'delete')"))
        )
    )
);

$controlPanel['GROUPS'][0]['ITEMS'][] = $button;
$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getEditButton();
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
    $editUrl = CComponentEngine::makePathFromTemplate(
        $arParams['URL_TEMPLATES']['EDIT'],
        array('STORE_ID' => $store['ID'])
    );

    $deleteUrlParams = http_build_query(array(
        'action_button_' . $arResult['GRID_ID'] => 'delete',
        'ID' => array($store['ID']),
        'sessid' => bitrix_sessid()
    ));
    $deleteUrl = $arParams['SEF_FOLDER'] . '?' . $deleteUrlParams;
    // здесь возможно надо будет поправить чтобы объект тянулся в сделку
    $arEntitySubMenuItems[] = array(
        'TITLE' => GetMessage('CRMSTORES_DEAL_ADD_TITLE'),
        'TEXT' => GetMessage('CRMSTORES_DEAL_ADD_SHORT'),
        'ONCLICK' => "BX.Crm.Page.open('".CUtil::JSEscape('/crm/deal/details/0/')."')"
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
                            '{$gridManagerId1}', 
                            BX.CrmUIGridMenuCommand.createActivity, 
                            { typeId: BX.CrmActivityType.task, settings: { ownerID: {$store['ID']} } }
                        )"
        );
    }


    $rows[] = array(
        'id' => $store['ID'],
        'actions' => array(
            array(
                'TITLE' => Loc::getMessage('CRMSTORES_ACTION_VIEW_TITLE'),
                'TEXT' => Loc::getMessage('CRMSTORES_ACTION_VIEW_TEXT'),
                'ONCLICK' => 'BX.Crm.Page.open(' . Json::encode($viewUrl) . ')',
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
            'NAME' => '<a href="' . $viewUrl . '" target="_self">' . $store['NAME'] . '</a>',
            'ASSIGNED_BY' => empty($store['ASSIGNED_BY']) ? '' : CCrmViewHelper::PrepareUserBaloonHtml(
                array(
                    'PREFIX' => "STORE_{$store['ID']}_RESPONSIBLE",
                    'USER_ID' => $store['ASSIGNED_BY_ID'],
                    'USER_NAME'=> CUser::FormatName(CSite::GetNameFormat(), $store['ASSIGNED_BY']),
                    'USER_PROFILE_URL' => Option::get('intranet', 'path_user', '', SITE_ID) . '/'
                )
            ),
            'ADDRESS' => $store['ADDRESS'],
        )
    );
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
                // служебное
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