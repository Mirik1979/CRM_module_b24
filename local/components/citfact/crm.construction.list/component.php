<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * @global \CMain $APPLICATION
 * @global \CUser $USER
 * @global \CDatabase $DB
 * @var \CUserTypeManager $USER_FIELD_MANAGER
 * @var \CBitrixComponent $this
 * @var array $arParams
 * @var array $arResult
 */

global $USER_FIELD_MANAGER, $USER, $APPLICATION, $DB;

$isErrorOccured = false;
$errorMessage = '';

if (!CModule::IncludeModule('crm'))
{
	$errorMessage = GetMessage('CRM_MODULE_NOT_INSTALLED');
	$isErrorOccured = true;
}

$isBizProcInstalled = IsModuleInstalled('bizproc');
if (!$isErrorOccured && $isBizProcInstalled)
{
	if (!CModule::IncludeModule('bizproc'))
	{
		$errorMessage = GetMessage('BIZPROC_MODULE_NOT_INSTALLED');
		$isErrorOccured = true;
	}
	elseif (!CBPRuntime::isFeatureEnabled())
	{
		$isBizProcInstalled = false;
	}
}

$userPermissions = CCrmPerms::GetCurrentUserPermissions();
if (!$isErrorOccured && !CCrmConstruction::CheckReadPermission(0, $userPermissions))
{
	$errorMessage = GetMessage('CRM_PERMISSION_DENIED');
	$isErrorOccured = true;
}

//region Export params
$sExportType = !empty($arParams['EXPORT_TYPE']) ?
	strval($arParams['EXPORT_TYPE']) : (!empty($_REQUEST['type']) ? strval($_REQUEST['type']) : '');
$isInExportMode = false;
$isStExport = false;    // Step-by-step export mode
if (!empty($sExportType))
{
	$sExportType = strtolower(trim($sExportType));
	switch ($sExportType)
	{
		case 'csv':
		case 'excel':
			$isInExportMode = true;
			$isStExport = (isset($arParams['STEXPORT_MODE']) && $arParams['STEXPORT_MODE'] === 'Y');
			break;
		default:
			$sExportType = '';
	}
}

$isStExportAllFields = (isset($arParams['STEXPORT_INITIAL_OPTIONS']['EXPORT_ALL_FIELDS'])
						&& $arParams['STEXPORT_INITIAL_OPTIONS']['EXPORT_ALL_FIELDS'] === 'Y');
$arResult['STEXPORT_EXPORT_ALL_FIELDS'] = ($isStExport && $isStExportAllFields) ? 'Y' : 'N';

$isStExportRequisiteMultiline = (isset($arParams['STEXPORT_INITIAL_OPTIONS']['REQUISITE_MULTILINE'])
								 && $arParams['STEXPORT_INITIAL_OPTIONS']['REQUISITE_MULTILINE'] === 'Y');
$arResult['STEXPORT_REQUISITE_MULTILINE'] = ($isStExport && $isStExportRequisiteMultiline) ? 'Y' : 'N';

$arResult['STEXPORT_MODE'] = $isStExport ? 'Y' : 'N';
$arResult['STEXPORT_TOTAL_ITEMS'] = isset($arParams['STEXPORT_TOTAL_ITEMS']) ?
	(int)$arParams['STEXPORT_TOTAL_ITEMS'] : 0;
//endregion

$CCrmConstruction = new CCrmConstruction();
if (!$isErrorOccured && $isInExportMode && $CCrmConstruction->cPerms->HavePerm('CONSTRUCTION', BX_CRM_PERM_NONE, 'EXPORT'))
{
	$errorMessage = GetMessage('CRM_PERMISSION_DENIED');
	$isErrorOccured = true;
}

if ($isErrorOccured)
{
	if ($isStExport)
	{
		return array('ERROR' => $errorMessage);
	}
	else
	{
		ShowError($errorMessage);
		return;
	}
}

use Bitrix\Main;
use Bitrix\Main\Grid\Editor;
use Bitrix\Crm;
use Bitrix\Crm\Tracking;
use Bitrix\Crm\EntityAddress;
use Bitrix\Crm\Format\AddressSeparator;
use Bitrix\Crm\ConstructionAddress;
use Bitrix\Crm\Format\ConstructionAddressFormatter;
use Bitrix\Crm\Settings\HistorySettings;
use Bitrix\Crm\Settings\ConstructionSettings;
use Bitrix\Crm\WebForm\Manager as WebFormManager;
use Bitrix\Crm\Settings\LayoutSettings;

$CCrmBizProc = new CCrmBizProc('CONSTRUCTION');

$userID = CCrmSecurityHelper::GetCurrentUserID();
$isAdmin = CCrmPerms::IsAdmin();
$enableOutmodedFields = $arResult['ENABLE_OUTMODED_FIELDS'] = ConstructionSettings::getCurrent()->areOutmodedRequisitesEnabled();

$arResult['CURRENT_USER_ID'] = CCrmSecurityHelper::GetCurrentUserID();
$arParams['PATH_TO_CONSTRUCTION_LIST'] = CrmCheckPath('PATH_TO_CONSTRUCTION_LIST', $arParams['PATH_TO_CONSTRUCTION_LIST'], $APPLICATION->GetCurPage());
$arParams['PATH_TO_CONSTRUCTION_DETAILS'] = CrmCheckPath('PATH_TO_CONSTRUCTION_DETAILS', $arParams['PATH_TO_CONSTRUCTION_DETAILS'], $APPLICATION->GetCurPage().'details/#construction_id#/');
$arParams['PATH_TO_CONSTRUCTION_SHOW'] = CrmCheckPath('PATH_TO_CONSTRUCTION_SHOW', $arParams['PATH_TO_CONSTRUCTION_SHOW'], $APPLICATION->GetCurPage().'show/#construction_id#/');
$arParams['PATH_TO_CONSTRUCTION_EDIT'] = CrmCheckPath('PATH_TO_CONSTRUCTION_EDIT', $arParams['PATH_TO_CONSTRUCTION_EDIT'], $APPLICATION->GetCurPage().'edit/#construction_id#/');
$arParams['PATH_TO_CONTACT_SHOW'] = CrmCheckPath('PATH_TO_CONTACT_SHOW', $arParams['PATH_TO_CONTACT_SHOW'], $APPLICATION->GetCurPage().'show/#contact_id#/');
$arParams['PATH_TO_COMPANY_SHOW'] = CrmCheckPath('PATH_TO_COMPANY_SHOW', $arParams['PATH_TO_COMPANY_SHOW'], $APPLICATION->GetCurPage().'show/#company_id#/');
$arParams['PATH_TO_DEAL_DETAILS'] = CrmCheckPath('PATH_TO_DEAL_DETAILS', $arParams['PATH_TO_DEAL_DETAILS'], $APPLICATION->GetCurPage().'?deal_id=#deal_id#&details');
$arParams['PATH_TO_DEAL_EDIT'] = CrmCheckPath('PATH_TO_DEAL_EDIT', $arParams['PATH_TO_DEAL_EDIT'], $APPLICATION->GetCurPage().'?deal_id=#deal_id#&edit');
$arParams['PATH_TO_QUOTE_EDIT'] = CrmCheckPath('PATH_TO_QUOTE_EDIT', $arParams['PATH_TO_QUOTE_EDIT'], $APPLICATION->GetCurPage().'?quote_id=#quote_id#&edit');
$arParams['PATH_TO_INVOICE_EDIT'] = CrmCheckPath('PATH_TO_INVOICE_EDIT', $arParams['PATH_TO_INVOICE_EDIT'], $APPLICATION->GetCurPage().'?invoice_id=#invoice_id#&edit');
$arParams['PATH_TO_USER_PROFILE'] = CrmCheckPath('PATH_TO_USER_PROFILE', $arParams['PATH_TO_USER_PROFILE'], '/company/personal/user/#user_id#/');
$arParams['PATH_TO_USER_BP'] = CrmCheckPath('PATH_TO_USER_BP', $arParams['PATH_TO_USER_BP'], '/company/personal/bizproc/');
$arParams['PATH_TO_CONSTRUCTION_WIDGET'] = CrmCheckPath('PATH_TO_CONSTRUCTION_WIDGET', $arParams['PATH_TO_CONSTRUCTION_WIDGET'], $APPLICATION->GetCurPage());
$arParams['PATH_TO_CONSTRUCTION_PORTRAIT'] = CrmCheckPath('PATH_TO_CONSTRUCTION_PORTRAIT', $arParams['PATH_TO_CONSTRUCTION_PORTRAIT'], $APPLICATION->GetCurPage());
$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$arResult['IS_AJAX_CALL'] = isset($_REQUEST['AJAX_CALL']) || isset($_REQUEST['ajax_request']) || !!CAjax::GetSession();
$arResult['SESSION_ID'] = bitrix_sessid();
$arResult['NAVIGATION_CONTEXT_ID'] = isset($arParams['NAVIGATION_CONTEXT_ID']) ? $arParams['NAVIGATION_CONTEXT_ID'] : '';
$arResult['PRESERVE_HISTORY'] = isset($arParams['PRESERVE_HISTORY']) ? $arParams['PRESERVE_HISTORY'] : false;
$arResult['ENABLE_SLIDER'] = \Bitrix\Crm\Settings\LayoutSettings::getCurrent()->isSliderEnabled();

if(LayoutSettings::getCurrent()->isSimpleTimeFormatEnabled())
{
	$arResult['TIME_FORMAT'] = array(
		'tommorow' => 'tommorow',
		's' => 'sago',
		'i' => 'iago',
		'H3' => 'Hago',
		'today' => 'today',
		'yesterday' => 'yesterday',
		//'d7' => 'dago',
		'-' => Main\Type\DateTime::convertFormatToPhp(FORMAT_DATE)
	);
}
else
{
	$arResult['TIME_FORMAT'] = preg_replace('/:s$/', '', Main\Type\DateTime::convertFormatToPhp(FORMAT_DATETIME));
}

CUtil::InitJSCore(array('ajax', 'tooltip'));

$arResult['GADGET'] = 'N';
if (isset($arParams['GADGET_ID']) && strlen($arParams['GADGET_ID']) > 0)
{
	$arResult['GADGET'] = 'Y';
	$arResult['GADGET_ID'] = $arParams['GADGET_ID'];
}
$isInGadgetMode = $arResult['GADGET'] === 'Y';

$arFilter = $arSort = array();
$bInternal = false;
$arResult['FORM_ID'] = isset($arParams['FORM_ID']) ? $arParams['FORM_ID'] : '';
$arResult['TAB_ID'] = isset($arParams['TAB_ID']) ? $arParams['TAB_ID'] : '';
if (!empty($arParams['INTERNAL_FILTER']) || $isInGadgetMode)
	$bInternal = true;
$arResult['INTERNAL'] = $bInternal;
if (!empty($arParams['INTERNAL_FILTER']) && is_array($arParams['INTERNAL_FILTER']))
{
	if(empty($arParams['GRID_ID_SUFFIX']))
	{
		$arParams['GRID_ID_SUFFIX'] = $this->GetParent() !== null ? strtoupper($this->GetParent()->GetName()) : '';
	}

	$arFilter = $arParams['INTERNAL_FILTER'];
}

if (!empty($arParams['INTERNAL_SORT']) && is_array($arParams['INTERNAL_SORT']))
{
	$arSort = $arParams['INTERNAL_SORT'];
}

$enableWidgetFilter = false;
$widgetFilter = null;
if (isset($arParams['WIDGET_DATA_FILTER']) && isset($arParams['WIDGET_DATA_FILTER']['WG']) && $arParams['WIDGET_DATA_FILTER']['WG'] === 'Y')
{
	$enableWidgetFilter = true;
	$widgetFilter = $arParams['WIDGET_DATA_FILTER'];
}
elseif (!$bInternal && isset($_REQUEST['WG']) && strtoupper($_REQUEST['WG']) === 'Y')
{
	$enableWidgetFilter = true;
	$widgetFilter = $_REQUEST;
}
if ($enableWidgetFilter)
{
	$dataSourceFilter = null;

	$dataSourceName = isset($widgetFilter['DS']) ? $widgetFilter['DS'] : '';
	if($dataSourceName !== '')
	{
		$dataSource = null;
		try
		{
			$dataSource = Bitrix\Crm\Widget\Data\DataSourceFactory::create(array('name' => $dataSourceName), $userID, true);
		}
		catch(Bitrix\Main\NotSupportedException $e)
		{
		}

		try
		{
			$dataSourceFilter = $dataSource ? $dataSource->prepareEntityListFilter($widgetFilter) : null;
		}
		catch(Bitrix\Main\ArgumentException $e)
		{
		}
		catch(Bitrix\Main\InvalidOperationException $e)
		{
		}
	}

	if(is_array($dataSourceFilter) && !empty($dataSourceFilter))
	{
		$arFilter = $dataSourceFilter;
	}
	else
	{
		$enableWidgetFilter = false;
	}
}

