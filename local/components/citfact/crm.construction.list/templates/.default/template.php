<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
* Bitrix vars
* @global CUser $USER
* @global CMain $APPLICATION
* @global CDatabase $DB
* @var array $arParams
* @var array $arResult
* @var CBitrixComponent $component
*/

use Bitrix\Crm\Integration;
use Bitrix\Crm\Tracking;

$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/crm-entity-show.css");
if(SITE_TEMPLATE_ID === 'bitrix24')
{
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/bitrix24/crm-entity-show.css");
}
if (CModule::IncludeModule('bitrix24') && !\Bitrix\Crm\CallList\CallList::isAvailable())
{
	CBitrix24::initLicenseInfoPopupJS();
}

Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/activity.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/interface_grid.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/analytics.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/autorun_proc.js');
Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/autorun_proc.css');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/batch_deletion.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/dialog.js');

Bitrix\Main\UI\Extension::load("ui.progressbar");
?><div id="batchDeletionWrapper"></div><?
$APPLICATION->SetTitle('Объекты');
if($arResult['NEED_FOR_REBUILD_DUP_INDEX']):
	?><div id="rebuildConstructionDupIndexMsg" class="crm-view-message">
		<?=GetMessage('CRM_CONSTRUCTION_REBUILD_DUP_INDEX', array('#ID#' => 'rebuildConstructionDupIndexLink', '#URL#' => '#'))?>
	</div><?
endif;

if($arResult['NEED_FOR_REBUILD_SEARCH_CONTENT']):
	?><div id="rebuildConstructionSearchWrapper"></div><?
endif;

if($arResult['NEED_FOR_BUILD_TIMELINE']):
	?><div id="buildConstructionTimelineWrapper"></div><?
endif;

if($arResult['NEED_FOR_BUILD_DUPLICATE_INDEX']):
	?><div id="buildConstructionDuplicateIndexWrapper"></div><?
endif;

if($arResult['NEED_FOR_REBUILD_CONSTRUCTION_ATTRS']):
	?><div id="rebuildConstructionAttrsMsg" class="crm-view-message">
		<?=GetMessage('CRM_CONSTRUCTION_REBUILD_ACCESS_ATTRS', array('#ID#' => 'rebuildConstructionAttrsLink', '#URL#' => $arResult['PATH_TO_PRM_LIST']))?>
	</div><?
endif;

if($arResult['NEED_FOR_TRANSFER_REQUISITES']):
	?><div id="transferRequisitesMsg" class="crm-view-message">
	<?=Bitrix\Crm\Requisite\EntityRequisiteConverter::getIntroMessage(
		array(
			'EXEC_ID' => 'transferRequisitesLink', 'EXEC_URL' => '#',
			'SKIP_ID' => 'skipTransferRequisitesLink', 'SKIP_URL' => '#'
		)
	)?>
	</div><?
endif;

