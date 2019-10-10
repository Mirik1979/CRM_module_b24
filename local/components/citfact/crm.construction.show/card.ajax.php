<?
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if(!CModule::IncludeModule('crm'))
	return ;

global $APPLICATION;

$CCrmPerms = CCrmPerms::GetCurrentUserPermissions();
if (!(CCrmPerms::IsAuthorized() && CCrmConstruction::CheckReadPermission(0, $CCrmPerms)))
	return;

$arResult = array();
$entityId = $_GET['USER_ID'];
$_GET['USER_ID'] = preg_replace('/^(CONSTRUCTION|COMPANY|LEAD|DEAL)_/i'.BX_UTF_PCRE_MODIFIER, '', $_GET['USER_ID']);
$iConstructionId = (int) $_GET['USER_ID'];
$iVersion = (!empty($_GET["version"]) ? intval($_GET["version"]) : 1);

if ($iConstructionId > 0)
{
	\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

	$arParams['PATH_TO_CONSTRUCTION_SHOW'] = '/crm/construction/details/#construction_id#/';
	$arParams['PATH_TO_CONSTRUCTION_EDIT'] = '/crm/construction/details/#construction_id#/?init_mode=edit';;
	$arParams['PATH_TO_COMPANY_SHOW'] = CrmCheckPath('PATH_TO_COMPANY_SHOW', $arParams['PATH_TO_COMPANY_SHOW'], $APPLICATION->GetCurPage().'?company_id=#company_id#&show');
	$arResult['TYPE_LIST'] = CCrmStatus::GetStatusListEx('CONSTRUCTION_TYPE');

	$obRes = CCrmConstruction::GetListEx(array(), array('=ID' => $iConstructionId));
	$arConstruction = $obRes->Fetch();
	if ($arConstruction == false)
		return ;

	$arConstruction['PATH_TO_CONSTRUCTION_SHOW'] = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CONSTRUCTION_SHOW'],
		array(
			'construction_id' => $iConstructionId
		)
	);

	$arConstruction['PATH_TO_CONSTRUCTION_EDIT'] = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CONSTRUCTION_EDIT'],
		array(
			'construction_id' => $iConstructionId
		)
	);

	$arConstruction['PATH_TO_COMPANY_SHOW'] = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_COMPANY_SHOW'],
		array(
			'company_id' => $arConstruction['COMPANY_ID']
		)
	);

	$arConstruction['FORMATTED_NAME'] = CCrmConstruction::PrepareFormattedName(
		array(
			'HONORIFIC' => isset($arConstruction['HONORIFIC']) ? $arConstruction['HONORIFIC'] : '',
			'NAME' => isset($arConstruction['NAME']) ? $arConstruction['NAME'] : '',
			'LAST_NAME' => isset($arConstruction['LAST_NAME']) ? $arConstruction['LAST_NAME'] : '',
			'SECOND_NAME' => isset($arConstruction['SECOND_NAME']) ? $arConstruction['SECOND_NAME'] : ''
		)
	);

	//region Multifields
	$arEntityTypes = CCrmFieldMulti::GetEntityTypes();
	$multiFieldHtml = array();

	$sipConfig =  array(
		'ENABLE_SIP' => true,
		'SIP_PARAMS' => array(
			'ENTITY_TYPE' => 'CRM_'.CCrmOwnerType::ConstructionName,
			'ENTITY_ID' => $iConstructionId)
	);

	$dbRes = CCrmFieldMulti::GetListEx(
		array(),
		array('ENTITY_ID' => CCrmOwnerType::ConstructionName, 'ELEMENT_ID' => $iConstructionId, '@TYPE_ID' => array('PHONE', 'EMAIL')),
		false,
		false,
		array('TYPE_ID', 'VALUE_TYPE', 'VALUE')
	);

	while($multiField = $dbRes->Fetch())
	{
		$typeID = isset($multiField['TYPE_ID']) ? $multiField['TYPE_ID'] : '';

		if(isset($multiFieldHtml[$typeID]))
		{
			continue;
		}

		$value = isset($multiField['VALUE']) ? $multiField['VALUE'] : '';
		$valueType = isset($multiField['VALUE_TYPE']) ? $multiField['VALUE_TYPE'] : '';

		$entityType = $arEntityTypes[$typeID];
		$valueTypeInfo = isset($entityType[$valueType]) ? $entityType[$valueType] : null;

		$params = array('VALUE' => $value, 'VALUE_TYPE_ID' => $valueType, 'VALUE_TYPE' => $valueTypeInfo);
		$item = CCrmViewHelper::PrepareMultiFieldValueItemData($typeID, $params, $sipConfig);
		if(isset($item['value']) && $item['value'] !== '')
		{
			$multiFieldHtml[$typeID] = $item['value'];
		}
	}
	//endregion

	$strName = ($iVersion >= 2 ? '<a href="'.$arConstruction['PATH_TO_CONSTRUCTION_SHOW'].'" target="_blank">'.htmlspecialcharsbx($arConstruction['FORMATTED_NAME']).'</a>' : '');

	if ($iVersion >= 2)
	{
		$fields = '';

		if (!empty($arConstruction['TYPE_ID']))
		{
			$fields .= '<span class="bx-ui-tooltip-field-row">
				<span class="bx-ui-tooltip-field-name">'.GetMessage('CRM_COLUMN_TYPE').'</span>: <span class="bx-ui-tooltip-field-value"><span class="fields enumeration">'.$arResult['TYPE_LIST'][$arConstruction['TYPE_ID']].'</span></span>
			</span>';
		}
		$fields .= '<span class="bx-ui-tooltip-field-row">
			<span class="bx-ui-tooltip-field-name">'.GetMessage('CRM_COLUMN_DATE_MODIFY').'</span>: <span class="bx-ui-tooltip-field-value"><span class="fields enumeration">'.FormatDate('x', MakeTimeStamp($arConstruction['DATE_MODIFY']), (time() + CTimeZone::GetOffset())).'</span></span>
		</span>';

//		$fields .= '<span class="bx-ui-tooltip-field-row">'.GetMessage('CRM_SECTION_CONSTRUCTION_INFO').'</span>';

		if (isset($multiFieldHtml['PHONE']))
		{
			$fields .= '<span class="bx-ui-tooltip-field-row">
				<span class="bx-ui-tooltip-field-name">'.GetMessage('CRM_COLUMN_PHONE').'</span>: <span class="bx-ui-tooltip-field-value">'.$multiFieldHtml['PHONE'].'</span>
			</span>';
		}
		if (isset($multiFieldHtml['EMAIL']))
		{
			$fields .= '<span class="bx-ui-tooltip-field-row">
				<span class="bx-ui-tooltip-field-name">'.GetMessage('CRM_COLUMN_EMAIL').'</span>: <span class="bx-ui-tooltip-field-value">'.$multiFieldHtml['EMAIL'].'</span>
			</span>';
		}
		if (!empty($arConstruction['COMPANY_TITLE']))
		{
			$fields .= '<span class="bx-ui-tooltip-field-row">
				<span class="bx-ui-tooltip-field-name">'.GetMessage('CRM_COLUMN_COMPANY_TITLE').'</span>: <span class="bx-ui-tooltip-field-value"><a href="'.$arConstruction['PATH_TO_COMPANY_SHOW'].'" target="_blank">'.htmlspecialcharsbx($arConstruction['COMPANY_TITLE']).'</a></span>
			</span>';
		}

		$strCard = '<div class="bx-ui-tooltip-info-data-cont" id="bx_user_info_data_cont_'.htmlspecialcharsbx($entityId).'"><div class="bx-ui-tooltip-info-data-info">'.$fields.'</div></div>';
	}
	else
	{
		$strCard = '
<div class="bx-user-info-data-cont-video  bx-user-info-fields" id="bx_user_info_data_cont_1">
	<div class="bx-user-info-data-name ">
		<a href="'.$arConstruction['PATH_TO_CONSTRUCTION_SHOW'].'" target="_blank">'.htmlspecialcharsbx($arConstruction['FORMATTED_NAME']).'</a>
	</div>
	<div class="bx-user-info-data-info">';
		if (!empty($arConstruction['TYPE_ID']))
		{
			$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_TYPE').'</span>:
		<span class="fields enumeration">'.$arResult['TYPE_LIST'][$arConstruction['TYPE_ID']].'</span>
		<br />';
		}
		$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_DATE_MODIFY').'</span>:
		<span class="fields enumeration">'.FormatDate('x', MakeTimeStamp($arConstruction['DATE_MODIFY']), (time() + CTimeZone::GetOffset())).'</span>
		<br />
		<br />
	</div>
	<div class="bx-user-info-data-name bx-user-info-seporator">
		<nobr>'.GetMessage('CRM_SECTION_CONSTRUCTION_INFO').'</nobr>
	</div>
	<div class="bx-user-info-data-info">';
		if (isset($multiFieldHtml['PHONE']))
		{
			$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_PHONE').'</span>:
		<span class="crm-client-constructions-block-text crm-client-constructions-block-handset">'.$multiFieldHtml['PHONE'].'</span>
		<br />';
		}
		if (isset($multiFieldHtml['EMAIL']))
		{
			$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_EMAIL').'</span>:
		<span class="crm-client-constructions-block-text">'.$multiFieldHtml['EMAIL'].'</span>
		<br />';
		}
		$strCard .= '<br />';
		if (!empty($arConstruction['COMPANY_TITLE']))
		{
			$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_COMPANY_TITLE').'</span>:
		<a href="'.$arConstruction['PATH_TO_COMPANY_SHOW'].'" target="_blank">'.htmlspecialcharsbx($arConstruction['COMPANY_TITLE']).'</a>
		<br /> ';
		}
		$strCard .= '</div>
</div>';
	}

	if (!empty($arConstruction['PHOTO']))
	{
		$imageFile = CFile::GetFileArray($arConstruction['PHOTO']);
		if ($imageFile !== false)
		{
			$arFileTmp = CFile::ResizeImageGet(
				$imageFile,
				array('width' => 102, 'height' => 104),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				false
			);
			$imageImg = CFile::ShowImage($arFileTmp['src'], 102, 104, "border='0'", '');
		}
		if (strlen($imageImg)>0)
			$strPhoto = '<a href="'.$arConstruction['PATH_TO_CONSTRUCTION_SHOW'].'" class="bx-user-info-data-photo" target="_blank">'.$imageImg.'</a>';
		else
			$strPhoto = '<a href="'.$arConstruction['PATH_TO_CONSTRUCTION_SHOW'].'" class="bx-user-info-data-photo no-photo" target="_blank"></a>';
	}
	else
		$strPhoto = '<a href="'.$arConstruction['PATH_TO_CONSTRUCTION_SHOW'].'" class="bx-user-info-data-photo no-photo" target="_blank"></a>';

	$strToolbar2 = '