$enableCounterFilter = false;
if(!$bInternal && isset($_REQUEST['counter']))
{
	$counterTypeID = Bitrix\Crm\Counter\EntityCounterType::resolveID($_REQUEST['counter']);
	$counter = null;
	if(Bitrix\Crm\Counter\EntityCounterType::isDefined($counterTypeID))
	{
		try
		{
			$counter = Bitrix\Crm\Counter\EntityCounterFactory::create(
				CCrmOwnerType::Construction,
				$counterTypeID,
				$userID,
				Bitrix\Crm\Counter\EntityCounter::internalizeExtras($_REQUEST)
			);

			$arFilter = $counter->prepareEntityListFilter(
				array(
					'MASTER_ALIAS' => CCrmConstruction::TABLE_ALIAS,
					'MASTER_IDENTITY' => 'ID'
				)
			);
			$enableCounterFilter = !empty($arFilter);
		}
		catch(Bitrix\Main\NotSupportedException $e)
		{
		}
		catch(Bitrix\Main\ArgumentException $e)
		{
		}
	}
}

$arResult['IS_EXTERNAL_FILTER'] = ($enableWidgetFilter || $enableCounterFilter);

$CCrmUserType = new CCrmUserType($USER_FIELD_MANAGER, CCrmConstruction::$sUFEntityID);
$CCrmFieldMulti = new CCrmFieldMulti();

$arResult['GRID_ID'] = 'CRM_CONSTRUCTION_LIST_V12'.($bInternal && !empty($arParams['GRID_ID_SUFFIX']) ? '_'.$arParams['GRID_ID_SUFFIX'] : '');
$arResult['HONORIFIC'] = CCrmStatus::GetStatusListEx('HONORIFIC');
$arResult['TYPE_LIST'] = CCrmStatus::GetStatusListEx('CONSTRUCTION_TYPE');
$arResult['SOURCE_LIST'] = CCrmStatus::GetStatusListEx('SOURCE');
$arResult['WEBFORM_LIST'] = WebFormManager::getListNames();
$arResult['EXPORT_LIST'] = array('Y' => GetMessage('MAIN_YES'), 'N' => GetMessage('MAIN_NO'));
$arResult['FILTER'] = array();
$arResult['FILTER2LOGIC'] = array();
$arResult['FILTER_PRESETS'] = array();

$arResult['AJAX_MODE'] = isset($arParams['AJAX_MODE']) ? $arParams['AJAX_MODE'] : ($arResult['INTERNAL'] ? 'N' : 'Y');
$arResult['AJAX_ID'] = isset($arParams['AJAX_ID']) ? $arParams['AJAX_ID'] : '';
$arResult['AJAX_OPTION_JUMP'] = isset($arParams['AJAX_OPTION_JUMP']) ? $arParams['AJAX_OPTION_JUMP'] : 'N';
$arResult['AJAX_OPTION_HISTORY'] = isset($arParams['AJAX_OPTION_HISTORY']) ? $arParams['AJAX_OPTION_HISTORY'] : 'N';
$arResult['EXTERNAL_SALES'] = CCrmExternalSaleHelper::PrepareListItems();
$arResult['CALL_LIST_UPDATE_MODE'] = isset($_REQUEST['call_list_context']) && isset($_REQUEST['call_list_id']) && IsModuleInstalled('voximplant');
$arResult['CALL_LIST_CONTEXT'] = (string)$_REQUEST['call_list_context'];
$arResult['CALL_LIST_ID'] = (int)$_REQUEST['call_list_id'];
if($arResult['CALL_LIST_UPDATE_MODE'])
{
	AddEventHandler('crm', 'onCrmConstructionListItemBuildMenu', array('\Bitrix\Crm\CallList\CallList', 'handleOnCrmConstructionListItemBuildMenu'));
}

$addressLabels = EntityAddress::getShortLabels();
$requisite = new \Bitrix\Crm\EntityRequisite();

//region Filter Presets Initialization
if (!$bInternal)
{
	$currentUserID = $arResult['CURRENT_USER_ID'];
	$currentUserName = CCrmViewHelper::GetFormattedUserName($currentUserID, $arParams['NAME_TEMPLATE']);
	$arResult['FILTER_PRESETS'] = array(
		'filter_my' => array('name' => GetMessage('CRM_PRESET_MY'), 'fields' => array('ASSIGNED_BY_ID_name' => $currentUserName, 'ASSIGNED_BY_ID' => $currentUserID)),
//		'filter_change_my' => array('name' => GetMessage('CRM_PRESET_CHANGE_MY'), 'fields' => array('MODIFY_BY_ID_name' => $currentUserName, 'MODIFY_BY_ID' => $currentUserID))
	);
}
//endregion

$gridOptions = new \Bitrix\Main\Grid\Options($arResult['GRID_ID'], $arResult['FILTER_PRESETS']);
$filterOptions = new \Bitrix\Main\UI\Filter\Options($arResult['GRID_ID'], $arResult['FILTER_PRESETS']);

//region Navigation Params
if ($arParams['CONSTRUCTION_COUNT'] <= 0)
{
	$arParams['CONSTRUCTION_COUNT'] = 20;
}
$arNavParams = $gridOptions->GetNavParams(array('nPageSize' => $arParams['CONSTRUCTION_COUNT']));
$arNavParams['bShowAll'] = false;
//endregion

//region Filter initialization
if (!$bInternal)
{
	$arResult['FILTER2LOGIC'] = array('TITLE', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'POST', 'COMMENTS');

	$filterFlags = Crm\Filter\ConstructionSettings::FLAG_NONE;
	if($enableOutmodedFields)
	{
		$filterFlags |= Crm\Filter\ConstructionSettings::FLAG_ENABLE_ADDRESS;
	}

	$entityFilter = Crm\Filter\Factory::createEntityFilter(
		new Crm\Filter\ConstructionSettings(
			array('ID' => $arResult['GRID_ID'], 'flags' => $filterFlags)
		)
	);
	$effectiveFilterFieldIDs = $filterOptions->getUsedFields();
	if(empty($effectiveFilterFieldIDs))
	{
		$effectiveFilterFieldIDs = $entityFilter->getDefaultFieldIDs();
	}

	//region HACK: Preload fields for filter of user activities & webforms
	if(!in_array('ASSIGNED_BY_ID', $effectiveFilterFieldIDs, true))
	{
		$effectiveFilterFieldIDs[] = 'ASSIGNED_BY_ID';
	}

	if(!in_array('ACTIVITY_COUNTER', $effectiveFilterFieldIDs, true))
	{
		$effectiveFilterFieldIDs[] = 'ACTIVITY_COUNTER';
	}

	if(!in_array('WEBFORM_ID', $effectiveFilterFieldIDs, true))
	{
		$effectiveFilterFieldIDs[] = 'WEBFORM_ID';
	}

	Tracking\UI\Filter::appendEffectiveFields($effectiveFilterFieldIDs);
	//endregion
	foreach($effectiveFilterFieldIDs as $filterFieldID)
	{
		$filterField = $entityFilter->getField($filterFieldID);
		if($filterField)
		{
			$arResult['FILTER'][] = $filterField->toArray();
		}
	}
}
//endregion

//region Headers initialization
$arResult['HEADERS'] = array(
	array('id' => 'ID', 'name' => GetMessage('CRM_COLUMN_ID'), 'sort' => 'id', 'first_order' => 'desc', 'width' => 60, 'editable' => false, 'type' => 'int', 'class' => 'minimal'),
	array('id' => 'CONSTRUCTION_SUMMARY', 'name' => GetMessage('CRM_COLUMN_CONSTRUCTION'), 'sort' => 'full_name', 'width' => 200, 'default' => true, 'editable' => false),
);

// Don't display activities in INTERNAL mode.
if(!$bInternal)
{
	$arResult['HEADERS'][] = array(
		'id' => 'ACTIVITY_ID',
		'name' => GetMessage('CRM_COLUMN_ACTIVITY'),
		'sort' => 'nearest_activity',
		'default' => true,
		'prevent_default' => false
	);
}

$arResult['HEADERS'] = array_merge(
	$arResult['HEADERS'],
	array(
		array('id' => 'ASSIGNED_BY', 'name' => GetMessage('CRM_COLUMN_ASSIGNED_BY'), 'sort' => 'assigned_by', 'default' => true, 'editable' => false, 'class' => 'username')
	)
);

//$CCrmFieldMulti->PrepareListHeaders($arResult['HEADERS']);
if($isInExportMode)
{
    $arResult['HEADERS'] = array_merge(
        $arResult['HEADERS'],
        array(
		    array('id' => 'NAME', 'name' => GetMessage('CRM_COLUMN_NAME'), 'sort' => 'name', 'editable' => true, 'class' => 'username')
        )
    );
}


$arResult['HEADERS'] = array_merge(
	$arResult['HEADERS'],
	array(
		array('id' => 'CREATED_BY', 'name' => GetMessage('CRM_COLUMN_CREATED_BY'), 'sort' => 'created_by', 'editable' => false, 'class' => 'username'),
		array('id' => 'DATE_CREATE', 'name' => GetMessage('CRM_COLUMN_DATE_CREATE'), 'sort' => 'date_create', 'first_order' => 'desc', 'default' => true, 'class' => 'date'),
		array('id' => 'MODIFY_BY', 'name' => GetMessage('CRM_COLUMN_MODIFY_BY'), 'sort' => 'modify_by', 'editable' => false, 'class' => 'username'),
		array('id' => 'DATE_MODIFY', 'name' => GetMessage('CRM_COLUMN_DATE_MODIFY'), 'sort' => 'date_modify', 'first_order' => 'desc', 'class' => 'date'),
	)
);

$CCrmUserType->ListAddHeaders($arResult['HEADERS']);

$arBPData = array();
if ($isBizProcInstalled)
{
	$arBPData = CBPDocument::GetWorkflowTemplatesForDocumentType(array('crm', 'CCrmDocumentConstruction', 'CONSTRUCTION'));
	$arDocumentStates = CBPDocument::GetDocumentStates(
		array('crm', 'CCrmDocumentConstruction', 'CONSTRUCTION'),
		null
	);
	foreach ($arBPData as $arBP)
	{
		if (!CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::ViewWorkflow,
			$userID,
			array('crm', 'CCrmDocumentConstruction', 'CONSTRUCTION'),
			array(
				'UserGroups' => $CCrmBizProc->arCurrentUserGroups,
				'DocumentStates' => $arDocumentStates,
				'WorkflowTemplateId' => $arBP['ID'],
				'UserIsAdmin' => $isAdmin
			)
		))
		{
			continue;
		}
		$arResult['HEADERS'][] = array('id' => 'BIZPROC_'.$arBP['ID'], 'name' => $arBP['NAME'], 'sort' => false, 'editable' => false);
	}

	if ($arBPData)
	{
		CJSCore::Init('bp_starter');
	}
}

// list all filds for export
$exportAllFieldsList = array();
if ($isInExportMode && $isStExportAllFields)
{
	foreach ($arResult['HEADERS'] as $arHeader)
	{
		$exportAllFieldsList[$arHeader['id']] = true;
	}
}
unset($arHeader);

//endregion Headers initialization

//region Try to extract user action data
// We have to extract them before call of CGridOptions::GetFilter() overvise the custom filter will be corrupted.
$actionData = array(
	'METHOD' => $_SERVER['REQUEST_METHOD'],
	'ACTIVE' => false
);
$isStepRunningProcess = false;