$isInternal = $arResult['INTERNAL'];
$callListUpdateMode = $arResult['CALL_LIST_UPDATE_MODE'];
$allowWrite = $arResult['PERMS']['WRITE'] && !$callListUpdateMode;
$allowDelete = $arResult['PERMS']['DELETE'] && !$callListUpdateMode;
$currentUserID = $arResult['CURRENT_USER_ID'];
$activityEditorID = '';
if(!$isInternal):
	$activityEditorID = "{$arResult['GRID_ID']}_activity_editor";
	$APPLICATION->IncludeComponent(
		'bitrix:crm.activity.editor',
		'',
		array(
			'EDITOR_ID' => $activityEditorID,
			'PREFIX' => $arResult['GRID_ID'],
			'OWNER_TYPE' => 'CONSTRUCTION',
			'OWNER_ID' => 0,
			'READ_ONLY' => false,
			'ENABLE_UI' => false,
			'ENABLE_TOOLBAR' => false
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
endif;
$gridManagerID = $arResult['GRID_ID'].'_MANAGER';
$gridManagerCfg = array(
	'ownerType' => 'CONSTRUCTION',
	'gridId' => $arResult['GRID_ID'],
	'formName' => "form_{$arResult['GRID_ID']}",
	'allRowsCheckBoxId' => "actallrows_{$arResult['GRID_ID']}",
	'activityEditorId' => $activityEditorID,
	'serviceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
	'filterFields' => array()
);
$prefix = $arResult['GRID_ID'];
$prefixLC = strtolower($arResult['GRID_ID']);

$arResult['GRID_DATA'] = array();
$arColumns = array();
foreach ($arResult['HEADERS'] as $arHead)
	$arColumns[$arHead['id']] = false;

$now = time() + CTimeZone::GetOffset();

foreach($arResult['CONSTRUCTION'] as $sKey =>  $arConstruction)
{
	$arEntitySubMenuItems = array();
	$arActivityMenuItems = array();
	$arActivitySubMenuItems = array();
	$arActions = array();

	$arActions[] = array(
		'TITLE' => GetMessage('CRM_CONSTRUCTION_SHOW_TITLE'),
		'TEXT' => GetMessage('CRM_CONSTRUCTION_SHOW'),
		'ONCLICK' => "BX.Crm.Page.open('".CUtil::JSEscape($arConstruction['PATH_TO_CONSTRUCTION_SHOW'])."')",
		'DEFAULT' => true
	);
	if($arConstruction['EDIT'])
	{
		$arActions[] = array(
			'TITLE' => GetMessage('CRM_Construction_EDIT_TITLE'),
			'TEXT' => GetMessage('CRM_CONSTRUCTION_EDIT'),
			'ONCLICK' => "BX.Crm.Page.open('".CUtil::JSEscape($arConstruction['PATH_TO_CONSTRUCTION_EDIT'])."')"
		);
		$arActions[] = array(
			'TITLE' => GetMessage('CRM_CONSTRUCTION_COPY_TITLE'),
			'TEXT' => GetMessage('CRM_CONSTRUCTION_COPY'),
			'ONCLICK' => "BX.Crm.Page.open('".CUtil::JSEscape($arConstruction['PATH_TO_CONSTRUCTION_COPY'])."')",
		);
	}

	if(!$isInternal && $arConstruction['DELETE'])
	{
		$pathToRemove = CUtil::JSEscape($arConstruction['PATH_TO_CONSTRUCTION_DELETE']);
		$arActions[] = array(
			'TITLE' => GetMessage('CRM_CONSTRUCTION_DELETE_TITLE'),
			'TEXT' => GetMessage('CRM_CONSTRUCTION_DELETE'),
			'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
				'{$gridManagerID}', 
				BX.CrmUIGridMenuCommand.remove, 
				{ pathToRemove: '{$pathToRemove}' }
			)"
		);
	}

	$arActions[] = array('SEPARATOR' => true);

	if(!$isInternal)
	{
		if($arResult['PERM_DEAL'])
		{
			$arEntitySubMenuItems[] = array(
				'TITLE' => GetMessage('CRM_CONSTRUCTION_DEAL_ADD_TITLE'),
				'TEXT' => GetMessage('CRM_CONSTRUCTION_DEAL_ADD'),
				'ONCLICK' => "BX.Crm.Page.open('".CUtil::JSEscape($arConstruction['PATH_TO_DEAL_EDIT'])."')"
			);
		}

		/*if($arResult['PERM_QUOTE'])
		{
			$arEntitySubMenuItems[] = array(
				'TITLE' => GetMessage('CRM_CONSTRUCTION_ADD_QUOTE_TITLE'),
				'TEXT' => GetMessage('CRM_CONSTRUCTION_ADD_QUOTE'),
				'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arConstruction['PATH_TO_QUOTE_ADD'])."');"
			);
		}
		if($arResult['PERM_INVOICE'] && IsModuleInstalled('sale'))
		{
			$arEntitySubMenuItems[] = array(
				'TITLE' => GetMessage('CRM_DEAL_ADD_INVOICE_TITLE'),
				'TEXT' => GetMessage('CRM_DEAL_ADD_INVOICE'),
				'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arConstruction['PATH_TO_INVOICE_ADD'])."');"
			);
		}*/

		if(!empty($arEntitySubMenuItems))
		{
			$arActions[] = array(
				'TITLE' => GetMessage('CRM_CONSTRUCTION_ADD_ENTITY_TITLE'),
				'TEXT' => GetMessage('CRM_CONSTRUCTION_ADD_ENTITY'),
				'MENU' => $arEntitySubMenuItems
			);
		}

		$arActions[] = array('SEPARATOR' => true);
        $arActivityMenuItems = [];

		if($arConstruction['EDIT'])
		{
			/*$arActions[] = array(
				'TITLE' => GetMessage('CRM_CONSTRUCTION_EVENT_TITLE'),
				'TEXT' => GetMessage('CRM_CONSTRUCTION_EVENT'),
				'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
					'{$gridManagerID}', 
					BX.CrmUIGridMenuCommand.createEvent, 
					{ entityTypeName: BX.CrmEntityType.names.construction, entityId: {$arConstruction['ID']} }
				)"
			);*/

			/*if(IsModuleInstalled('subscribe'))
			{
				$arActions[] = $arActivityMenuItems[] = array(
					'TITLE' => GetMessage('CRM_CONSTRUCTION_ADD_EMAIL_TITLE'),
					'TEXT' => GetMessage('CRM_CONSTRUCTION_ADD_EMAIL'),
					'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
						'{$gridManagerID}', 
						BX.CrmUIGridMenuCommand.createActivity, 
						{ typeId: BX.CrmActivityType.email, settings: { ownerID: {$arConstruction['ID']} } }
					)"
				);
			}*/

			if(IsModuleInstalled(CRM_MODULE_CALENDAR_ID))
			{
				$arActivityMenuItems[] = array(
					'TITLE' => GetMessage('CRM_CONSTRUCTION_ADD_CALL_TITLE'),
					'TEXT' => GetMessage('CRM_CONSTRUCTION_ADD_CALL'),
					'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
						'{$gridManagerID}', 
						BX.CrmUIGridMenuCommand.createActivity, 
						{ typeId: BX.CrmActivityType.call, settings: { ownerID: {$arConstruction['ID']} } }
					)"
				);

				$arActivityMenuItems[] = array(
					'TITLE' => GetMessage('CRM_CONSTRUCTION_ADD_MEETING_TITLE'),
					'TEXT' => GetMessage('CRM_CONSTRUCTION_ADD_MEETING'),
					'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
						'{$gridManagerID}', 
						BX.CrmUIGridMenuCommand.createActivity, 
						{ typeId: BX.CrmActivityType.meeting, settings: { ownerID: {$arConstruction['ID']} } }
					)"
				);

				$arActivitySubMenuItems[] = array(
					'TITLE' => GetMessage('CRM_CONSTRUCTION_ADD_CALL_TITLE'),
					'TEXT' => GetMessage('CRM_CONSTRUCTION_ADD_CALL_SHORT'),
					'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
						'{$gridManagerID}', 
						BX.CrmUIGridMenuCommand.createActivity, 
						{ typeId: BX.CrmActivityType.call, settings: { ownerID: {$arConstruction['ID']} } }
					)"
				);

				$arActivitySubMenuItems[] = array(
					'TITLE' => GetMessage('CRM_CONSTRUCTION_ADD_MEETING_TITLE'),
					'TEXT' => GetMessage('CRM_CONSTRUCTION_ADD_MEETING_SHORT'),
					'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
						'{$gridManagerID}', 
						BX.CrmUIGridMenuCommand.createActivity, 
						{ typeId: BX.CrmActivityType.meeting, settings: { ownerID: {$arConstruction['ID']} } }
					)"
				);
			}

			if(IsModuleInstalled('tasks'))
			{
				$arActivityMenuItems[] = array(
					'TITLE' => GetMessage('CRM_CONSTRUCTION_TASK_TITLE'),
					'TEXT' => GetMessage('CRM_CONSTRUCTION_TASK'),
					'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
						'{$gridManagerID}', 
						BX.CrmUIGridMenuCommand.createActivity, 
						{ typeId: BX.CrmActivityType.task, settings: { ownerID: {$arConstruction['ID']} } }
					)"
				);

				$arActivitySubMenuItems[] = array(
					'TITLE' => GetMessage('CRM_CONSTRUCTION_TASK_TITLE'),
					'TEXT' => GetMessage('CRM_CONSTRUCTION_TASK_SHORT'),
					'ONCLICK' => "BX.CrmUIGridExtension.processMenuCommand(
						'{$gridManagerID}', 
						BX.CrmUIGridMenuCommand.createActivity, 
						{ typeId: BX.CrmActivityType.task, settings: { ownerID: {$arConstruction['ID']} } }
					)"
				);
			}

			if(!empty($arActivitySubMenuItems))
			{
				$arActions[] = array(
					'TITLE' => GetMessage('CRM_CONSTRUCTION_ADD_ACTIVITY_TITLE'),
					'TEXT' => GetMessage('CRM_CONSTRUCTION_ADD_ACTIVITY'),
					'MENU' => $arActivitySubMenuItems
				);
			}

			/*if($arResult['IS_BIZPROC_AVAILABLE'])
			{
				$arActions[] = array('SEPARATOR' => true);

				if(isset($arConstruction['PATH_TO_BIZPROC_LIST']) && $arConstruction['PATH_TO_BIZPROC_LIST'] !== '')
					$arActions[] = array(
						'TITLE' => GetMessage('CRM_CONSTRUCTION_BIZPROC_TITLE'),
						'TEXT' => GetMessage('CRM_CONSTRUCTION_BIZPROC'),
						'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arConstruction['PATH_TO_BIZPROC_LIST'])."');"
					);
				if(!empty($arConstruction['BIZPROC_LIST']))
				{
					$arBizprocList = array();
					foreach($arConstruction['BIZPROC_LIST'] as $arBizproc)
					{
						$arBizprocList[] = array(
							'TITLE' => $arBizproc['DESCRIPTION'],
							'TEXT' => $arBizproc['NAME'],
							'ONCLICK' => isset($arBizproc['ONCLICK']) ?
								$arBizproc['ONCLICK']
								: "jsUtils.Redirect([], '".CUtil::JSEscape($arBizproc['PATH_TO_BIZPROC_START'])."');"
						);
					}
					$arActions[] = array(
						'TITLE' => GetMessage('CRM_CONSTRUCTION_BIZPROC_LIST_TITLE'),
						'TEXT' => GetMessage('CRM_CONSTRUCTION_BIZPROC_LIST'),
						'MENU' => $arBizprocList
					);
				}
			}*/
		}
	}

    /*$eventParam = array(
        'ID' => $arConstruction['ID'],
        'CALL_LIST_ID' => $arResult['CALL_LIST_ID'],
        'CALL_LIST_CONTEXT' => $arResult['CALL_LIST_CONTEXT'],
        'GRID_ID' => $arResult['GRID_ID']
    );
    foreach(GetModuleEvents('crm', 'onCrmConstructionListItemBuildMenu', true) as $event)
    {
        ExecuteModuleEventEx($event, array('CRM_CONSTRUCTION_LIST_MENU', $eventParam, &$arActions));
    }*/

	$_sBPHint = 'class="'.($arConstruction['BIZPROC_STATUS'] != '' ? 'bizproc bizproc_status_'.$arConstruction['BIZPROC_STATUS'] : '').'"
				'.($arConstruction['BIZPROC_STATUS_HINT'] != '' ? 'onmouseover="BX.hint(this, \''.CUtil::JSEscape($arConstruction['BIZPROC_STATUS_HINT']).'\');"' : '');

	$resultItem = array(
		'id' => $arConstruction['ID'],
		'actions' => $arActions,
		'data' => $arConstruction,
		'editable' => !$arConstruction['EDIT'] ? ($arResult['INTERNAL'] ? 'N' : $arColumns) : 'Y',
		'columns' => array(
			'CONSTRUCTION_SUMMARY' => CCrmViewHelper::RenderConstructionName(
				$arConstruction['PATH_TO_CONSTRUCTION_SHOW'],
				$arConstruction['NAME'],
				/*Tracking\UI\Grid::enrichSourceName(
					\CCrmOwnerType::Construction,
					$arConstruction['ID'],
					$arConstruction['CONSTRUCTION_TYPE_NAME']
				),*/
				'Дополнительное поле',
				'_top'
			),
			'CONSTRUCTION_COMPANY' => isset($arConstruction['COMPANY_INFO']) ? CCrmViewHelper::PrepareClientInfo($arConstruction['COMPANY_INFO']) : '',
			'COMPANY_ID' => isset($arConstruction['COMPANY_INFO']) ? CCrmViewHelper::PrepareClientInfo($arConstruction['COMPANY_INFO']) : '',
			'ASSIGNED_BY' => $arConstruction['~ASSIGNED_BY_ID'] > 0
				? CCrmViewHelper::PrepareUserBaloonHtml(
					array(
						'PREFIX' => "CONSTRUCTION_{$arConstruction['~ID']}_RESPONSIBLE",
						'USER_ID' => $arConstruction['~ASSIGNED_BY_ID'],
						'USER_NAME'=> $arConstruction['ASSIGNED_BY'],
						'USER_PROFILE_URL' => $arConstruction['PATH_TO_USER_PROFILE']
					)
				) : '',
			'COMMENTS' => htmlspecialcharsback($arConstruction['COMMENTS']),
			'SOURCE_DESCRIPTION' => nl2br($arConstruction['SOURCE_DESCRIPTION']),
			'DATE_CREATE' => FormatDate($arResult['TIME_FORMAT'], MakeTimeStamp($arConstruction['DATE_CREATE']), $now),
			'DATE_MODIFY' => FormatDate($arResult['TIME_FORMAT'], MakeTimeStamp($arConstruction['DATE_MODIFY']), $now),
			'HONORIFIC' => isset($arResult['HONORIFIC'][$arConstruction['HONORIFIC']]) ? $arResult['HONORIFIC'][$arConstruction['HONORIFIC']] : '',
			'TYPE_ID' => isset($arResult['TYPE_LIST'][$arConstruction['TYPE_ID']]) ? $arResult['TYPE_LIST'][$arConstruction['TYPE_ID']] : $arConstruction['TYPE_ID'],
			'SOURCE_ID' => isset($arResult['SOURCE_LIST'][$arConstruction['SOURCE_ID']]) ? $arResult['SOURCE_LIST'][$arConstruction['SOURCE_ID']] : $arConstruction['SOURCE_ID'],
			'WEBFORM_ID' => isset($arResult['WEBFORM_LIST'][$arConstruction['WEBFORM_ID']]) ? $arResult['WEBFORM_LIST'][$arConstruction['WEBFORM_ID']] : $arConstruction['WEBFORM_ID'],
			'CREATED_BY' => $arConstruction['~CREATED_BY'] > 0
				? CCrmViewHelper::PrepareUserBaloonHtml(
					array(
						'PREFIX' => "CONSTRUCTION_{$arConstruction['~ID']}_CREATOR",
						'USER_ID' => $arConstruction['~CREATED_BY'],
						'USER_NAME'=> $arConstruction['CREATED_BY_FORMATTED_NAME'],
						'USER_PROFILE_URL' => $arConstruction['PATH_TO_USER_CREATOR']
					)
				) : '',
			'MODIFY_BY' => $arConstruction['~MODIFY_BY'] > 0
				? CCrmViewHelper::PrepareUserBaloonHtml(
					array(
						'PREFIX' => "CONSTRUCTION_{$arConstruction['~ID']}_MODIFIER",
						'USER_ID' => $arConstruction['~MODIFY_BY'],
						'USER_NAME'=> $arConstruction['MODIFY_BY_FORMATTED_NAME'],
						'USER_PROFILE_URL' => $arConstruction['PATH_TO_USER_MODIFIER']
					)
				) : '',
		) + CCrmViewHelper::RenderListMultiFields($arConstruction, "CONSTRUCTION_{$arConstruction['ID']}_", array('ENABLE_SIP' => true, 'SIP_PARAMS' => array('ENTITY_TYPE' => 'CRM_'.CCrmOwnerType::ConstructionName, 'ENTITY_ID' => $arConstruction['ID']))) + $arResult['CONSTRUCTION_UF'][$sKey]
	);

	Tracking\UI\Grid::appendRows(
		\CCrmOwnerType::Construction,
		$arConstruction['ID'],
		$resultItem['columns']
	);

	if($arResult['ENABLE_OUTMODED_FIELDS'])
	{
		$resultItem['columns']['ADDRESS'] = nl2br($arConstruction['ADDRESS']);
	}

	if(isset($arConstruction['~BIRTHDATE']))
	{
		$resultItem['columns']['BIRTHDATE'] = FormatDate('SHORT', MakeTimeStamp($arConstruction['~BIRTHDATE']));
	}

	$userActivityID = isset($arConstruction['~ACTIVITY_ID']) ? intval($arConstruction['~ACTIVITY_ID']) : 0;
	$commonActivityID = isset($arConstruction['~C_ACTIVITY_ID']) ? intval($arConstruction['~C_ACTIVITY_ID']) : 0;
	if($userActivityID > 0)
	{
		$resultItem['columns']['ACTIVITY_ID'] = CCrmViewHelper::RenderNearestActivity(
			array(
				'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName(CCrmOwnerType::Construction),
				'ENTITY_ID' => $arConstruction['~ID'],
				'ENTITY_RESPONSIBLE_ID' => $arConstruction['~ASSIGNED_BY'],
				'GRID_MANAGER_ID' => $gridManagerID,
				'ACTIVITY_ID' => $userActivityID,
				'ACTIVITY_SUBJECT' => isset($arConstruction['~ACTIVITY_SUBJECT']) ? $arConstruction['~ACTIVITY_SUBJECT'] : '',
				'ACTIVITY_TIME' => isset($arConstruction['~ACTIVITY_TIME']) ? $arConstruction['~ACTIVITY_TIME'] : '',
				'ACTIVITY_EXPIRED' => isset($arConstruction['~ACTIVITY_EXPIRED']) ? $arConstruction['~ACTIVITY_EXPIRED'] : '',
				'ALLOW_EDIT' => $arConstruction['EDIT'],
				'MENU_ITEMS' => $arActivityMenuItems,
				'USE_GRID_EXTENSION' => true
			)
		);

		$counterData = array(
			'CURRENT_USER_ID' => $currentUserID,
			'ENTITY' => $arConstruction,
			'ACTIVITY' => array(
				'RESPONSIBLE_ID' => $currentUserID,
				'TIME' => isset($arConstruction['~ACTIVITY_TIME']) ? $arConstruction['~ACTIVITY_TIME'] : '',
				'IS_CURRENT_DAY' => isset($arConstruction['~ACTIVITY_IS_CURRENT_DAY']) ? $arConstruction['~ACTIVITY_IS_CURRENT_DAY'] : false
			)
		);

		if(CCrmUserCounter::IsReckoned(CCrmUserCounter::CurrentConstructionActivies, $counterData))
		{
			$resultItem['columnClasses'] = array('ACTIVITY_ID' => 'crm-list-deal-today');
		}
	}
	elseif($commonActivityID > 0)
	{
		$resultItem['columns']['ACTIVITY_ID'] = CCrmViewHelper::RenderNearestActivity(
			array(
				'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName(CCrmOwnerType::Construction),
				'ENTITY_ID' => $arConstruction['~ID'],
				'ENTITY_RESPONSIBLE_ID' => $arConstruction['~ASSIGNED_BY'],
				'GRID_MANAGER_ID' => $gridManagerID,
				'ACTIVITY_ID' => $commonActivityID,
				'ACTIVITY_SUBJECT' => isset($arConstruction['~C_ACTIVITY_SUBJECT']) ? $arConstruction['~C_ACTIVITY_SUBJECT'] : '',
				'ACTIVITY_TIME' => isset($arConstruction['~C_ACTIVITY_TIME']) ? $arConstruction['~C_ACTIVITY_TIME'] : '',
				'ACTIVITY_RESPONSIBLE_ID' => isset($arConstruction['~C_ACTIVITY_RESP_ID']) ? intval($arConstruction['~C_ACTIVITY_RESP_ID']) : 0,
				'ACTIVITY_RESPONSIBLE_LOGIN' => isset($arConstruction['~C_ACTIVITY_RESP_LOGIN']) ? $arConstruction['~C_ACTIVITY_RESP_LOGIN'] : '',
				'ACTIVITY_RESPONSIBLE_NAME' => isset($arConstruction['~C_ACTIVITY_RESP_NAME']) ? $arConstruction['~C_ACTIVITY_RESP_NAME'] : '',
				'ACTIVITY_RESPONSIBLE_LAST_NAME' => isset($arConstruction['~C_ACTIVITY_RESP_LAST_NAME']) ? $arConstruction['~C_ACTIVITY_RESP_LAST_NAME'] : '',
				'ACTIVITY_RESPONSIBLE_SECOND_NAME' => isset($arConstruction['~C_ACTIVITY_RESP_SECOND_NAME']) ? $arConstruction['~C_ACTIVITY_RESP_SECOND_NAME'] : '',
				'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
				'PATH_TO_USER_PROFILE' => $arParams['PATH_TO_USER_PROFILE'],
				'ALLOW_EDIT' => $arConstruction['EDIT'],
				'MENU_ITEMS' => $arActivityMenuItems,
				'USE_GRID_EXTENSION' => true
			)
		);
	}
	else
	{
		$resultItem['columns']['ACTIVITY_ID'] = CCrmViewHelper::RenderNearestActivity(
			array(
				'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName(CCrmOwnerType::Construction),
				'ENTITY_ID' => $arConstruction['~ID'],
				'ENTITY_RESPONSIBLE_ID' => $arConstruction['~ASSIGNED_BY'],
				'GRID_MANAGER_ID' => $gridManagerID,
				'ALLOW_EDIT' => $arConstruction['EDIT'],
				'MENU_ITEMS' => $arActivityMenuItems,
				'USE_GRID_EXTENSION' => true
			)
		);
	}

	$arResult['GRID_DATA'][] = &$resultItem;
	unset($resultItem);
}

