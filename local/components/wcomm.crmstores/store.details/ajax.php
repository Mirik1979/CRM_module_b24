<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main;
use Bitrix\Crm;
use Wcomm\CrmStores\Entity\StoreTable;
use WComm\CrmStores\BizProc\StoreDocument;

if (!CModule::IncludeModule('crm'))
{
	return;
}


/*
 * ONLY 'POST' METHOD SUPPORTED
 * SUPPORTED ACTIONS:
 * 'SAVE'
 * 'RENDER_IMAGE_INPUT'
 * 'GET_FORMATTED_SUM'
 */
global $DB, $APPLICATION,  $USER_FIELD_MANAGER;;
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

if(!function_exists('__CrmCompanyDetailsEndHtmlResonse'))
{
	function __CrmCompanyDetailsEndHtmlResonse()
	{
		if(!defined('PUBLIC_AJAX_MODE'))
		{
			define('PUBLIC_AJAX_MODE', true);
		}
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
		die();
	}
}

if(!function_exists('__CrmCompanyDetailsEndJsonResonse'))
{
	function __CrmCompanyDetailsEndJsonResonse($result)
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		if(!empty($result))
		{
			echo CUtil::PhpToJSObject($result);
		}
		if(!defined('PUBLIC_AJAX_MODE'))
		{
			define('PUBLIC_AJAX_MODE', true);
		}
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
		die();
	}
}

if (!CCrmSecurityHelper::IsAuthorized() || !check_bitrix_sessid() || $_SERVER['REQUEST_METHOD'] != 'POST')
{
	return;
}

CUtil::JSPostUnescape();
$APPLICATION->RestartBuffer();
Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

//\Bitrix\Main\Diag\Debug::writeToFile($_POST, "POST", "__miros.log");

$currentUserID = CCrmSecurityHelper::GetCurrentUserID();
$currentUserPermissions =  CCrmPerms::GetCurrentUserPermissions();