if(check_bitrix_sessid())
{
	$postAction = 'action_button_'.$arResult['GRID_ID'];
	$getAction = 'action_'.$arResult['GRID_ID'];
	$actionToken = 'action_token_'.$arResult['GRID_ID'];

	// Adapt data from BX.CrmLongRunningProcessDialog request
	if (
		$arResult['IS_AJAX_CALL'] &&
		isset($_POST['PARAMS']['controls']) &&
		is_array($_POST['PARAMS']['controls']) &&
		isset($_POST['PARAMS']['controls'][$postAction])
	)
	{
		$isStepRunningProcess = true;
		$_POST = $_POST['PARAMS'];
	}

	//We need to check grid 'controls'
	$controls = isset($_POST['controls']) && is_array($_POST['controls']) ? $_POST['controls'] : array();
	if ($actionData['METHOD'] == 'POST' && (isset($controls[$postAction]) || isset($_POST[$postAction])))
	{
		CUtil::JSPostUnescape();

		$actionData['ACTIVE'] = true;

		if(isset($controls[$postAction]))
		{
			$actionData['NAME'] = $controls[$postAction];
		}
		else
		{
			$actionData['NAME'] = $_POST[$postAction];
			unset($_POST[$postAction], $_REQUEST[$postAction]);
		}

		if(isset($controls[$actionToken]))
		{
			$actionData['ACTION_TOKEN'] = $controls[$actionToken];
		}

		$allRows = 'action_all_rows_'.$arResult['GRID_ID'];
		$actionData['ALL_ROWS'] = false;
		if(isset($controls[$allRows]))
		{
			$actionData['ALL_ROWS'] = $controls[$allRows] == 'Y';
		}
		if(isset($_POST[$allRows]))
		{
			$actionData['ALL_ROWS'] = $_POST[$allRows] == 'Y';
			unset($_POST[$allRows], $_REQUEST[$allRows]);
		}

		if(isset($_POST['rows']) && is_array($_POST['rows']))
		{
			$actionData['ID'] = $_POST['rows'];
		}
		elseif(isset($_POST['ID']))
		{
			$actionData['ID'] = $_POST['ID'];
			unset($_POST['ID'], $_REQUEST['ID']);
		}

		if(isset($_POST['FIELDS']))
		{
			$actionData['FIELDS'] = $_POST['FIELDS'];
			unset($_POST['FIELDS'], $_REQUEST['FIELDS']);
		}

		if(isset($_POST['ACTION_ASSIGNED_BY_ID']) || isset($controls['ACTION_ASSIGNED_BY_ID']))
		{
			$assignedByID = 0;
			if(isset($_POST['ACTION_ASSIGNED_BY_ID']))
			{
				if(!is_array($_POST['ACTION_ASSIGNED_BY_ID']))
				{
					$assignedByID = intval($_POST['ACTION_ASSIGNED_BY_ID']);
				}
				elseif(count($_POST['ACTION_ASSIGNED_BY_ID']) > 0)
				{
					$assignedByID = intval($_POST['ACTION_ASSIGNED_BY_ID'][0]);
				}
				unset($_POST['ACTION_ASSIGNED_BY_ID'], $_REQUEST['ACTION_ASSIGNED_BY_ID']);
			}
			else
			{
				$assignedByID = (int)$controls['ACTION_ASSIGNED_BY_ID'];
			}

			$actionData['ASSIGNED_BY_ID'] = $assignedByID;
		}

		if(isset($_POST['ACTION_EXPORT']) || isset($controls['ACTION_EXPORT']))
		{
			if(isset($_POST['ACTION_EXPORT']))
			{
				$actionData['EXPORT'] = strtoupper($_POST['ACTION_EXPORT']) === 'Y' ? 'Y' : 'N';
				unset($_POST['ACTION_EXPORT'], $_REQUEST['ACTION_EXPORT']);
			}
			else
			{
				$actionData['EXPORT'] = strtoupper($controls['ACTION_EXPORT']) === 'Y' ? 'Y' : 'N';
			}
		}

		if(isset($_POST['ACTION_OPENED']) || isset($controls['ACTION_OPENED']))
		{
			if(isset($_POST['ACTION_OPENED']))
			{
				$actionData['OPENED'] = strtoupper($_POST['ACTION_OPENED']) === 'Y' ? 'Y' : 'N';
				unset($_POST['ACTION_OPENED'], $_REQUEST['ACTION_OPENED']);
			}
			else
			{
				$actionData['OPENED'] = strtoupper($controls['ACTION_OPENED']) === 'Y' ? 'Y' : 'N';
			}
		}

		$actionData['AJAX_CALL'] = $arResult['IS_AJAX_CALL'];
	}
	elseif ($actionData['METHOD'] == 'GET' && isset($_GET[$getAction]))
	{
		$actionData['ACTIVE'] = true;

		$actionData['NAME'] = $_GET[$getAction];
		unset($_GET[$getAction], $_REQUEST[$getAction]);

		if(isset($_GET['ID']))
		{
			$actionData['ID'] = $_GET['ID'];
			unset($_GET['ID'], $_REQUEST['ID']);
		}

		$actionData['AJAX_CALL'] = $arResult['IS_AJAX_CALL'];
	}
}
//endregion Try to extract user action data

//region Step running process params
if ($isStepRunningProcess)
{
	$stepRunningResultLimit = 500; // items per step
	$stepRunningTimeLimit = 25; // seconds per step
	$stepRunningStartTime = time();

	$maxExecutionTime = (int)ini_get('max_execution_time');
	if ($maxExecutionTime > 0 && $maxExecutionTime < $stepRunningTimeLimit)
	{
		$stepRunningTimeLimit = $maxExecutionTime - 5;
	}

	if (!isset($_SESSION[$arResult['GRID_ID']]))
	{
		$_SESSION[$arResult['GRID_ID']] = array(
			'PROCESSED_ITEMS' => 0,
			'TOTAL_ITEMS' => 0,
			'LAST_ID' => 0,
		);
	}
	$stepRunningParams = &$_SESSION['CRM_PROCESS_'.$arResult['GRID_ID']];

	if (!empty($actionData['ACTION_TOKEN']))
	{
		if (isset($stepRunningParams['ACTION_TOKEN']) && $stepRunningParams['ACTION_TOKEN'] !== $actionData['ACTION_TOKEN'])
		{
			// new process
			$stepRunningParams['PROCESSED_ITEMS'] = 0;
			$stepRunningParams['TOTAL_ITEMS'] = 0;
			$stepRunningParams['LAST_ID'] = 0;
		}
		$stepRunningParams['ACTION_TOKEN'] = $actionData['ACTION_TOKEN'];
	}
}
//endregion

// HACK: for clear filter by CREATED_BY_ID, MODIFY_BY_ID and ASSIGNED_BY_ID
if($_SERVER['REQUEST_METHOD'] === 'GET')
{
	if(isset($_REQUEST['CREATED_BY_ID_name']) && $_REQUEST['CREATED_BY_ID_name'] === '')
	{
		$_REQUEST['CREATED_BY_ID'] = $_GET['CREATED_BY_ID'] = array();
	}

	if(isset($_REQUEST['MODIFY_BY_ID_name']) && $_REQUEST['MODIFY_BY_ID_name'] === '')
	{
		$_REQUEST['MODIFY_BY_ID'] = $_GET['MODIFY_BY_ID'] = array();
	}

	if(isset($_REQUEST['ASSIGNED_BY_ID_name']) && $_REQUEST['ASSIGNED_BY_ID_name'] === '')
	{
		$_REQUEST['ASSIGNED_BY_ID'] = $_GET['ASSIGNED_BY_ID'] = array();
	}
}

if(!$arResult['IS_EXTERNAL_FILTER'])
{
	$arFilter += $filterOptions->getFilter($arResult['FILTER']);
}

$CCrmUserType->PrepareListFilterValues($arResult['FILTER'], $arFilter, $arResult['GRID_ID']);

$USER_FIELD_MANAGER->AdminListAddFilter(CCrmConstruction::$sUFEntityID, $arFilter);

// converts data from filter
Bitrix\Crm\Search\SearchEnvironment::convertEntityFilterValues(CCrmOwnerType::Construction, $arFilter);

//region Activity Counter Filter
if(isset($arFilter['ACTIVITY_COUNTER']))
{
	if(is_array($arFilter['ACTIVITY_COUNTER']))
	{
		$counterTypeID = Bitrix\Crm\Counter\EntityCounterType::joinType(
			array_filter($arFilter['ACTIVITY_COUNTER'], 'is_numeric')
		);
	}
	else
	{
		$counterTypeID = (int)$arFilter['ACTIVITY_COUNTER'];
	}

	$counter = null;
	if($counterTypeID > 0)
	{
		$counterUserIDs = array();
		if(isset($arFilter['ASSIGNED_BY_ID']))
		{
			if(is_array($arFilter['ASSIGNED_BY_ID']))
			{
				$counterUserIDs = array_filter($arFilter['ASSIGNED_BY_ID'], 'is_numeric');
			}
			elseif($arFilter['ASSIGNED_BY_ID'] > 0)
			{
				$counterUserIDs[] = $arFilter['ASSIGNED_BY_ID'];
			}
		}

		try
		{
			$counter = Bitrix\Crm\Counter\EntityCounterFactory::create(
				CCrmOwnerType::Construction,
				$counterTypeID,
				0,
				Bitrix\Crm\Counter\EntityCounter::internalizeExtras($_REQUEST)
			);

			$arFilter += $counter->prepareEntityListFilter(
				array(
					'MASTER_ALIAS' => CCrmConstruction::TABLE_ALIAS,
					'MASTER_IDENTITY' => 'ID',
					'USER_IDS' => $counterUserIDs
				)
			);
			unset($arFilter['ASSIGNED_BY_ID']);
		}
		catch(Bitrix\Main\NotSupportedException $e)
		{
		}
		catch(Bitrix\Main\ArgumentException $e)
		{
		}
	}
}
//endregion

CCrmEntityHelper::PrepareMultiFieldFilter($arFilter, array(), '=%', false);
$requisite->prepareEntityListFilter($arFilter);

$arImmutableFilters = array(
	'FM', 'ID', 'COMPANY_ID', 'COMPANY_ID_value', 'ASSOCIATED_COMPANY_ID', 'ASSOCIATED_DEAL_ID',
	'ASSIGNED_BY_ID', 'ASSIGNED_BY_ID_value',
	'CREATED_BY_ID', 'CREATED_BY_ID_value',
	'MODIFY_BY_ID', 'MODIFY_BY_ID_value',
	'TYPE_ID', 'SOURCE_ID', 'WEBFORM_ID', 'TRACKING_SOURCE_ID', 'TRACKING_CHANNEL_CODE',
	'HAS_PHONE', 'HAS_EMAIL', 'RQ',
	'SEARCH_CONTENT',
	'FILTER_ID', 'FILTER_APPLIED', 'PRESET_ID'
);

foreach ($arFilter as $k => $v)
{
	//Check if first key character is aplpha and key is not immutable
	if(preg_match('/^[a-zA-Z]/', $k) !== 1 || in_array($k, $arImmutableFilters, true))
	{
		continue;
	}

	$arMatch = array();
	if($k === 'ORIGINATOR_ID')
	{
		// HACK: build filter by internal entities
		$arFilter['=ORIGINATOR_ID'] = $v !== '__INTERNAL' ? $v : null;
		unset($arFilter[$k]);
	}
	elseif($k === 'ADDRESS'
		|| $k === 'ADDRESS_2'
		|| $k === 'ADDRESS_CITY'
		|| $k === 'ADDRESS_REGION'
		|| $k === 'ADDRESS_PROVINCE'
		|| $k === 'ADDRESS_POSTAL_CODE'
		|| $k === 'ADDRESS_COUNTRY')
	{
		$v = trim($v);
		if($v === '')
		{
			continue;
		}

		if(!isset($arFilter['ADDRESSES']))
		{
			$arFilter['ADDRESSES'] = array();
		}

		$addressTypeID = ConstructionAddress::resolveEntityFieldTypeID($k);
		if(!isset($arFilter['ADDRESSES'][$addressTypeID]))
		{
			$arFilter['ADDRESSES'][$addressTypeID] = array();
		}

		$n = ConstructionAddress::mapEntityField($k, $addressTypeID);
		$arFilter['ADDRESSES'][$addressTypeID][$n] = "{$v}%";
		unset($arFilter[$k]);
	}
	elseif (preg_match('/(.*)_from$/i'.BX_UTF_PCRE_MODIFIER, $k, $arMatch))
	{
		\Bitrix\Crm\UI\Filter\Range::prepareFrom($arFilter, $arMatch[1], $v);
	}
	elseif (preg_match('/(.*)_to$/i'.BX_UTF_PCRE_MODIFIER, $k, $arMatch))
	{
		if ($v != '' && ($arMatch[1] == 'DATE_CREATE' || $arMatch[1] == 'DATE_MODIFY') && !preg_match('/\d{1,2}:\d{1,2}(:\d{1,2})?$/'.BX_UTF_PCRE_MODIFIER, $v))
		{
			$v = CCrmDateTimeHelper::SetMaxDayTime($v);
		}
		\Bitrix\Crm\UI\Filter\Range::prepareTo($arFilter, $arMatch[1], $v);
	}
	elseif (in_array($k, $arResult['FILTER2LOGIC']))
	{
		// Bugfix #26956 - skip empty values in logical filter
		$v = trim($v);
		if($v !== '')
		{
			$arFilter['?'.$k] = $v;
		}
		unset($arFilter[$k]);
	}
	elseif($k === 'COMMUNICATION_TYPE')
	{
		if(!is_array($v))
		{
			$v = array($v);
		}
		foreach($v as $commTypeID)
		{
			if($commTypeID === CCrmFieldMulti::PHONE)
			{
				$arFilter['=HAS_PHONE'] = 'Y';
			}
			elseif($commTypeID === CCrmFieldMulti::EMAIL)
			{
				$arFilter['=HAS_EMAIL'] = 'Y';
			}
		}
		unset($arFilter['COMMUNICATION_TYPE']);
	}
	elseif ($k != 'ID' && $k != 'LOGIC' && $k != '__INNER_FILTER' && $k != '__JOINS' && $k != '__CONDITIONS' && strpos($k, 'UF_') !== 0 && preg_match('/^[^\=\%\?\>\<]{1}/', $k) === 1)
	{
		$arFilter['%'.$k] = $v;
		unset($arFilter[$k]);
	}
}