if($arResult['ENABLE_TOOLBAR'])
{
	$APPLICATION->IncludeComponent(
		'bitrix:crm.interface.toolbar',
		'',
		array(
			'TOOLBAR_ID' => strtolower($arResult['GRID_ID']).'_toolbar',
			'BUTTONS' => array(
				array(
					'TEXT' => GetMessage('CRM_CONSTRUCTION_LIST_ADD_SHORT'),
					'TITLE' => GetMessage('CRM_CONSTRUCTION_LIST_ADD'),
					'LINK' => $arResult['PATH_TO_CONSTRUCTION_ADD'],
					'ICON' => 'btn-new'
				)
			)
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);
}

//region Action Panel
$controlPanel = array('GROUPS' => array(array('ITEMS' => array())));

if(!$isInternal
	&& ($allowWrite || $allowDelete || $callListUpdateMode))
{
	$yesnoList = array(
		array('NAME' => GetMessage('MAIN_YES'), 'VALUE' => 'Y'),
		array('NAME' => GetMessage('MAIN_NO'), 'VALUE' => 'N')
	);

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

	$actionList = array(array('NAME' => GetMessage('CRM_CONSTRUCTION_LIST_CHOOSE_ACTION'), 'VALUE' => 'none'));

	if($allowWrite)
	{
		//region Add letter & Add to segment
//		Integration\Sender\GridPanel::appendActions($actionList, $applyButton, $gridManagerID);
		//endregion
		//region Add Task
		if (IsModuleInstalled('tasks'))
		{
			$actionList[] = array(
				'NAME' => GetMessage('CRM_CONSTRUCTION_TASK'),
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
		}
		//endregion
		//region Assign To
		//region Render User Search control
		if(!Bitrix\Main\Grid\Context::isInternalRequest())
		{
			//action_assigned_by_search + _control
			//Prefix control will be added by main.ui.grid
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
		//endregion
		$actionList[] = array(
			'NAME' => GetMessage('CRM_CONSTRUCTION_ASSIGN_TO'),
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
		//endregion
		//region Create call list
		/*if(IsModuleInstalled('voximplant'))
		{
			$actionList[] = array(
				'NAME' => GetMessage('CRM_CONSTRUCTION_CREATE_CALL_LIST'),
				'VALUE' => 'create_call_list',
				'ONCHANGE' => array(
					array(
						'ACTION' => Bitrix\Main\Grid\Panel\Actions::CREATE,
						'DATA' => array(
							$applyButton
						)
					),
					array(
						'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
						'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processActionChange('{$gridManagerID}', 'create_call_list')"))
					)
				)
			);
		}*/
		//endregion
	}

	if($allowDelete)
	{
		//region Remove button
		//$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getRemoveButton();
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
		$controlPanel['GROUPS'][0]['ITEMS'][] = $button;
		//endregion

		//$actionList[] = $snippet->getRemoveAction();
		$actionList[] = array(
			'NAME' => GetMessage('CRM_CONSTRUCTION_ACTION_DELETE'),
			'VALUE' => 'delete',
			'ONCHANGE' => array(
				array(
					'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
					'DATA' => array(array('JS' => "BX.CrmUIGridExtension.applyAction('{$gridManagerID}', 'delete')"))
				)
			)
		);
	}

	if($allowWrite)
	{
		//region Edit Button
		$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getEditButton();
		$actionList[] = $snippet->getEditAction();
		//endregion
		//region Mark as Opened
		/*$actionList[] = array(
			'NAME' => GetMessage('CRM_CONSTRUCTION_MARK_AS_OPENED'),
			'VALUE' => 'mark_as_opened',
			'ONCHANGE' => array(
				array(
					'ACTION' => Bitrix\Main\Grid\Panel\Actions::CREATE,
					'DATA' => array(
						array(
							'TYPE' => Bitrix\Main\Grid\Panel\Types::DROPDOWN,
							'ID' => 'action_opened',
							'NAME' => 'ACTION_OPENED',
							'ITEMS' => $yesnoList
						),
						$applyButton
					)
				),
				array(
					'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
					'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processActionChange('{$gridManagerID}', 'mark_as_opened')"))
				)
			)
		);*/
		//endregion
		//region Export
		/*$actionList[] = array(
			'NAME' => GetMessage('CRM_CONSTRUCTION_EXPORT'),
			'VALUE' => 'export',
			'ONCHANGE' => array(
				array(
					'ACTION' => Bitrix\Main\Grid\Panel\Actions::CREATE,
					'DATA' => array(
						array(
							'TYPE' => Bitrix\Main\Grid\Panel\Types::DROPDOWN,
							'ID' => 'action_export',
							'NAME' => 'ACTION_EXPORT',
							'ITEMS' => $yesnoList
						),
						$applyButton
					)
				),
				array(
					'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
					'DATA' => array(array('JS' => "BX.CrmUIGridExtension.processActionChange('{$gridManagerID}', 'mark_as_opened')"))
				)
			)
		);*/
		//endregion
	}

	if($callListUpdateMode)
	{
		$controlPanel['GROUPS'][0]['ITEMS'][] = array(
			"TYPE" => \Bitrix\Main\Grid\Panel\Types::BUTTON,
			"TEXT" => GetMessage("CRM_CONSTRUCTION_UPDATE_CALL_LIST"),
			"ID" => "update_call_list",
			"NAME" => "update_call_list",
			'ONCHANGE' => array(
				array(
					'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
					'DATA' => array(array('JS' => "BX.CrmUIGridExtension.updateCallList('{$gridManagerID}', {$arResult['CALL_LIST_ID']}, '{$arResult['CALL_LIST_CONTEXT']}')"))
				)
			)
		);
	}
	else
	{
		//region Create & start call list
		if(IsModuleInstalled('voximplant'))
		{
			$controlPanel['GROUPS'][0]['ITEMS'][] = array(
				"TYPE" => \Bitrix\Main\Grid\Panel\Types::BUTTON,
				"TEXT" => GetMessage('CRM_CONSTRUCTION_START_CALL_LIST'),
				"VALUE" => "start_call_list",
				"ONCHANGE" => array(
					array(
						"ACTION" => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
						"DATA" => array(array('JS' => "BX.CrmUIGridExtension.createCallList('{$gridManagerID}', true)"))
					)
				)
			);
		}
		//endregion
		$controlPanel['GROUPS'][0]['ITEMS'][] = array(
			"TYPE" => \Bitrix\Main\Grid\Panel\Types::DROPDOWN,
			"ID" => "action_button_{$prefix}",
			"NAME" => "action_button_{$prefix}",
			"ITEMS" => $actionList
		);
	}

	$controlPanel['GROUPS'][0]['ITEMS'][] = $snippet->getForAllCheckbox();
}
//endregion

$APPLICATION->IncludeComponent(
	'bitrix:crm.newentity.counter.panel',
	'',
	array(
		'ENTITY_TYPE_NAME' => CCrmOwnerType::ConstructionName,
		'GRID_ID' => $arResult['GRID_ID']
	),
	$component
);

$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.grid',
	'titleflex',
	array(
		'GRID_ID' => $arResult['GRID_ID'],
		'HEADERS' => $arResult['HEADERS'],
		'SORT' => $arResult['SORT'],
		'SORT_VARS' => $arResult['SORT_VARS'],
		'ROWS' => $arResult['GRID_DATA'],
		'FORM_ID' => $arResult['FORM_ID'],
		'TAB_ID' => $arResult['TAB_ID'],
		'AJAX_ID' => $arResult['AJAX_ID'],
		'AJAX_OPTION_JUMP' => $arResult['AJAX_OPTION_JUMP'],
		'AJAX_OPTION_HISTORY' => $arResult['AJAX_OPTION_HISTORY'],
		'FILTER' => $arResult['FILTER'],
		'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
		'FILTER_PARAMS' => array(
			'LAZY_LOAD' => array(
				'GET_LIST' => '/local/components/citfact/crm.construction.list/filter.ajax.php?action=list&filter_id='.urlencode($arResult['GRID_ID']).'&siteID='.SITE_ID.'&'.bitrix_sessid_get(),
				'GET_FIELD' => '/local/components/citfact/crm.construction.list/filter.ajax.php?action=field&filter_id='.urlencode($arResult['GRID_ID']).'&siteID='.SITE_ID.'&'.bitrix_sessid_get(),
			)
		),
		'ENABLE_LIVE_SEARCH' => true,
		'ACTION_PANEL' => $controlPanel,
		'PAGINATION' => isset($arResult['PAGINATION']) && is_array($arResult['PAGINATION'])
			? $arResult['PAGINATION'] : array(),
		'ENABLE_ROW_COUNT_LOADER' => true,
		'PRESERVE_HISTORY' => $arResult['PRESERVE_HISTORY'],
		'NAVIGATION_BAR' => array(
			'ITEMS' => array(
				array(
					//'icon' => 'table',
					'id' => 'list',
					'name' => GetMessage('CRM_CONSTRUCTION_LIST_FILTER_NAV_BUTTON_LIST'),
					'active' => true,
					'url' => $arParams['PATH_TO_CONSTRUCTION_LIST'],
				),
				/*array(
					//'icon' => 'chart',
					'id' => 'widget',
					'name' => GetMessage('CRM_CONSTRUCTION_LIST_FILTER_NAV_BUTTON_WIDGET'),
					'active' => false,
					'url' => $arParams['PATH_TO_CONSTRUCTION_WIDGET']
				)*/
			),
			'BINDING' => array(
				'category' => 'crm.navigation',
				'name' => 'index',
				'key' => strtolower($arResult['NAVIGATION_CONTEXT_ID'])
			)
		),
		'IS_EXTERNAL_FILTER' => $arResult['IS_EXTERNAL_FILTER'],
		'EXTENSION' => array(
			'ID' => $gridManagerID,
			'CONFIG' => array(
				'ownerTypeName' => CCrmOwnerType::ConstructionName,
				'gridId' => $arResult['GRID_ID'],
				'activityEditorId' => $activityEditorID,
				'activityServiceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
				'taskCreateUrl'=> isset($arResult['TASK_CREATE_URL']) ? $arResult['TASK_CREATE_URL'] : '',
				'serviceUrl' => '/local/components/citfact/crm.construction.list/list.ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
				'loaderData' => isset($arParams['AJAX_LOADER']) ? $arParams['AJAX_LOADER'] : null
			),
			'MESSAGES' => array(
				'deletionDialogTitle' => GetMessage('CRM_CONSTRUCTION_DELETE_TITLE'),
				'deletionDialogMessage' => GetMessage('CRM_CONSTRUCTION_DELETE_CONFIRM'),
				'deletionDialogButtonTitle' => GetMessage('CRM_CONSTRUCTION_DELETE'),
				'processExportDialogTitle' => GetMessageJS('CRM_CONSTRUCTION_EXPORT_DIALOG_TITLE'),
				'processExportDialogSummary' => GetMessageJS('CRM_CONSTRUCTION_EXPORT_DIALOG_SUMMARY'),
			)
		)
	)
);
?><script type="text/javascript">
BX.ready(
		function()
		{
			BX.CrmSipManager.getCurrent().setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::ConstructionName)?>",
				"/local/components/citfact/crm.construction.show/ajax.php?<?=bitrix_sessid_get()?>"
			);

			if(typeof(BX.CrmSipManager.messages) === 'undefined')
			{
				BX.CrmSipManager.messages =
				{
					"unknownRecipient": "<?= GetMessageJS('CRM_SIP_MGR_UNKNOWN_RECIPIENT')?>",
					"makeCall": "<?= GetMessageJS('CRM_SIP_MGR_MAKE_CALL')?>"
				};
			}
		}
);
</script>

<?if(!$isInternal):?>
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
<?endif;?>
<script type="text/javascript">
BX.ready(
	function()
	{
		BX.CrmLongRunningProcessDialog.messages =
		{
			startButton: "<?=GetMessageJS('CRM_CONSTRUCTION_LRP_DLG_BTN_START')?>",
			stopButton: "<?=GetMessageJS('CRM_CONSTRUCTION_LRP_DLG_BTN_STOP')?>",
			closeButton: "<?=GetMessageJS('CRM_CONSTRUCTION_LRP_DLG_BTN_CLOSE')?>",
			wait: "<?=GetMessageJS('CRM_CONSTRUCTION_LRP_DLG_WAIT')?>",
			requestError: "<?=GetMessageJS('CRM_CONSTRUCTION_LRP_DLG_REQUEST_ERR')?>"
		};

		var gridId = "<?= CUtil::JSEscape($arResult['GRID_ID'])?>";
		BX.Crm.BatchDeletionManager.create(
			gridId,
			{
				gridId: gridId,
				entityTypeId: <?=CCrmOwnerType::Construction?>,
				container: "batchDeletionWrapper",
				stateTemplate: "<?=GetMessageJS('CRM_CONSTRUCTION_STEPWISE_STATE_TEMPLATE')?>",
				messages:
					{
						title: "<?=GetMessageJS('CRM_CONSTRUCTION_LIST_DEL_PROC_DLG_TITLE')?>",
						confirmation: "<?=GetMessageJS('CRM_CONSTRUCTION_LIST_DEL_PROC_DLG_SUMMARY')?>",
						summaryCaption: "<?=GetMessageJS('CRM_CONSTRUCTION_BATCH_DELETION_COMPLETED')?>",
						summarySucceeded: "<?=GetMessageJS('CRM_CONSTRUCTION_BATCH_DELETION_COUNT_SUCCEEDED')?>",
						summaryFailed: "<?=GetMessageJS('CRM_CONSTRUCTION_BATCH_DELETION_COUNT_FAILED')?>"
					}
			}
		);

		BX.Crm.AnalyticTracker.config =
			{
				id: "construction_list",
				settings: { params: <?=CUtil::PhpToJSObject($arResult['ANALYTIC_TRACKER'])?> }
			};
	}
);
</script>
<?if($arResult['NEED_FOR_REBUILD_DUP_INDEX']):?>
<script type="text/javascript">
BX.ready(
	function()
	{
		BX.CrmDuplicateManager.messages =
		{
			rebuildconstructionIndexDlgTitle: "<?=GetMessageJS('CRM_CONSTRUCTION_REBUILD_DUP_INDEX_DLG_TITLE')?>",
			rebuildconstructionIndexDlgSummary: "<?=GetMessageJS('CRM_CONSTRUCTION_REBUILD_DUP_INDEX_DLG_SUMMARY')?>"
		};
		var mgr = BX.CrmDuplicateManager.create("mgr", { entityTypeName: "<?=CUtil::JSEscape(CCrmOwnerType::ConstructionName)?>", serviceUrl: "<?=SITE_DIR?>local/components/citfact/crm.construction.list/list.ajax.php?&<?=bitrix_sessid_get()?>" });
		BX.addCustomEvent(
			mgr,
			'ON_CONSTRUCTION_INDEX_REBUILD_COMPLETE',
			function()
			{
				var msg = BX("rebuildConstructionDupIndexMsg");
				if(msg)
				{
					msg.style.display = "none";
				}
			}
		);

		var link = BX("rebuildConstructionDupIndexLink");
		if(link)
		{
			BX.bind(
				link,
				"click",
				function(e)
				{
					mgr.rebuildIndex();
					return BX.PreventDefault(e);
				}
			);
		}
	}
);
</script>
<?endif;?>
<?if($arResult['NEED_FOR_REBUILD_SEARCH_CONTENT']):?>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				if(BX.AutorunProcessPanel.isExists("rebuildConstructionSearch"))
				{
					return;
				}

				BX.AutorunProcessManager.messages =
					{
						title: "<?=GetMessageJS('CRM_CONSTRUCTION_REBUILD_SEARCH_CONTENT_DLG_TITLE')?>",
						stateTemplate: "<?=GetMessageJS('CRM_REBUILD_SEARCH_CONTENT_STATE')?>"
					};
				var manager = BX.AutorunProcessManager.create("rebuildConstructionSearch",
					{
						serviceUrl: "<?='/local/components/citfact/crm.construction.list/list.ajax.php?'.bitrix_sessid_get()?>",
						actionName: "REBUILD_SEARCH_CONTENT",
						container: "rebuildConstructionSearchWrapper",
						enableLayout: true
					}
				);
				manager.runAfter(100);
			}
		);
	</script>