$action = isset($_POST['ACTION']) ? $_POST['ACTION'] : '';
if($action === '' && isset($_POST['MODE']))
{
	$action = $_POST['MODE'];
}
if($action === '')
{
	__CrmCompanyDetailsEndJsonResonse(array('ERROR'=>'ACTION IS NOT DEFINED!'));
}
if($action === 'SAVE')
{
    if (!Main\Loader::includeModule('wcomm.crmstores')) {
       // \Bitrix\Main\Diag\Debug::writeToFile("modulenonactive", "POST", "__miros.log");
        ShowError(Loc::getMessage('модуль не установлен'));
        return;
    } else {
        //\Bitrix\Main\Diag\Debug::writeToFile("moduleactive", "POST", "__miros.log");

    }
    $ID = isset($_POST['ACTION_ENTITY_ID']) ? max((int)$_POST['ACTION_ENTITY_ID'], 0) : 0;
    //\Bitrix\Main\Diag\Debug::writeToFile($_POST, "POST", "__miros.log");
	/* ролевая модель
	if(($ID > 0 && !\CCrmCompany::CheckUpdatePermission($ID, $currentUserPermissions))
		|| ($ID === 0 && !\CCrmCompany::CheckCreatePermission($currentUserPermissions))
	)
	{
		__CrmCompanyDetailsEndJsonResonse(array('ERROR'=>'PERMISSION DENIED!'));
	} */

	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();
	// видимо под копирование
	$sourceEntityID =  isset($params['COMPANY_ID']) ? (int)$params['COMPANY_ID'] : 0;

	$isNew = $ID === 0;
	$isCopyMode = $isNew && $sourceEntityID > 0;
    //\Bitrix\Main\Diag\Debug::writeToFile("miros", "POST", "__miros.log");
	$fields = array();
    //\Bitrix\Main\Diag\Debug::writeToFile("miros", "POST", "__miros.log");
	$fieldsInfo = Wcomm\CrmStores\Entity\StoreTable::GetFieldsInfo();

    //\Bitrix\Main\Diag\Debug::writeToFile($fieldsInfo, "FINFO", "__miros.log");
    $ufID = StoreTable::getUfId();
    //\Bitrix\Main\Diag\Debug::writeToFile($ufID, "UF", "__miros.log");

    $userType = new \CCrmUserType($GLOBALS['USER_FIELD_MANAGER'], StoreTable::getUfId());
	if(isset($params['IS_MY_COMPANY']) && $params['IS_MY_COMPANY'] == 'Y')
	{
		$userType->setOptions(['isMyCompany' => true]);
	}
	$userType->PrepareFieldsInfo($fieldsInfo);
	//\CCrmFieldMulti::PrepareFieldsInfo($fieldsInfo);

	$presentFields = array();
	if($ID > 0)
	{
		$dbResult = StoreTable::GetbyID($ID);
        $presentFields = $dbResult->Fetch();
        $arUserFields = $USER_FIELD_MANAGER->GetUserFields(StoreTable::getUfId());
		//$presentFields = $dbResult->Fetch();
        foreach($arUserFields as $FIELD_ID => $arField) {
            $presentFields[$FIELD_ID] = $USER_FIELD_MANAGER->GetUserFieldValue(StoreTable::getUfId(), $FIELD_ID, $ID);
        }

		if(!is_array($presentFields))
		{
			$presentFields = array();
		}
	} else {
        $arUserFields = $USER_FIELD_MANAGER->GetUserFields(StoreTable::getUfId());
        $presentFields = array_merge($fieldsInfo, $arUserFields);
    }
    //\Bitrix\Main\Diag\Debug::writeToFile("prf", "prfields", "__miros.log");
    //\Bitrix\Main\Diag\Debug::writeToFile($presentFields, "prfields", "__miros.log");

	// копроваие
	 $sourceFields = array();
	if($sourceEntityID > 0)
	{
        $dbResult = StoreTable::GetListEx(
            array(),
            array('=ID' => $ID),
            false,
            false,
            array('*')
        );
        $sourceFields = $dbResult;
        //$presentFields = $dbResult->Fetch();
        if(!is_array($sourceFields))
        {
            $sourceFields = array();
        }
	}


	foreach($presentFields as $name => $info)
	{
	    if($isNew) {
            $fields[$name] = $_POST[$name];
        } else {
            if ($_POST[$name]) {
                $fields[$name] = $_POST[$name];
            } else {
                $fields[$name] = $presentFields[$name];
            }

        }

	}




	if($isNew && isset($params['IS_MY_COMPANY']) && $params['IS_MY_COMPANY'] === 'Y')
	{
		$fields['IS_MY_COMPANY'] = 'Y';
	}

    //\Bitrix\Main\Diag\Debug::writeToFile($fields, "FINFOS", "__miros.log");

	//region CLIENT
	/*$clientData = null;
	if(isset($_POST['CLIENT_DATA']) && $_POST['CLIENT_DATA'] !== '')
	{
		try
		{
			$clientData = Main\Web\Json::decode(
				Main\Text\Encoding::convertEncoding($_POST['CLIENT_DATA'], LANG_CHARSET, 'UTF-8')
			);
		}
		catch (Main\SystemException $e)
		{
		}
	} */


	$createdEntities = array();

	$errorMessage = '';
		if(!empty($fields))
		{
			if(isset($fields['ASSIGNED_BY_ID']) && $fields['ASSIGNED_BY_ID'] > 0)
			{
				\Bitrix\Crm\Entity\EntityEditor::registerSelectedUser($fields['ASSIGNED_BY_ID']);
			}
            // копирование
			if($isCopyMode)
			{
				if(!isset($fields['ASSIGNED_BY_ID']))
				{
					$fields['ASSIGNED_BY_ID'] = $currentUserID;
				}

				\Bitrix\Crm\Entity\EntityEditor::prepareForCopy($fields, $userType);
				$merger = new \Bitrix\Crm\Merger\CompanyMerger($currentUserID, false);
				//Merge with disabling of multiple user fields (SKIP_MULTIPLE_USER_FIELDS = TRUE)
				$merger->mergeFields(
					$sourceFields,
					$fields,
					true,
					array('SKIP_MULTIPLE_USER_FIELDS' => true)
				);
			}

			if(isset($fields['COMMENTS']))
			{
				$fields['COMMENTS'] = \Bitrix\Crm\Format\TextHelper::sanitizeHtml($fields['COMMENTS']);
			}

			$entity = new StoreTable(false);
			if($isNew)
			{
                \Bitrix\Main\Diag\Debug::writeToFile($fields, "FIELDSTOADD", "__miros.log");
			    //unset($fields['ID']);
                $fields['ADDRESS'] = "";
                //$fields['UF_CRM_1571643732'] = array();
                \Bitrix\Main\Diag\Debug::writeToFile($fields, "FIELDSTOADD", "__miros.log");
                $oID = $entity->Add($fields);
                $ID = $oID->getId();
                //\Bitrix\Main\Diag\Debug::writeToFile($ID, "ID", "__miros.log");
				if($ID <= 0)
				{
					$errorMessage = $entity->LAST_ERROR;
				}
                if (!Main\Loader::includeModule('bizproc')) {
                    return array();
                }
                CBPDocument::AutoStartWorkflows(
                    StoreDocument::getComplexDocumentType(),
                    CBPDocumentEventType::Create,
                    StoreDocument::getComplexDocumentId($ID),
                    array(),
                    $errors
                );
            }
			else
			{
                //\Bitrix\Main\Diag\Debug::writeToFile($fields, "forupdate", "__miros.log");
			    if(!$entity->Update($ID, $fields, true, true,  array('REGISTER_SONET_EVENT' => true)))
				{
                    //\Bitrix\Main\Diag\Debug::writeToFile("nonupd", "FINFOS", "__miros.log");
				    $errorMessage = $entity->LAST_ERROR;
				}

                $CCrmEvent = new CCrmEvent();
                $CCrmEvent->Add(
                    array(
                        'ENTITY_TYPE'=> 'CRM_STORES',
                        'ENTITY_ID' => $ID,
                        'EVENT_ID' => 'INFO',
                        'EVENT_TEXT_1' => 'Карточка объекта изменена',
                        'DATE_CREATE' => date("d.m.Y G:i:s"),
                        //'FILES' => array(
                        //  CFile::MakeFileArray('/bitrix/templates/bitrix24/images/template_sprite_21.png')
                        //)
                    )
                );



			    if (!Main\Loader::includeModule('bizproc')) {
                    return array();
                }

                //static $eventMap = array(
                //    self::EVENT_CREATED => CBPDocumentEventType::Create,
                //    self::EVENT_UPDATED => CBPDocumentEventType::Edit,
                //);

                CBPDocument::AutoStartWorkflows(
                      StoreDocument::getComplexDocumentType(),
                      \CBPDocumentEventType::Edit,
                      StoreDocument::getComplexDocumentId($ID),
                      array(),
                      $errors
                );

			}
		}

		if($errorMessage !== '')
		{
			//Deletion early created entities
			foreach($createdEntities as $entityTypeID => $entityIDs)
			{
				foreach($entityIDs as $entityID)
				{
					\Bitrix\Crm\Component\EntityDetails\BaseComponent::deleteEntity($entityTypeID, $entityID);
				}
			}
			__CrmCompanyDetailsEndJsonResonse(array('ERROR' => $errorMessage));
		}





		/*\Bitrix\Crm\Tracking\UI\Details::saveEntityData(
			\CCrmOwnerType::Company,
			$ID,
			$_POST,
			$isNew
		); */
        // бизнес-процессы - позже
		/*$arErrors = array();
		\CCrmBizProcHelper::AutoStartWorkflows(
			\CCrmOwnerType::Company,
			$ID,
			$isNew ? \CCrmBizProcEventType::Create : \CCrmBizProcEventType::Edit,
			$arErrors,
			isset($_POST['bizproc_parameters']) ? $_POST['bizproc_parameters'] : null
		); */




	CBitrixComponent::includeComponentClass('wcomm.crmstores:store.details');
	$component = new CStoreDetailsComponent();
	$component->initializeParams($params);
	$component->setEntityID($ID);
	$result = array(
		'ENTITY_ID' => $ID,
		'ENTITY_DATA' => $component->prepareEntityData(),
		'ENTITY_INFO' => $component->prepareEntityInfo()
	);

    \Bitrix\Main\Diag\Debug::writeToFile($result, "result", "__miros.log");


	if($isNew)
	{
		$result['EVENT_PARAMS'] = array(
			'entityInfo' => array (
			    'ID' => $ID,
                'type' => 'STORES',
                'typename' => 'STORES',
                'place' => 'stores',
                'title' => $fields['NAME'],
                'url' => '/crm/stores/details/'.$ID.'/',
			)
		);

		// возможно тут переадресация - надо смотреть
		$result['REDIRECT_URL'] = '/crm/stores/details/'.$ID.'/?IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER';

	}

	__CrmCompanyDetailsEndJsonResonse($result);
}
elseif($action === 'DELETE')
{
    if (!Main\Loader::includeModule('wcomm.crmstores')) {
        // \Bitrix\Main\Diag\Debug::writeToFile("modulenonactive", "POST", "__miros.log");
        ShowError(Loc::getMessage('модуль не установлен'));
        return;
    }

    //\Bitrix\Main\Diag\Debug::writeToFile($_POST, "delete", "__miros.log");
    $ID = isset($_POST['ACTION_ENTITY_ID']) ? max((int)$_POST['ACTION_ENTITY_ID'], 0) : 0;
	if($ID <= 0)
	{
		__CrmCompanyDetailsEndJsonResonse(array('ERROR' => GetMessage('CRM_COMPANY_NOT_FOUND')));
	}

	//if(!\CCrmCompany::CheckDeletePermission($ID, $currentUserPermissions))
	//{
	//	__CrmCompanyDetailsEndJsonResonse(array('ERROR' => GetMessage('CRM_COMPANY_ACCESS_DENIED')));
	//}
    // остановка бизнес-процессовб можно забить так как БП автоматичекие
	//$bizProc = new CCrmBizProc('COMPANY');
	//if (!$bizProc->Delete($ID, \CCrmCompany::GetPermissionAttributes(array($ID))))
	//{
	//	__CrmCompanyDetailsEndJsonResonse(array('ERROR' => $bizProc->LAST_ERROR));
	//}


    //Wcomm\CrmStores\Entity\StoreTable::Delete($ID);

    $entity = new StoreTable(false);
	//if (!$entity->Delete($ID, array('PROCESS_BIZPROC' => false)))
    if (!$entity->Delete($ID))
	{
		/** @var CApplicationException $ex */
		$ex = $APPLICATION->GetException();
		__CrmCompanyDetailsEndJsonResonse(
			array(
				'ERROR' => ($ex instanceof CApplicationException) ? $ex->GetString() : GetMessage('CRM_COMPANY_DELETION_ERROR')
			)
		);
	}
	__CrmCompanyDetailsEndJsonResonse(array('ENTITY_ID' => $ID));
}
elseif($action === 'RENDER_IMAGE_INPUT')
{
	$ID = isset($_POST['ACTION_ENTITY_ID']) ? max((int)$_POST['ACTION_ENTITY_ID'], 0) : 0;
	if(($ID > 0 && !\CCrmCompany::CheckUpdatePermission($ID, $currentUserPermissions))
		|| ($ID === 0 && !\CCrmCompany::CheckCreatePermission($currentUserPermissions))
	)
	{
		__CrmCompanyDetailsEndHtmlResonse();
	}

	$fieldName = isset($_POST['FIELD_NAME']) ? $_POST['FIELD_NAME'] : '';
	if($fieldName !== '')
	{
		$value = 0;
		if($ID > 0)
		{
			$dbResult = \CCrmCompany::GetListEx(
				array(),
				array('=ID' => $ID, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array($fieldName)
			);
			$fields = $dbResult->Fetch();
			if(is_array($fields) && isset($fields[$fieldName]))
			{
				$value = (int)$fields[$fieldName];
			}
		}

		Header('Content-Type: text/html; charset='.LANG_CHARSET);
		$APPLICATION->ShowAjaxHead();
		$APPLICATION->IncludeComponent(
			'bitrix:main.file.input',
			'',
			array(
				'MODULE_ID' => 'crm',
				'MAX_FILE_SIZE' => 3145728,
				'MULTIPLE'=> 'N',
				'ALLOW_UPLOAD' => 'I',
				'SHOW_AVATAR_EDITOR' => 'Y',
				'ENABLE_CAMERA' => 'N',
				'CONTROL_ID' => strtolower($fieldName).'_uploader',
				'INPUT_NAME' => $fieldName,
				'INPUT_VALUE' => $value
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}
	__CrmCompanyDetailsEndHtmlResonse();

}
elseif($action === 'GET_FORMATTED_SUM')
{
	$sum = isset($_POST['SUM']) ? $_POST['SUM'] : 0.0;
	$currencyID = isset($_POST['CURRENCY_ID']) ? $_POST['CURRENCY_ID'] : '';
	if($currencyID === '')
	{
		$currencyID = \CCrmCurrency::GetBaseCurrencyID();
	}

	__CrmCompanyDetailsEndJsonResonse(
		array(
			'FORMATTED_SUM' => \CCrmCurrency::MoneyToString($sum, $currencyID, '#'),
			'FORMATTED_SUM_WITH_CURRENCY' => \CCrmCurrency::MoneyToString($sum, $currencyID, '')
		)
	);
}