\Bitrix\Crm\UI\Filter\EntityHandler::internalize($arResult['FILTER'], $arFilter);

//region POST & GET actions processing
if($actionData['ACTIVE'])
{
	if ($actionData['METHOD'] == 'POST')
	{
		if($actionData['NAME'] == 'delete')
		{
			if ((isset($actionData['ID']) && is_array($actionData['ID'])) || $actionData['ALL_ROWS'])
			{
				$arFilterDel = array();
				if (!$actionData['ALL_ROWS'])
				{
					$arFilterDel = array('ID' => $actionData['ID']);
				}
				else
				{
					// Fix for issue #26628
					$arFilterDel += $arFilter;
				}

				$obRes = CCrmConstruction::GetListEx(array(), $arFilterDel, false, false, array('ID'));
				while($arConstruction = $obRes->Fetch())
				{
					$ID = $arConstruction['ID'];
					$arEntityAttr = $userPermissions->GetEntityAttr('CONSTRUCTION', array($ID));
					if (!$userPermissions->CheckEnityAccess('CONSTRUCTION', 'DELETE', $arEntityAttr[$ID]))
					{
						continue ;
					}

					$DB->StartTransaction();

					if ($CCrmBizProc->Delete($ID, $arEntityAttr)
						&& $CCrmConstruction->Delete($ID, array('PROCESS_BIZPROC' => false)))
					{
						$DB->Commit();
					}
					else
					{
						$DB->Rollback();
					}
				}
			}
		}
		elseif($actionData['NAME'] == 'edit')
		{
			if(isset($actionData['FIELDS']) && is_array($actionData['FIELDS']))
			{
				foreach($actionData['FIELDS'] as $ID => $arSrcData)
				{
					$arEntityAttr = $userPermissions->GetEntityAttr('CONSTRUCTION', array($ID));
					if (!$userPermissions->CheckEnityAccess('CONSTRUCTION', 'WRITE', $arEntityAttr[$ID]))
					{
						continue ;
					}

					$arUpdateData = array();
					reset($arResult['HEADERS']);
					foreach ($arResult['HEADERS'] as $arHead)
					{
						if (isset($arHead['editable']) && (is_array($arHead['editable']) || $arHead['editable'] === true) && isset($arSrcData[$arHead['id']]))
						{
							$arUpdateData[$arHead['id']] = $arSrcData[$arHead['id']];
						}
					}

					if (!empty($arUpdateData))
					{
						$DB->StartTransaction();
						if($CCrmConstruction->Update($ID, $arUpdateData, true, true, array('DISABLE_REQUIRED_USER_FIELD_CHECK' => true)))
						{
							$DB->Commit();

							$arErrors = array();
							CCrmBizProcHelper::AutoStartWorkflows(
								CCrmOwnerType::Construction,
								$ID,
								CCrmBizProcEventType::Edit,
								$arErrors
							);
						}
						else
						{
							$DB->Rollback();
						}
					}
				}
			}
		}
		elseif ($actionData['NAME'] == 'tasks')
		{
			if (isset($actionData['ID']) && is_array($actionData['ID']))
			{
				$arTaskID = array();
				foreach($actionData['ID'] as $ID)
				{
					$arTaskID[] = 'C_'.$ID;
				}

				$APPLICATION->RestartBuffer();

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
						'TITLE' => urlencode(GetMessage('CRM_TASK_TITLE_PREFIX')),
						'TAGS' => urlencode(GetMessage('CRM_TASK_TAG')),
						'back_url' => urlencode($arParams['PATH_TO_CONSTRUCTION_LIST'])
					)
				);
				if ($actionData['AJAX_CALL'])
				{
					echo '<script> parent.window.location = "'.CUtil::JSEscape($taskUrl).'";</script>';
					exit();
				}
				else
				{
					LocalRedirect($taskUrl);
				}
			}
		}
		elseif ($actionData['NAME'] == 'assign_to')
		{
			if(isset($actionData['ASSIGNED_BY_ID']))
			{
				$arIDs = array();
				if ($actionData['ALL_ROWS'])
				{
					$arActionFilter = $arFilter;
					$arActionFilter['CHECK_PERMISSIONS'] = 'N'; // Ignore 'WRITE' permission - we will check it before update.
					$dbRes = CCrmConstruction::GetListEx(array(), $arActionFilter, false, false, array('ID'));
					while($arConstruction = $dbRes->Fetch())
					{
						$arIDs[] = $arConstruction['ID'];
					}
				}
				elseif (isset($actionData['ID']) && is_array($actionData['ID']))
				{
					$arIDs = $actionData['ID'];
				}

				$arEntityAttr = $userPermissions->GetEntityAttr('CONSTRUCTION', $arIDs);


				foreach($arIDs as $ID)
				{
					if (!$userPermissions->CheckEnityAccess('CONSTRUCTION', 'WRITE', $arEntityAttr[$ID]))
					{
						continue;
					}

					$DB->StartTransaction();

					$arUpdateData = array(
						'ASSIGNED_BY_ID' => $actionData['ASSIGNED_BY_ID']
					);

					if($CCrmConstruction->Update($ID, $arUpdateData, true, true, array('DISABLE_USER_FIELD_CHECK' => true)))
					{
						$DB->Commit();

						$arErrors = array();
						CCrmBizProcHelper::AutoStartWorkflows(
							CCrmOwnerType::Construction,
							$ID,
							CCrmBizProcEventType::Edit,
							$arErrors
						);
					}
					else
					{
						$DB->Rollback();
					}
				}
			}
		}
		elseif ($actionData['NAME'] == 'export')
		{
			if(isset($actionData['EXPORT']))
			{
				$arIDs = array();
				if ($actionData['ALL_ROWS'])
				{
					$arActionFilter = $arFilter;
					$arActionFilter['CHECK_PERMISSIONS'] = 'N'; // Ignore 'WRITE' permission - we will check it before update.
					//$arActionFilter['!EXPORT'] = $actionData['EXPORT'];

					$arActionNavParams = false;
					$arActionOrder = array();
					if ($isStepRunningProcess)
					{
						$arActionOrder['ID'] = 'ASC';
						// set limitation
						if (isset($stepRunningParams['TOTAL_ITEMS']) && $stepRunningParams['TOTAL_ITEMS'] > 0)
						{
							$arActionNavParams = array('nTopCount' => $stepRunningResultLimit);
						}
						if (isset($stepRunningParams['LAST_ID']) && $stepRunningParams['LAST_ID'] > 0)
						{
							$arActionFilter['>ID'] = $stepRunningParams['LAST_ID'];
						}
					}

					$dbRes = \CCrmConstruction::GetListEx(
						$arActionOrder,
						$arActionFilter,
						false,
						$arActionNavParams,
						array('ID','EXPORT')
					);
					if ($isStepRunningProcess && empty($stepRunningParams['TOTAL_ITEMS']))
					{
						$stepRunningParams['TOTAL_ITEMS'] = $dbRes->SelectedRowsCount();
						$stepRunningParams['PROCESSED_ITEMS'] = 0;
						$stepRunningParams['LAST_ID'] = 0;
					}
					while($arConstruction = $dbRes->Fetch())
					{
						if ($arConstruction['ID'] == $actionData['EXPORT'])
						{
							continue;
						}
						$arIDs[] = $arConstruction['ID'];

						// item count limit
						if ($isStepRunningProcess && count($arIDs) >= $stepRunningResultLimit)
						{
							break;
						}
					}
				}
				elseif (isset($actionData['ID']) && is_array($actionData['ID']))
				{
					$arIDs = $actionData['ID'];
				}

				$arEntityAttr = $userPermissions->GetEntityAttr('CONSTRUCTION', $arIDs);


				foreach($arIDs as $ID)
				{
					if (!$userPermissions->CheckEnityAccess('CONSTRUCTION', 'WRITE', $arEntityAttr[$ID]))
					{
						continue;
					}

					$DB->StartTransaction();

					$arUpdateData = array(
						'EXPORT' => $actionData['EXPORT']
					);

					if($CCrmConstruction->Update($ID, $arUpdateData, true, true, array('DISABLE_USER_FIELD_CHECK' => true)))
					{
						$DB->Commit();

						$arErrors = array();
						CCrmBizProcHelper::AutoStartWorkflows(
							CCrmOwnerType::Construction,
							$ID,
							CCrmBizProcEventType::Edit,
							$arErrors
						);
					}
					else
					{
						$DB->Rollback();
					}

					if ($isStepRunningProcess)
					{
						$stepRunningParams['PROCESSED_ITEMS'] ++;
						$stepRunningParams['LAST_ID'] = $ID;

						// time limit per step
						if ((time() - $stepRunningStartTime) >= $stepRunningTimeLimit)
						{
							break;
						}
					}
				}

				if ($isStepRunningProcess)
				{
					$result = array(
						'STATUS' => ($stepRunningParams['PROCESSED_ITEMS'] >= $stepRunningParams['TOTAL_ITEMS'] ? 'COMPLETED' : 'PROGRESS'),
						'TOTAL_ITEMS' => $stepRunningParams['TOTAL_ITEMS'],
						'PROCESSED_ITEMS' => $stepRunningParams['PROCESSED_ITEMS'],
					);
					if ($stepRunningParams['PROCESSED_ITEMS'] >= $stepRunningParams['TOTAL_ITEMS'])
					{
						unset($stepRunningParams, $_SESSION['CRM_PROCESS_'.$arResult['GRID_ID']]);
					}

					$APPLICATION->RestartBuffer();
					Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
					echo \Bitrix\Main\Web\Json::encode($result);

					require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
					die();
				}
			}
		}
		elseif ($actionData['NAME'] == 'mark_as_opened')
		{
			if(isset($actionData['OPENED']) && $actionData['OPENED'] != '')
			{
				$isOpened = strtoupper($actionData['OPENED']) === 'Y' ? 'Y' : 'N';
				$arIDs = array();
				if ($actionData['ALL_ROWS'])
				{
					$arActionFilter = $arFilter;
					$arActionFilter['CHECK_PERMISSIONS'] = 'N'; // Ignore 'WRITE' permission - we will check it before update.

					$dbRes = CCrmConstruction::GetListEx(
						array(),
						$arActionFilter,
						false,
						false,
						array('ID', 'OPENED')
					);

					while($arConstruction = $dbRes->Fetch())
					{
						if(isset($arConstruction['OPENED']) && $arConstruction['OPENED'] === $isOpened)
						{
							continue;
						}

						$arIDs[] = $arConstruction['ID'];
					}
				}
				elseif (isset($actionData['ID']) && is_array($actionData['ID']))
				{
					$dbRes = CCrmConstruction::GetListEx(
						array(),
						array(
							'@ID'=> $actionData['ID'],
							'CHECK_PERMISSIONS' => 'N'
						),
						false,
						false,
						array('ID', 'OPENED')
					);

					while($arConstruction = $dbRes->Fetch())
					{
						if(isset($arConstruction['OPENED']) && $arConstruction['OPENED'] === $isOpened)
						{
							continue;
						}

						$arIDs[] = $arConstruction['ID'];
					}
				}

				$arEntityAttr = $userPermissions->GetEntityAttr('CONSTRUCTION', $arIDs);
				foreach($arIDs as $ID)
				{
					if (!$userPermissions->CheckEnityAccess('CONSTRUCTION', 'WRITE', $arEntityAttr[$ID]))
					{
						continue;
					}

					$DB->StartTransaction();
					$arUpdateData = array('OPENED' => $isOpened);
					if($CCrmConstruction->Update($ID, $arUpdateData, true, true, array('DISABLE_USER_FIELD_CHECK' => true)))
					{
						$DB->Commit();

						CCrmBizProcHelper::AutoStartWorkflows(
							CCrmOwnerType::Construction,
							$ID,
							CCrmBizProcEventType::Edit,
							$arErrors
						);
					}
					else
					{
						$DB->Rollback();
					}
				}
			}
		}
		if (!$actionData['AJAX_CALL'])
		{
			LocalRedirect($arParams['PATH_TO_CONSTRUCTION_LIST']);
		}
	}
	else//if ($actionData['METHOD'] == 'GET')
	{
		if ($actionData['NAME'] == 'delete' && isset($actionData['ID']))
		{
			$ID = intval($actionData['ID']);
			$arEntityAttr = $userPermissions->GetEntityAttr('CONSTRUCTION', array($ID));
			if(CCrmAuthorizationHelper::CheckDeletePermission(CCrmOwnerType::ConstructionName, $ID, $userPermissions, $arEntityAttr))
			{
				$DB->StartTransaction();

				if($CCrmBizProc->Delete($ID, $arEntityAttr)
					&& $CCrmConstruction->Delete($ID, array('PROCESS_BIZPROC' => false)))
				{
					$DB->Commit();
				}
				else
				{
					$DB->Rollback();
				}
			}
		}

		if (!$actionData['AJAX_CALL'])
		{
			LocalRedirect($bInternal ? '?'.$arParams['FORM_ID'].'_active_tab=tab_construction' : $arParams['PATH_TO_CONSTRUCTION_LIST']);
		}
	}
}
//endregion POST & GET actions processing