<?endif;?>
<?if($arResult['NEED_FOR_BUILD_TIMELINE']):?>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				if(BX.AutorunProcessPanel.isExists("buildConstructionTimeline"))
				{
					return;
				}

				BX.AutorunProcessManager.messages =
				{
					title: "<?=GetMessageJS('CRM_CONSTRUCTION_BUILD_TIMELINE_DLG_TITLE')?>",
					stateTemplate: "<?=GetMessageJS('CRM_CONSTRUCTION_BUILD_TIMELINE_STATE')?>"
				};
				var manager = BX.AutorunProcessManager.create("buildConstructionTimeline",
					{
						serviceUrl: "<?='/local/components/citfact/crm.construction.list/list.ajax.php?'.bitrix_sessid_get()?>",
						actionName: "BUILD_TIMELINE",
						container: "buildConstructionTimelineWrapper",
						enableLayout: true
					}
				);
				manager.runAfter(100);
			}
		);
	</script>
<?endif;?>
<?if($arResult['NEED_FOR_BUILD_DUPLICATE_INDEX']):?>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				if(BX.AutorunProcessPanel.isExists("buildConstructionDuplicateIndex"))
				{
					return;
				}

				BX.AutorunProcessManager.messages =
					{
						title: "<?=GetMessageJS('CRM_CONSTRUCTION_BUILD_DUPLICATE_INDEX_DLG_TITLE')?>",
						stateTemplate: "<?=GetMessageJS('CRM_CONSTRUCTION_BUILD_DUPLICATE_INDEX_STATE')?>"
					};
				var manager = BX.AutorunProcessManager.create("buildConstructionDuplicateIndex",
					{
						serviceUrl: "<?='/local/components/citfact/crm.construction.list/list.ajax.php?'.bitrix_sessid_get()?>",
						actionName: "BUILD_DUPLICATE_INDEX",
						container: "buildConstructionDuplicateIndexWrapper",
						enableLayout: true
					}
				);
				manager.runAfter(100);
			}
		);
	</script>