<div class="bx-user-info-data-separator"></div>
<ul>
	<li class="bx-icon bx-icon-show">
		<a href="'.$arConstruction['PATH_TO_CONSTRUCTION_SHOW'].'" target="_blank">'.GetMessage('CRM_OPER_SHOW').'</a>
	</li>
	<li class="bx-icon bx-icon-message">
		<a href="'.$arConstruction['PATH_TO_CONSTRUCTION_EDIT'].'" target="_blank">'.GetMessage('CRM_OPER_EDIT').'</a>
	</li>
</ul>';

	$script = '
		var params = 
		{
			serviceUrls: 
			{ 
				"CRM_'.CUtil::JSEscape(CCrmOwnerType::ConstructionName).'" : 
					"/local/components/citfact/crm.construction.show/ajax.php?'.bitrix_sessid_get().'"
			},
			messages: 
			{
				"unknownRecipient": "'.GetMessageJS('CRM_SIP_MGR_UNKNOWN_RECIPIENT').'",
				"makeCall": "'.GetMessageJS('CRM_SIP_MGR_MAKE_CALL').'"						
			}
		};
		
		if(typeof(BX.CrmSipManager) === "undefined")
		{
			BX.loadScript(
				"/bitrix/js/crm/common.js", 
				function() { BX.CrmSipManager.ensureInitialized(params); }
			);
		}
		else
		{
			BX.CrmSipManager.ensureInitialized(params);
		}';

	$arResult = array(
		'Toolbar' => '',
		'ToolbarItems' => '',
		'Toolbar2' => $strToolbar2,
		'Name' => $strName,
		'Card' => $strCard,
		'Photo' => $strPhoto,
		'Scripts' => array($script)
	);
}

$APPLICATION->RestartBuffer();
Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
echo CUtil::PhpToJsObject(array('RESULT' => $arResult));
if(!defined('PUBLIC_AJAX_MODE'))
{
	define('PUBLIC_AJAX_MODE', true);
}
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
die();

?>