$_arSort = $gridOptions->GetSorting(array(
	'sort' => array('full_name' => 'asc'),
	'vars' => array('by' => 'by', 'order' => 'order')
));

$arResult['SORT'] = !empty($arSort) ? $arSort : $_arSort['sort'];
$arResult['SORT_VARS'] = $_arSort['vars'];

if ($isInExportMode)
{
	$arFilter['EXPORT'] = 'Y';
}

$arSelect = $gridOptions->GetVisibleColumns();

// Remove column for deleted RQ & UF
if ($requisite->normalizeEntityListFields($arSelect, $arResult['HEADERS'])
	|| $CCrmUserType->NormalizeFields($arSelect))
{
	$gridOptions->SetVisibleColumns($arSelect);
}

$rqSelect = $requisite->separateEntityListRqFields($arSelect);

$arSelectMap = array_fill_keys($arSelect, true);

$arResult['ENABLE_BIZPROC'] = $arResult['IS_BIZPROC_AVAILABLE'] = $isBizProcInstalled;
$arResult['ENABLE_TASK'] = IsModuleInstalled('tasks');

if($arResult['ENABLE_TASK'])
{
	$arResult['TASK_CREATE_URL'] = CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate(
			COption::GetOptionString('tasks', 'paths_task_user_edit', ''),
			array(
				'task_id' => 0,
				'user_id' => $userID
			)
		),
		array(
			'UF_CRM_TASK' => '#ENTITY_KEYS#',
			'TITLE' => urlencode(GetMessage('CRM_TASK_TITLE_PREFIX')),
			'TAGS' => urlencode(GetMessage('CRM_TASK_TAG')),
			'back_url' => urlencode($arParams['PATH_TO_CONSTRUCTION_LIST'])
		)
	);
}

// Export all fields
if ($isInExportMode && $isStExport && $isStExportAllFields)
{
	$arSelectMap = $exportAllFieldsList;
}

// Fill in default values if empty
if (empty($arSelectMap))
{
	foreach ($arResult['HEADERS'] as $arHeader)
	{
		if ($arHeader['default'])
		{
			$arSelectMap[$arHeader['id']] = true;
		}
	}

	//Disable bizproc fields processing
	$arResult['ENABLE_BIZPROC'] = false;
}
else
{
	if($arResult['ENABLE_BIZPROC'])
	{
		//Check if bizproc fields selected
		$hasBizprocFields = false;
		foreach($arSelectMap as $k => $v)
		{
			if(strncmp($k, 'BIZPROC_', 8) === 0)
			{
				$hasBizprocFields = true;
				break;
			}
		}
		$arResult['ENABLE_BIZPROC'] = $hasBizprocFields;
	}
	unset($fieldName);
}

// separate entity requisite fields
if ($isInExportMode && $isStExport && $isStExportRequisiteMultiline)
{
	$arSelectedHeaders = array_keys($arSelectMap);
}
else
{
	$arSelectedHeaders = array_merge(array_keys($arSelectMap), $rqSelect);
}

if ($isInGadgetMode)
{
	$arSelectMap['DATE_CREATE'] =
		$arSelectMap['HONORIFIC'] =
		$arSelectMap['NAME'] =
		$arSelectMap['SECOND_NAME'] =
		$arSelectMap['LAST_NAME'] =
		$arSelectMap['LOGIN'] =
		$arSelectMap['TYPE_ID'] = true;
}
else
{
	if(isset($arSelectMap['CONSTRUCTION_SUMMARY']))
	{
		$arSelectMap['PHOTO'] =
		$arSelectMap['HONORIFIC'] =
		$arSelectMap['NAME'] =
		$arSelectMap['LAST_NAME'] =
		$arSelectMap['SECOND_NAME'] =
		$arSelectMap['TYPE_ID'] = true;
	}

	if($arSelectMap['ASSIGNED_BY'])
	{
		$arSelectMap['ASSIGNED_BY_LOGIN'] =
			$arSelectMap['ASSIGNED_BY_NAME'] =
			$arSelectMap['ASSIGNED_BY_LAST_NAME'] =
			$arSelectMap['ASSIGNED_BY_SECOND_NAME'] = true;
	}

	if(isset($arSelectMap['COMPANY_ID']))
	{
		$arSelectMap['COMPANY_TITLE'] =
		$arSelectMap['POST'] = true;
	}
	else
	{
		// Required for construction of URLs
		$arSelectMap['COMPANY_ID'] = true;
	}

	if(isset($arSelectMap['CONSTRUCTION_COMPANY']))
	{
		$arSelectMap['COMPANY_TITLE'] =
			$arSelectMap['POST'] = true;
	}

	if(isset($arSelectMap['ACTIVITY_ID']))
	{
		$arSelectMap['ACTIVITY_TIME'] =
			$arSelectMap['ACTIVITY_SUBJECT'] =
			$arSelectMap['C_ACTIVITY_ID'] =
			$arSelectMap['C_ACTIVITY_TIME'] =
			$arSelectMap['C_ACTIVITY_SUBJECT'] =
			$arSelectMap['C_ACTIVITY_RESP_ID'] =
			$arSelectMap['C_ACTIVITY_RESP_LOGIN'] =
			$arSelectMap['C_ACTIVITY_RESP_NAME'] =
			$arSelectMap['C_ACTIVITY_RESP_LAST_NAME'] =
			$arSelectMap['C_ACTIVITY_RESP_SECOND_NAME'] = true;
	}

	if(isset($arSelectMap['CREATED_BY']))
	{
		$arSelectMap['CREATED_BY_LOGIN'] =
			$arSelectMap['CREATED_BY_NAME'] =
			$arSelectMap['CREATED_BY_LAST_NAME'] =
			$arSelectMap['CREATED_BY_SECOND_NAME'] = true;
	}

	if(isset($arSelectMap['MODIFY_BY']))
	{
		$arSelectMap['MODIFY_BY_LOGIN'] =
			$arSelectMap['MODIFY_BY_NAME'] =
			$arSelectMap['MODIFY_BY_LAST_NAME'] =
			$arSelectMap['MODIFY_BY_SECOND_NAME'] = true;
	}

	if(isset($arSelectMap['FULL_ADDRESS']))
	{
		$arSelectMap['ADDRESS'] =
			$arSelectMap['ADDRESS_2'] =
			$arSelectMap['ADDRESS_CITY'] =
			$arSelectMap['ADDRESS_POSTAL_CODE'] =
			$arSelectMap['ADDRESS_POSTAL_CODE'] =
			$arSelectMap['ADDRESS_REGION'] =
			$arSelectMap['ADDRESS_PROVINCE'] =
			$arSelectMap['ADDRESS_COUNTRY'] = true;
	}

	// ID must present in select
	if(!isset($arSelectMap['ID']))
	{
		$arSelectMap['ID'] = true;
	}
}

if ($isInExportMode)
{
	CCrmComponentHelper::PrepareExportFieldsList(
		$arSelectedHeaders,
		array(
			'CONSTRUCTION_SUMMARY' => array(
				'HONORIFIC',
				'NAME',
				'SECOND_NAME',
				'LAST_NAME',
				'PHOTO',
				'TYPE_ID'
			),
			'CONSTRUCTION_COMPANY' => array(
				'COMPANY_ID',
				'POST'
			),
			'ACTIVITY_ID' => array()
		)
	);

	if(!in_array('ID', $arSelectedHeaders))
	{
		$arSelectedHeaders[] = 'ID';
	}

	$arResult['SELECTED_HEADERS'] = $arSelectedHeaders;
}

$nTopCount = false;
if ($isInGadgetMode)
{
	$nTopCount = $arParams['CONSTRUCTION_COUNT'];
}

if($nTopCount > 0 && !isset($arFilter['ID']))
{
	$arNavParams['nTopCount'] = $nTopCount;
}

if ($isInExportMode)
{
	$arFilter['PERMISSION'] = 'EXPORT';
}

// HACK: Make custom sort for ASSIGNED_BY and FULL_NAME field
$arSort = $arResult['SORT'];
if(isset($arSort['assigned_by']))
{
    $arSort['assigned_by_last_name'] = $arSort['assigned_by'];
    $arSort['assigned_by_name'] = $arSort['assigned_by'];
	unset($arSort['assigned_by']);
}
if(isset($arSort['full_name']))
{
	$arSort['last_name'] = $arSort['full_name'];
	$arSort['name'] = $arSort['full_name'];
	unset($arSort['full_name']);
}

if($arSort['date_create'])
{
	$arSort['id'] = $arSort['date_create'];
	unset($arSort['date_create']);
}

if(!empty($arSort) && !isset($arSort['id']))
{
	$arSort['id'] = reset($arSort);
}

$arOptions = array('FIELD_OPTIONS' => array('ADDITIONAL_FIELDS' => array()));
if(isset($arSelectMap['ACTIVITY_ID']))
{
	$arOptions['FIELD_OPTIONS']['ADDITIONAL_FIELDS'][] = 'ACTIVITY';
}

if(isset($arParams['IS_EXTERNAL_CONTEXT']))
{
	$arOptions['IS_EXTERNAL_CONTEXT'] = $arParams['IS_EXTERNAL_CONTEXT'];
}

$arSelect = array_unique(array_keys($arSelectMap), SORT_STRING);

$arResult['CONSTRUCTION'] = array();
$arResult['CONSTRUCTION_ID'] = array();
$arResult['CONSTRUCTION_UF'] = array();

//region Navigation data initialization
$pageNum = 0;
if ($isInExportMode && $isStExport)
{
	$pageSize = !empty($arParams['STEXPORT_PAGE_SIZE']) ? $arParams['STEXPORT_PAGE_SIZE'] : $arParams['CONSTRUCTION_COUNT'];
}
else
{
	$pageSize = !$isInExportMode
		? (int)(isset($arNavParams['nPageSize']) ? $arNavParams['nPageSize'] : $arParams['CONSTRUCTION_COUNT']) : 0;
}

$enableNextPage = false;
if(isset($_REQUEST['apply_filter']) && $_REQUEST['apply_filter'] === 'Y')
{
	$pageNum = 1;
}
elseif($pageSize > 0 && (isset($arParams['PAGE_NUMBER']) || isset($_REQUEST['page'])))
{
	$pageNum = (int)(isset($arParams['PAGE_NUMBER']) ? $arParams['PAGE_NUMBER'] : $_REQUEST['page']);
	if($pageNum < 0)
	{
		//Backward mode
		$offset = -($pageNum + 1);
		$total = CCrmConstruction::GetListEx(array(), $arFilter, array());
		$pageNum = (int)(ceil($total / $pageSize)) - $offset;
		if($pageNum <= 0)
		{
			$pageNum = 1;
		}
	}
}