<?endif;?>
<?if($arResult['NEED_FOR_REBUILD_CONSTRUCTION_ATTRS']):?>
<script type="text/javascript">
BX.ready(
	function()
	{
		var link = BX("rebuildConstructionAttrsLink");
		if(link)
		{
			BX.bind(
				link,
				"click",
				function(e)
				{
					var msg = BX("rebuildConstructionAttrsMsg");
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
<?endif;?>
<?if($arResult['NEED_FOR_TRANSFER_REQUISITES']):?>
<script type="text/javascript">
BX.ready(
	function()
	{
		BX.CrmRequisitePresetSelectDialog.messages =
		{
			title: "<?=GetMessageJS("CRM_CONSTRUCTION_RQ_TX_SELECTOR_TITLE")?>",
			presetField: "<?=GetMessageJS("CRM_CONSTRUCTION_RQ_TX_SELECTOR_FIELD")?>"
		};

		BX.CrmRequisiteConverter.messages =
		{
			processDialogTitle: "<?=GetMessageJS('CRM_CONSTRUCTION_RQ_TX_PROC_DLG_TITLE')?>",
			processDialogSummary: "<?=GetMessageJS('CRM_CONSTRUCTION_RQ_TX_PROC_DLG_DLG_SUMMARY')?>"
		};

		var converter = BX.CrmRequisiteConverter.create(
			"converter",
			{
				entityTypeName: "<?=CUtil::JSEscape(CCrmOwnerType::ConstructionName)?>",
				serviceUrl: "<?=SITE_DIR?>local/components/citfact/crm.construction.list/list.ajax.php?&<?=bitrix_sessid_get()?>"
			}
		);

		BX.addCustomEvent(
			converter,
			'ON_CONSTRUCTION_REQUISITE_TRANFER_COMPLETE',
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
<?endif;?>