if (!($isInExportMode && $isStExport))
{
	if ($pageNum > 0)
	{
		if (!isset($_SESSION['CRM_PAGINATION_DATA']))
		{
			$_SESSION['CRM_PAGINATION_DATA'] = array();
		}
		$_SESSION['CRM_PAGINATION_DATA'][$arResult['GRID_ID']] = array('PAGE_NUM' => $pageNum, 'PAGE_SIZE' => $pageSize);
	}
	else
	{
		if (!$bInternal
			&& !(isset($_REQUEST['clear_nav']) && $_REQUEST['clear_nav'] === 'Y')
			&& isset($_SESSION['CRM_PAGINATION_DATA'])
			&& isset($_SESSION['CRM_PAGINATION_DATA'][$arResult['GRID_ID']])
		)
		{
			$paginationData = $_SESSION['CRM_PAGINATION_DATA'][$arResult['GRID_ID']];
			if (isset($paginationData['PAGE_NUM'])
				&& isset($paginationData['PAGE_SIZE'])
				&& $paginationData['PAGE_SIZE'] == $pageSize
			)
			{
				$pageNum = (int)$paginationData['PAGE_NUM'];
			}
		}

		if ($pageNum <= 0)
		{
			$pageNum = 1;
		}
	}
}
//endregion

if ($isInExportMode && $isStExport && $pageNum === 1)
{
	$total = CCrmConstruction::GetListEx(array(), $arFilter, array());
	if (is_numeric($total))
	{
		$arResult['STEXPORT_TOTAL_ITEMS'] = (int)$total;
	}
}

$limit = $pageSize + 1;
if ($isInExportMode && $isStExport)
{
	$total = (int)$arResult['STEXPORT_TOTAL_ITEMS'];
	$processed = ($pageNum - 1) * $pageSize;
	if ($total - $processed <= $pageSize)
		$limit = $total - $processed;
	unset($total, $processed);
}

if(isset($arSort['nearest_activity']))
{
	$navListOptions = ($isInExportMode && !$isStExport)
		? array()
		: array_merge(
			$arOptions,
			array('QUERY_OPTIONS' => array('LIMIT' => $limit, 'OFFSET' => $pageSize * ($pageNum - 1)))
		);

	$navDbResult = CCrmActivity::GetEntityList(
		CCrmOwnerType::Construction,
		$userID,
		$arSort['nearest_activity'],
		$arFilter,
		false,
		$navListOptions
	);

	$qty = 0;
	while($arConstruction = $navDbResult->Fetch())
	{
		if($pageSize > 0 && ++$qty > $pageSize)
		{
			$enableNextPage = true;
			break;
		}

		$arResult['CONSTRUCTION'][$arConstruction['ID']] = $arConstruction;
		$arResult['CONSTRUCTION_ID'][$arConstruction['ID']] = $arConstruction['ID'];
		$arResult['CONSTRUCTION_UF'][$arConstruction['ID']] = array();
	}

	//region Navigation data storing
	$arResult['PAGINATION'] = array('PAGE_NUM' => $pageNum, 'ENABLE_NEXT_PAGE' => $enableNextPage);

	$arResult['DB_FILTER'] = $arFilter;

	if(!isset($_SESSION['CRM_GRID_DATA']))
	{
		$_SESSION['CRM_GRID_DATA'] = array();
	}
	$_SESSION['CRM_GRID_DATA'][$arResult['GRID_ID']] = array('FILTER' => $arFilter);
	//endregion

	$entityIDs = array_keys($arResult['CONSTRUCTION']);
	if(!empty($entityIDs))
	{
		if ($isInExportMode && $isStExport)
		{
			if (!is_array($arSort))
			{
				$arSort = array();
			}

			if (!isset($arSort['ID']))
			{
				$order = strtoupper($arSort['nearest_activity']);
				if ($order === 'ASC' || $order === 'DESC')
				{
					$arSort['ID'] = $arSort['nearest_activity'];
				}
				else
				{
					$arSort['ID'] = 'asc';
				}
				unset($order);
			}
		}
		//Permissions are already checked.
		$dbResult = CCrmConstruction::GetListEx(
			$arSort,
			array('@ID' => $entityIDs, 'CHECK_PERMISSIONS' => 'N'),
			false,
			false,
			$arSelect,
			$arOptions
		);
		while($arConstruction = $dbResult->GetNext())
		{
			$arResult['CONSTRUCTION'][$arConstruction['ID']] = $arConstruction;
		}
	}
}
else
{
	$addressSort = array();
	foreach($arSort as $k => $v)
	{
		if(strncmp($k, 'address', 7) === 0)
		{
			$addressSort[strtoupper($k)] = $v;
		}
	}

	if(!empty($addressSort))
	{
		$navListOptions = ($isInExportMode && !$isStExport)
			? array()
			: array_merge(
				$arOptions,
				array('QUERY_OPTIONS' => array('LIMIT' => $limit, 'OFFSET' => $pageSize * ($pageNum - 1)))
			);

		$navDbResult = \Bitrix\Crm\ConstructionAddress::getEntityList(
			\Bitrix\Crm\EntityAddress::Primary,
			$addressSort,
			$arFilter,
			false,
			$navListOptions
		);

		$qty = 0;
		while($arConstruction = $navDbResult->Fetch())
		{
			if($pageSize > 0 && ++$qty > $pageSize)
			{
				$enableNextPage = true;
				break;
			}

			$arResult['CONSTRUCTION'][$arConstruction['ID']] = $arConstruction;
			$arResult['CONSTRUCTION_ID'][$arConstruction['ID']] = $arConstruction['ID'];
			$arResult['CONSTRUCTION_UF'][$arConstruction['ID']] = array();
		}

		//region Navigation data storing
		$arResult['PAGINATION'] = array('PAGE_NUM' => $pageNum, 'ENABLE_NEXT_PAGE' => $enableNextPage);
		$arResult['DB_FILTER'] = $arFilter;
		if(!isset($_SESSION['CRM_GRID_DATA']))
		{
			$_SESSION['CRM_GRID_DATA'] = array();
		}
		$_SESSION['CRM_GRID_DATA'][$arResult['GRID_ID']] = array('FILTER' => $arFilter);
		//endregion

		$entityIDs = array_keys($arResult['CONSTRUCTION']);
		if(!empty($entityIDs))
		{
			$arSort['ID'] = array_shift(array_slice($addressSort, 0, 1));
			//Permissions are already checked.
			$dbResult = CCrmConstruction::GetListEx(
				$arSort,
				array('@ID' => $entityIDs, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				$arSelect,
				$arOptions
			);
			while($arConstruction = $dbResult->GetNext())
			{
				$arResult['CONSTRUCTION'][$arConstruction['ID']] = $arConstruction;
			}
		}
	}
	else
	{
		if ($isInGadgetMode && isset($arNavParams['nTopCount']))
		{
			$navListOptions = array_merge($arOptions, array('QUERY_OPTIONS' => array('LIMIT' => $arNavParams['nTopCount'])));
		}
		else
		{
			$navListOptions = ($isInExportMode && !$isStExport)
				? array()
				: array_merge(
					$arOptions,
					array('QUERY_OPTIONS' => array('LIMIT' => $limit, 'OFFSET' => $pageSize * ($pageNum - 1)))
				);
		}

		if ($isInExportMode && $isStExport)
		{
			if (!is_array($arSort))
			{
				$arSort = array();
			}

			if (!isset($arSort['ID']))
			{
				if (!empty($arSort))
				{
					$arSort['ID'] = array_shift(array_slice($arSort, 0, 1));
				}
				else
				{
					$arSort['ID'] = 'asc';
				}
			}
		}

		$dbResult = CCrmConstruction::GetListEx(
			$arSort,
			$arFilter,
			false,
			false,
			$arSelect,
			$navListOptions
		);

		$qty = 0;
		while($arConstruction = $dbResult->GetNext())
		{
			if($pageSize > 0 && ++$qty > $pageSize)
			{
				$enableNextPage = true;
				break;
			}

			$arResult['CONSTRUCTION'][$arConstruction['ID']] = $arConstruction;
			$arResult['CONSTRUCTION_ID'][$arConstruction['ID']] = $arConstruction['ID'];
			$arResult['CONSTRUCTION_UF'][$arConstruction['ID']] = array();
		}

		//region Navigation data storing
		$arResult['PAGINATION'] = array('PAGE_NUM' => $pageNum, 'ENABLE_NEXT_PAGE' => $enableNextPage);

		$arResult['DB_FILTER'] = $arFilter;

		if(!isset($_SESSION['CRM_GRID_DATA']))
		{
			$_SESSION['CRM_GRID_DATA'] = array();
		}
		$_SESSION['CRM_GRID_DATA'][$arResult['GRID_ID']] = array('FILTER' => $arFilter);
		//endregion
	}
}

$arResult['STEXPORT_IS_FIRST_PAGE'] = $pageNum === 1 ? 'Y' : 'N';
$arResult['STEXPORT_IS_LAST_PAGE'] = $enableNextPage ? 'N' : 'Y';

$arResult['PAGINATION']['URL'] = $APPLICATION->GetCurPageParam('', array('apply_filter', 'clear_filter', 'save', 'page', 'sessid', 'internal'));
$arResult['PERMS']['ADD']    = !$userPermissions->HavePerm('CONSTRUCTION', BX_CRM_PERM_NONE, 'ADD');
$arResult['PERMS']['WRITE']  = !$userPermissions->HavePerm('CONSTRUCTION', BX_CRM_PERM_NONE, 'WRITE');
$arResult['PERMS']['DELETE'] = !$userPermissions->HavePerm('CONSTRUCTION', BX_CRM_PERM_NONE, 'DELETE');

$arResult['PERM_DEAL'] = CCrmDeal::CheckCreatePermission($userPermissions);
$bQuote = !$CCrmConstruction->cPerms->HavePerm('QUOTE', BX_CRM_PERM_NONE, 'ADD');
$arResult['PERM_QUOTE'] = $bQuote;
$bInvoice = !$userPermissions->HavePerm('INVOICE', BX_CRM_PERM_NONE, 'ADD');
$arResult['PERM_INVOICE'] = $bInvoice;

$enableExportEvent = $isInExportMode && HistorySettings::getCurrent()->isExportEventEnabled();

$addressFormatOptions = $sExportType === 'csv'
	? array('SEPARATOR' => AddressSeparator::Comma)
	: array('SEPARATOR' => AddressSeparator::HtmlLineBreak, 'NL2BR' => true);

$now = time() + CTimeZone::GetOffset();
foreach($arResult['CONSTRUCTION'] as &$arConstruction)
{
	$entityID = $arConstruction['ID'];
	if($enableExportEvent)
	{
		CCrmEvent::RegisterExportEvent(CCrmOwnerType::Construction, $entityID, $userID);
	}

	if (!empty($arConstruction['PHOTO']))
	{
		if ($isInExportMode)
		{
			if ($arFile = CFile::GetFileArray($arConstruction['PHOTO']))
				$arConstruction['PHOTO'] = CHTTP::URN2URI($arFile["SRC"]);
		}
		else
		{
			$arFileTmp = CFile::ResizeImageGet(
				$arConstruction['PHOTO'],
				array('width' => 100, 'height' => 100),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				false
			);
			$arConstruction['PHOTO'] = CFile::ShowImage($arFileTmp['src'], 50, 50, 'border=0');
		}
	}

	$companyID = isset($arConstruction['~COMPANY_ID']) ? (int)$arConstruction['~COMPANY_ID'] : 0;
	$arConstruction['PATH_TO_COMPANY_SHOW'] = $companyID > 0
		? CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_COMPANY_SHOW'], array('company_id' => $companyID))
		: '';

	if($companyID > 0)
	{
		$arConstruction['COMPANY_INFO'] = array(
			'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
			'ENTITY_ID' => $companyID
		);

		if(!CCrmCompany::CheckReadPermission($companyID, $userPermissions))
		{
			$arConstruction['COMPANY_INFO']['IS_HIDDEN'] = true;
		}
		else
		{
			$arConstruction['COMPANY_INFO'] =
				array_merge(
					$arConstruction['COMPANY_INFO'],
					array(
						'TITLE' => isset($arConstruction['~COMPANY_TITLE']) ? $arConstruction['~COMPANY_TITLE'] : ('['.$companyID.']'),
						'PREFIX' => "CONSTRUCTION_{$arConstruction['~ID']}",
						'DESCRIPTION' => isset($arConstruction['~POST']) ? $arConstruction['~POST'] : ''
					)
				);
		}
	}


	$arConstruction['PATH_TO_CONSTRUCTION_DETAILS'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_CONSTRUCTION_DETAILS'],
		array('construction_id' => $entityID)
	);

	if($arResult['ENABLE_SLIDER'])
	{
		$arConstruction['PATH_TO_CONSTRUCTION_SHOW'] = $arConstruction['PATH_TO_CONSTRUCTION_DETAILS'];
		$arConstruction['PATH_TO_CONSTRUCTION_EDIT'] = CCrmUrlUtil::AddUrlParams(
			$arConstruction['PATH_TO_CONSTRUCTION_DETAILS'],
			array('init_mode' => 'edit')
		);
	}
	else
	{
		$arConstruction['PATH_TO_CONSTRUCTION_SHOW'] = CComponentEngine::MakePathFromTemplate(
			$arParams['PATH_TO_CONSTRUCTION_SHOW'],
			array('construction_id' => $entityID)
		);

		$arConstruction['PATH_TO_CONSTRUCTION_EDIT'] = CComponentEngine::MakePathFromTemplate(
			$arParams['PATH_TO_CONSTRUCTION_EDIT'],
			array('construction_id' => $entityID)
		);
	}

	if ($arResult['PERM_DEAL'])
	{
		$arConstruction['PATH_TO_DEAL_EDIT'] = CHTTP::urlAddParams(
			CComponentEngine::MakePathFromTemplate(
				$arResult['ENABLE_SLIDER'] ? $arParams['PATH_TO_DEAL_DETAILS'] : $arParams['PATH_TO_DEAL_EDIT'],
				array('deal_id' => 0)
			),
			array('construction_id' => $entityID, 'company_id' => $arConstruction['COMPANY_ID'])
		);
	}

	$arConstruction['PATH_TO_CONSTRUCTION_COPY'] =  CHTTP::urlAddParams(
		$arConstruction['PATH_TO_CONSTRUCTION_EDIT'],
		array('copy' => 1)
	);

	$arConstruction['PATH_TO_CONSTRUCTION_DELETE'] =  CHTTP::urlAddParams(
		$bInternal ? $APPLICATION->GetCurPage() : $arParams['PATH_TO_CONSTRUCTION_LIST'],
		array(
			'action_'.$arResult['GRID_ID'] => 'delete',
			'ID' => $entityID,
			'sessid' => $arResult['SESSION_ID']
		)
	);
	$arConstruction['PATH_TO_USER_PROFILE'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_USER_PROFILE'],
		array('user_id' => $arConstruction['ASSIGNED_BY'])
	);
	$arConstruction['~CONSTRUCTION_FORMATTED_NAME'] = CCrmConstruction::PrepareFormattedName(
		array(
			'HONORIFIC' => isset($arConstruction['~HONORIFIC']) ? $arConstruction['~HONORIFIC'] : '',
			'NAME' => isset($arConstruction['~NAME']) ? $arConstruction['~NAME'] : '',
			'LAST_NAME' => isset($arConstruction['~LAST_NAME']) ? $arConstruction['~LAST_NAME'] : '',
			'SECOND_NAME' => isset($arConstruction['~SECOND_NAME']) ? $arConstruction['~SECOND_NAME'] : ''
		)
	);
	$arConstruction['CONSTRUCTION_FORMATTED_NAME'] = htmlspecialcharsbx($arConstruction['~CONSTRUCTION_FORMATTED_NAME']);

	$typeID = isset($arConstruction['TYPE_ID']) ? $arConstruction['TYPE_ID'] : '';
	$arConstruction['CONSTRUCTION_TYPE_NAME'] = isset($arResult['TYPE_LIST'][$typeID]) ? $arResult['TYPE_LIST'][$typeID] : $typeID;

	$arConstruction['PATH_TO_USER_CREATOR'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_USER_PROFILE'],
		array('user_id' => $arConstruction['CREATED_BY'])
	);

	$arConstruction['PATH_TO_USER_MODIFIER'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_USER_PROFILE'],
		array('user_id' => $arConstruction['MODIFY_BY'])
	);

	$arConstruction['CREATED_BY_FORMATTED_NAME'] = CUser::FormatName(
		$arParams['NAME_TEMPLATE'],
		array(
			'LOGIN' => $arConstruction['CREATED_BY_LOGIN'],
			'NAME' => $arConstruction['CREATED_BY_NAME'],
			'LAST_NAME' => $arConstruction['CREATED_BY_LAST_NAME'],
			'SECOND_NAME' => $arConstruction['CREATED_BY_SECOND_NAME']
		),
		true, false
	);

	$arConstruction['MODIFY_BY_FORMATTED_NAME'] = CUser::FormatName(
		$arParams['NAME_TEMPLATE'],
		array(
			'LOGIN' => $arConstruction['MODIFY_BY_LOGIN'],
			'NAME' => $arConstruction['MODIFY_BY_NAME'],
			'LAST_NAME' => $arConstruction['MODIFY_BY_LAST_NAME'],
			'SECOND_NAME' => $arConstruction['MODIFY_BY_SECOND_NAME']
		),
		true, false
	);

	if(isset($arConstruction['~ACTIVITY_TIME']))
	{
		$time = MakeTimeStamp($arConstruction['~ACTIVITY_TIME']);
		$arConstruction['~ACTIVITY_EXPIRED'] = $time <= $now;
		$arConstruction['~ACTIVITY_IS_CURRENT_DAY'] = $arConstruction['~ACTIVITY_EXPIRED'] || CCrmActivity::IsCurrentDay($time);
	}

	if ($arResult['ENABLE_TASK'])
	{
		$arConstruction['PATH_TO_TASK_EDIT'] = CHTTP::urlAddParams(
			CComponentEngine::MakePathFromTemplate(
				COption::GetOptionString('tasks', 'paths_task_user_edit', ''),
				array(
					'task_id' => 0,
					'user_id' => $userID
				)
			),
			array(
				'UF_CRM_TASK' => "C_{$entityID}",
				'TITLE' => urlencode(GetMessage('CRM_TASK_TITLE_PREFIX').' '),
				'TAGS' => urlencode(GetMessage('CRM_TASK_TAG')),
				'back_url' => urlencode($arParams['PATH_TO_CONSTRUCTION_LIST'])
			)
		);
	}

	if (IsModuleInstalled('sale'))
	{
		$arConstruction['PATH_TO_QUOTE_ADD'] =
			CHTTP::urlAddParams(
				CComponentEngine::makePathFromTemplate(
					$arParams['PATH_TO_QUOTE_EDIT'],
					array('quote_id' => 0)
				),
				array('construction_id' => $entityID)
			);
		$arConstruction['PATH_TO_INVOICE_ADD'] =
			CHTTP::urlAddParams(
				CComponentEngine::makePathFromTemplate(
					$arParams['PATH_TO_INVOICE_EDIT'],
					array('invoice_id' => 0)
				),
				array('construction' => $entityID)
			);
	}

	if ($arResult['ENABLE_BIZPROC'])
	{
		$arConstruction['BIZPROC_STATUS'] = '';
		$arConstruction['BIZPROC_STATUS_HINT'] = '';

		$arDocumentStates = CBPDocument::GetDocumentStates(
			array('crm', 'CCrmDocumentConstruction', 'CONSTRUCTION'),
			array('crm', 'CCrmDocumentConstruction', "CONSTRUCTION_{$entityID}")
		);

		$arConstruction['PATH_TO_BIZPROC_LIST'] =  CHTTP::urlAddParams(
			CComponentEngine::MakePathFromTemplate(
				$arParams['PATH_TO_CONSTRUCTION_SHOW'],
				array('construction_id' => $entityID)
			),
			array('CRM_CONSTRUCTION_SHOW_V12_active_tab' => 'tab_bizproc')
		);

		$totalTaskQty = 0;
		$docStatesQty = count($arDocumentStates);
		if($docStatesQty === 1)
		{
			$arDocState = $arDocumentStates[array_shift(array_keys($arDocumentStates))];

			$docTemplateID = $arDocState['TEMPLATE_ID'];
			$paramName = "BIZPROC_{$docTemplateID}";
			$docTtl = isset($arDocState['STATE_TITLE']) ? $arDocState['STATE_TITLE'] : '';
			$docName = isset($arDocState['STATE_NAME']) ? $arDocState['STATE_NAME'] : '';
			$docTemplateName = isset($arDocState['TEMPLATE_NAME']) ? $arDocState['TEMPLATE_NAME'] : '';

			if($isInExportMode)
			{
				$arConstruction[$paramName] = $docTtl;
			}
			else
			{
				$arConstruction[$paramName] = '<a href="'.htmlspecialcharsbx($arConstruction['PATH_TO_BIZPROC_LIST']).'">'.htmlspecialcharsbx($docTtl).'</a>';

				$docID = $arDocState['ID'];
				$taskQty = CCrmBizProcHelper::GetUserWorkflowTaskCount(array($docID), $userID);
				if($taskQty > 0)
				{
					$totalTaskQty += $taskQty;
				}

				$arConstruction['BIZPROC_STATUS'] = $taskQty > 0 ? 'attention' : 'inprogress';
				$arConstruction['BIZPROC_STATUS_HINT'] =
					'<div class=\'bizproc-item-title\'>'.
						htmlspecialcharsbx($docTemplateName !== '' ? $docTemplateName : GetMessage('CRM_BPLIST')).
						': <span class=\'bizproc-item-title bizproc-state-title\'><a href=\''.$arConstruction['PATH_TO_BIZPROC_LIST'].'\'>'.
						htmlspecialcharsbx($docTtl !== '' ? $docTtl : $docName).'</a></span></div>';
			}
		}
		elseif($docStatesQty > 1)
		{
			foreach ($arDocumentStates as &$arDocState)
			{
				$docTemplateID = $arDocState['TEMPLATE_ID'];
				$paramName = "BIZPROC_{$docTemplateID}";
				$docTtl = isset($arDocState['STATE_TITLE']) ? $arDocState['STATE_TITLE'] : '';

				if($isInExportMode)
				{
					$arConstruction[$paramName] = $docTtl;
				}
				else
				{
					$arConstruction[$paramName] = '<a href="'.htmlspecialcharsbx($arConstruction['PATH_TO_BIZPROC_LIST']).'">'.htmlspecialcharsbx($docTtl).'</a>';

					$docID = $arDocState['ID'];
					//TODO: wait for bizproc bugs will be fixed and replace serial call of CCrmBizProcHelper::GetUserWorkflowTaskCount on single call
					$taskQty = CCrmBizProcHelper::GetUserWorkflowTaskCount(array($docID), $userID);
					if($taskQty === 0)
					{
						continue;
					}

					if ($arConstruction['BIZPROC_STATUS'] !== 'attention')
					{
						$arConstruction['BIZPROC_STATUS'] = 'attention';
					}

					$totalTaskQty += $taskQty;
					if($totalTaskQty > 5)
					{
						break;
					}
				}
			}
			unset($arDocState);

			if(!$isInExportMode)
			{
				$arConstruction['BIZPROC_STATUS_HINT'] =
					'<span class=\'bizproc-item-title\'>'.GetMessage('CRM_BP_R_P').': <a href=\''.$arConstruction['PATH_TO_BIZPROC_LIST'].'\' title=\''.GetMessage('CRM_BP_R_P_TITLE').'\'>'.$docStatesQty.'</a></span>'.
					($totalTaskQty === 0
						? ''
						: '<br /><span class=\'bizproc-item-title\'>'.GetMessage('CRM_TASKS').': <a href=\''.$arConstruction['PATH_TO_USER_BP'].'\' title=\''.GetMessage('CRM_TASKS_TITLE').'\'>'.$totalTaskQty.($totalTaskQty > 5 ? '+' : '').'</a></span>');
			}
		}
	}

	$arConstruction['ASSIGNED_BY_ID'] = $arConstruction['~ASSIGNED_BY_ID'] = isset($arConstruction['~ASSIGNED_BY']) ? (int)$arConstruction['~ASSIGNED_BY'] : 0;
	$arConstruction['~ASSIGNED_BY'] = CUser::FormatName(
		$arParams['NAME_TEMPLATE'],
		array(
			'LOGIN' => isset($arConstruction['~ASSIGNED_BY_LOGIN']) ? $arConstruction['~ASSIGNED_BY_LOGIN'] : '',
			'NAME' => isset($arConstruction['~ASSIGNED_BY_NAME']) ? $arConstruction['~ASSIGNED_BY_NAME'] : '',
			'LAST_NAME' => isset($arConstruction['~ASSIGNED_BY_LAST_NAME']) ? $arConstruction['~ASSIGNED_BY_LAST_NAME'] : '',
			'SECOND_NAME' => isset($arConstruction['~ASSIGNED_BY_SECOND_NAME']) ? $arConstruction['~ASSIGNED_BY_SECOND_NAME'] : ''
		),
		true, false
	);
	$arConstruction['ASSIGNED_BY'] = htmlspecialcharsbx($arConstruction['~ASSIGNED_BY']);

	if(isset($arSelectMap['FULL_ADDRESS']))
	{
		$arConstruction['FULL_ADDRESS'] = ConstructionAddressFormatter::format($arConstruction, $addressFormatOptions);
	}

	$arResult['CONSTRUCTION'][$entityID] = $arConstruction;
	$arResult['CONSTRUCTION_UF'][$entityID] = array();
	$arResult['CONSTRUCTION_ID'][$entityID] = $entityID;
}
unset($arConstruction);

$CCrmUserType->ListAddEnumFieldsValue(
	$arResult,
	$arResult['CONSTRUCTION'],
	$arResult['CONSTRUCTION_UF'],
	($isInExportMode ? ', ' : '<br />'),
	$isInExportMode,
	array(
		'FILE_URL_TEMPLATE' =>
			'/local/components/citfact/crm.construction.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#'
	)
);

$arResult['ENABLE_TOOLBAR'] = isset($arParams['ENABLE_TOOLBAR']) ? $arParams['ENABLE_TOOLBAR'] : false;
if($arResult['ENABLE_TOOLBAR'])
{
	$arResult['PATH_TO_CONSTRUCTION_ADD'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_CONSTRUCTION_EDIT'],
		array('construction_id' => 0)
	);

	$addParams = array();

	if($bInternal && isset($arParams['INTERNAL_CONTEXT']) && is_array($arParams['INTERNAL_CONTEXT']))
	{
		$internalContext = $arParams['INTERNAL_CONTEXT'];
		if(isset($internalContext['COMPANY_ID']))
		{
			$addParams['company_id'] = $internalContext['COMPANY_ID'];
		}
	}

	if(!empty($addParams))
	{
		$arResult['PATH_TO_CONSTRUCTION_ADD'] = CHTTP::urlAddParams(
			$arResult['PATH_TO_CONSTRUCTION_ADD'],
			$addParams
		);
	}
}

// adding crm multi field to result array
if (isset($arResult['CONSTRUCTION_ID']) && !empty($arResult['CONSTRUCTION_ID']))
{
	$arFmList = array();
	$res = CCrmFieldMulti::GetList(array('ID' => 'asc'), array('ENTITY_ID' => 'CONSTRUCTION', 'ELEMENT_ID' => $arResult['CONSTRUCTION_ID']));
	while($ar = $res->Fetch())
	{
		if (!$isInExportMode)
			$arFmList[$ar['ELEMENT_ID']][$ar['COMPLEX_ID']][] = CCrmFieldMulti::GetTemplateByComplex($ar['COMPLEX_ID'], $ar['VALUE']);
		else
			$arFmList[$ar['ELEMENT_ID']][$ar['COMPLEX_ID']][] = $ar['VALUE'];
		$arResult['CONSTRUCTION'][$ar['ELEMENT_ID']]['~'.$ar['COMPLEX_ID']][] = $ar['VALUE'];
	}

	foreach ($arFmList as $elementId => $arFM)
	{
		foreach ($arFM as $complexId => $arComplexName)
		{
			$arResult['CONSTRUCTION'][$elementId][$complexId] = implode(', ', $arComplexName);
		}
	}

	// checking access for operation
	$arConstructionAttr = CCrmPerms::GetEntityAttr('CONSTRUCTION', $arResult['CONSTRUCTION_ID']);
	foreach ($arResult['CONSTRUCTION_ID'] as $iConstructionId)
	{
		$arResult['CONSTRUCTION'][$iConstructionId]['EDIT'] = $userPermissions->CheckEnityAccess('CONSTRUCTION', 'WRITE', $arConstructionAttr[$iConstructionId]);
		$arResult['CONSTRUCTION'][$iConstructionId]['DELETE'] = $userPermissions->CheckEnityAccess('CONSTRUCTION', 'DELETE', $arConstructionAttr[$iConstructionId]);

		$arResult['CONSTRUCTION'][$iConstructionId]['BIZPROC_LIST'] = array();

		if ($isBizProcInstalled)
		{
			foreach ($arBPData as $arBP)
			{
				if (!CBPDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::StartWorkflow,
					$userID,
					array('crm', 'CCrmDocumentConstruction', 'CONSTRUCTION_'.$arResult['CONSTRUCTION'][$iConstructionId]['ID']),
					array(
						'UserGroups' => $CCrmBizProc->arCurrentUserGroups,
						'DocumentStates' => $arDocumentStates,
						'WorkflowTemplateId' => $arBP['ID'],
						'CreatedBy' => $arResult['CONSTRUCTION'][$iConstructionId]['~ASSIGNED_BY_ID'],
						'UserIsAdmin' => $isAdmin,
						'CRMEntityAttr' => $arConstructionAttr
					)
				))
				{
					continue;
				}

				$arBP['PATH_TO_BIZPROC_START'] = CHTTP::urlAddParams(CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CONSTRUCTION_SHOW'],
					array(
						'construction_id' => $arResult['CONSTRUCTION'][$iConstructionId]['ID']
					)),
					array(
						'workflow_template_id' => $arBP['ID'], 'bizproc_start' => 1,  'sessid' => $arResult['SESSION_ID'],
						'CRM_CONSTRUCTION_SHOW_V12_active_tab' => 'tab_bizproc', 'back_url' => $arParams['PATH_TO_CONSTRUCTION_LIST'])
				);

				if (isset($arBP['HAS_PARAMETERS']))
				{
					$params = \Bitrix\Main\Web\Json::encode(array(
						'moduleId' => 'crm',
						'entity' => 'CCrmDocumentConstruction',
						'documentType' => 'CONSTRUCTION',
						'documentId' => 'CONSTRUCTION_'.$arResult['CONSTRUCTION'][$iConstructionId]['ID'],
						'templateId' => $arBP['ID'],
						'templateName' => $arBP['NAME'],
						'hasParameters' => $arBP['HAS_PARAMETERS']
					));
					$arBP['ONCLICK'] = 'BX.Bizproc.Starter.singleStart('.$params
						.', function(){BX.Main.gridManager.reload(\''.CUtil::JSEscape($arResult['GRID_ID']).'\');});';
				}

				$arResult['CONSTRUCTION'][$iConstructionId]['BIZPROC_LIST'][] = $arBP;
			}
		}
	}
}

if (is_array($arResult['CONSTRUCTION_ID']) && !empty($arResult['CONSTRUCTION_ID']))
{
	if ($isInExportMode && $isStExport && $isStExportRequisiteMultiline)
	{
		/*$requisiteExportInfo =
			$requisite->prepareEntityListRequisiteExportData(CCrmOwnerType::Construction, $arResult['CONSTRUCTION_ID']);
		$arResult['STEXPORT_RQ_HEADERS'] = $requisiteExportInfo['HEADERS'];
		$requisiteExportData = $requisiteExportInfo['EXPORT_DATA'];
		unset($requisiteExportInfo);
		$arResult['STEXPORT_RQ_DATA'] = $requisite->entityListRequisiteExportDataFormatMultiline(
			$requisiteExportData,
			$arResult['STEXPORT_RQ_HEADERS'],
			array('EXPORT_TYPE' => $sExportType)
		);
		unset($requisiteExportData);*/
	}
	else
	{
		$requisite->prepareEntityListFieldsValues(
			$arResult['CONSTRUCTION'],
			CCrmOwnerType::Construction,
			$arResult['CONSTRUCTION_ID'],
			$rqSelect,
			array('EXPORT_TYPE' => $sExportType)
		);
	}
}

if (!$isInExportMode)
{
	$arResult['ANALYTIC_TRACKER'] = array(
		'lead_enabled' => \Bitrix\Crm\Settings\LeadSettings::getCurrent()->isEnabled() ? 'Y' : 'N'
	);

	$arResult['NEED_FOR_REBUILD_DUP_INDEX'] =
		$arResult['NEED_FOR_REBUILD_SEARCH_CONTENT'] =
		$arResult['NEED_FOR_REBUILD_CONSTRUCTION_ATTRS'] =
		$arResult['NEED_FOR_TRANSFER_REQUISITES'] =
		$arResult['NEED_FOR_BUILD_TIMELINE'] =
		$arResult['NEED_FOR_BUILD_DUPLICATE_INDEX'] = false;

	if(!$bInternal)
	{
		if(COption::GetOptionString('crm', '~CRM_REBUILD_CONSTRUCTION_SEARCH_CONTENT', 'N') === 'Y')
		{
			$arResult['NEED_FOR_REBUILD_SEARCH_CONTENT'] = true;
		}

		$arResult['NEED_FOR_BUILD_TIMELINE'] = \Bitrix\Crm\Agent\Timeline\ConstructionTimelineBuildAgent::getInstance()->isEnabled();

		$agent = Bitrix\Crm\Agent\Duplicate\ConstructionDuplicateIndexRebuildAgent::getInstance();
		$isAgentEnabled = $agent->isEnabled();
		if ($isAgentEnabled)
		{
			if (!$agent->isActive())
			{
				$agent->enable(false);
				$isAgentEnabled = false;
			}
		}
		$arResult['NEED_FOR_BUILD_DUPLICATE_INDEX'] = $isAgentEnabled;
		unset ($agent, $isAgentEnabled);

		if(CCrmPerms::IsAdmin())
		{
			if(COption::GetOptionString('crm', '~CRM_REBUILD_CONSTRUCTION_DUP_INDEX', 'N') === 'Y')
			{
				$arResult['NEED_FOR_REBUILD_DUP_INDEX'] = true;
			}
			if(COption::GetOptionString('crm', '~CRM_REBUILD_CONSTRUCTION_ATTR', 'N') === 'Y')
			{
				$arResult['PATH_TO_PRM_LIST'] = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_perm_list'));
				$arResult['NEED_FOR_REBUILD_CONSTRUCTION_ATTRS'] = true;
			}
			if(COption::GetOptionString('crm', '~CRM_TRANSFER_REQUISITES_TO_CONSTRUCTION', 'N') === 'Y')
			{
				$arResult['NEED_FOR_TRANSFER_REQUISITES'] = true;
			}
		}
	}

	$this->IncludeComponentTemplate();
	include_once($_SERVER['DOCUMENT_ROOT'].'/local/components/citfact/crm.construction/include/nav.php');
	return $arResult['ROWS_COUNT'];
}
else
{
	if ($isStExport)
	{
		$this->__templateName = '.default';

		$this->IncludeComponentTemplate($sExportType);

		return array(
			'PROCESSED_ITEMS' => count($arResult['CONSTRUCTION']),
			'TOTAL_ITEMS' => $arResult['STEXPORT_TOTAL_ITEMS']
		);
	}
	else
	{
		$APPLICATION->RestartBuffer();
		// hack. any '.default' customized template should contain 'excel' page
		$this->__templateName = '.default';

		if ($sExportType === 'carddav')
		{
			Header('Content-Type: text/vcard');
		}
		elseif ($sExportType === 'csv')
		{
			Header('Content-Type: text/csv');
			Header('Content-Disposition: attachment;filename=constructions.csv');
		}
		elseif ($sExportType === 'excel')
		{
			Header('Content-Type: application/vnd.ms-excel');
			Header('Content-Disposition: attachment;filename=constructions.xls');
		}
		Header('Content-Type: application/octet-stream');
		Header('Content-Transfer-Encoding: binary');

		// add UTF-8 BOM marker
		if (defined('BX_UTF') && BX_UTF)
			echo chr(239).chr(187).chr(191);

		$this->IncludeComponentTemplate($sExportType);

		die();
	}
}
?>