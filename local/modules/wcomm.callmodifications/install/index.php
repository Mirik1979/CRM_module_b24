<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Highloadblock as HL;
Loc::loadMessages(__FILE__);

if (class_exists("wcomm_callmodifications"))
	return;


//error_log(print_r(,true), 3, $_SERVER["DOCUMENT_ROOT"]."/wcomm.log");


class wcomm_callmodifications extends CModule
{
	var $MODULE_ID = "wcomm.callmodifications";
	var $MODULE_DIR = "wcomm_callmodifications";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	
	var $arrSiteList = null;

	var $errors = false;
	
	var $strActivityTableName = "";

	function wcomm_callmodifications()
	{
		$arModuleVersion = array();
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("WCOMM_CALLMODIFICATIONS_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("WCOMM_CALLMODIFICATIONS_DESCRIPTION");
		
		$this->PARTNER_NAME = GetMessage("WCOMM_CALLMODIFICATIONS_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("WCOMM_CALLMODIFICATIONS_PARTNER_URI");
		
	}

	function DoInstall()
	{
		global $USER, $APPLICATION;
		if ($USER->IsAdmin())
		{
			
			
			RegisterModuleDependences("crm", "OnActivityAdd", $this->MODULE_ID, "\\wcomm\\callmodifications\\EventListener", 'OnActivityAddV2');
			RegisterModuleDependences("crm", "OnActivityUpdate", $this->MODULE_ID, "\\wcomm\\callmodifications\\EventListener", 'OnActivityUpdateV2');
			
			
			\Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
			
			$this->InstallFiles();
			$this->CreateHLIBlocks();
			$this->CreateIBlocks();
				
				
		}
	}

	function DoUninstall()
	{
		global $USER, $APPLICATION;

		if ($USER->IsAdmin())
		{
						
			UnRegisterModuleDependences("crm", "OnActivityAdd", $this->MODULE_ID, "\\wcomm\\callmodifications\\EventListener", 'OnActivityAddV2');
			UnRegisterModuleDependences("crm", "OnActivityUpdate", $this->MODULE_ID, "\\wcomm\\callmodifications\\EventListener", 'OnActivityUpdateV2');
			
			$result = \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
			
			$this->UnInstallFiles();
			
		}
	}
	
	function CreateIBlocks()
	{
		
		
		if (CModule::IncludeModule('iblock'))
		{
			
			$obIBlockType =  new CIBlockType;
			
			
			$arrSiteList = $this->GetSiteList();
			
			$arFields = Array(
				"ID"=>$this->MODULE_DIR,
				"SECTIONS"=>"Y",
				"LANG"=>Array(
					"ru"=>Array(
						"NAME"=>$this->MODULE_NAME               
					)   
				)
			);
			$res = $obIBlockType->Add($arFields);
			if(!$res)
			{
				$this->ErrLog($obIBlockType->LAST_ERROR);
				return false;
			}
			
			$arrIblocksID = Array();
			$arrIblocks = Array(
			
				"CallResult" => Array(
				
					"IBlock" => Array(
						"NAME"=> Loc::getMessage("WCOMM_CALLMODIFICATIONS_IBLOCK_RESULT"),
						"ACTIVE" => "Y",
						"LIST_MODE" => "C",
					),
					
				),
				
				
			);

			$objIblock = new CIBlock;
			$objIBlockProperty = new CIBlockProperty;
			
			foreach($arrIblocks as $IBCode => $IBArr)
			{
				
				$arrIBlock = array_diff_key($IBArr["IBlock"], Array());
				
				$arrIBlock["CODE"] = $IBCode;
				$arrIBlock["IBLOCK_TYPE_ID"] = $this->MODULE_DIR;
				$arrIBlock["SITE_ID"] = $arrSiteList;
				
				$newIblockID = $objIblock->Add($arrIBlock);
				if(!$newIblockID)
				{
					$this->ErrLog($objIblock->LAST_ERROR);
					return false;
				}
				
				$arrIblocksID[$IBCode] = $newIblockID;
				
				if(count($IBArr["Properties"]) > 0)
				{
					foreach($IBArr["Properties"] as $PropArr)
					{
						$arrProperties = array_diff_key($PropArr, Array("LINK_IBLOCK_ID" => 0));
						
						$arrProperties["IBLOCK_ID"] = $newIblockID;
						
						if(array_key_exists("LINK_IBLOCK_ID", $PropArr))
						{
							$arrProperties["LINK_IBLOCK_ID"] = $arrIblocksID[$PropArr["LINK_IBLOCK_ID"]]; 
						}
						
						$newPropID = $objIBlockProperty->Add($arrProperties);
						if(!$newPropID)
						{
							$this->ErrLog($objIBlockProperty->LAST_ERROR);
							return false;
						}
					}
				}
					
			}		  
			
		}
	}
	
	function CreateHLIBlocks()
	{
				
		if (CModule::IncludeModule('iblock') && CModule::IncludeModule('highloadblock'))
		{
						
			$arrUserFieldsAll = array(
				0 => array(
					'FIELD_NAME'        => 'UF_CALL_ID',
					'USER_TYPE_ID'      => 'integer',
					'MULTIPLE'          => 'N',
					'MANDATORY'         => 'N',
					'SHOW_FILTER'       => 'N',
					'EDIT_FORM_LABEL'   => array(
						'ru'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_CALL_ID", null, 'ru'),
						'en'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_CALL_ID", null, 'en'),
					),
				),
				
				1 => array(
					'FIELD_NAME'        => 'UF_RESULT_ID',
					'USER_TYPE_ID'      => 'integer',
					'MULTIPLE'          => 'N',
					'MANDATORY'         => 'N',
					'SHOW_FILTER'       => 'N',
					'EDIT_FORM_LABEL'   => array(
						'ru'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_RESULT", null, 'ru'),
						'en'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_RESULT", null, 'en'),
					),
				),
				
				2 => array(
					'FIELD_NAME'        => 'UF_RESULT_STRING',
					'USER_TYPE_ID'      => 'string',
					'MULTIPLE'          => 'N',
					'MANDATORY'         => 'N',
					'SHOW_FILTER'       => 'N',
					'EDIT_FORM_LABEL'   => array(
						'ru'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_RESULT_COMMENT", null, 'ru'),
						'en'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_RESULT_COMMENT", null, 'en'),
					),
				),
			
			);
			
			$this->CreateHLIBlock("CallResults", $arrUserFieldsAll);
						
			$arrUserFieldsAll = array(
				0 => array(
					'FIELD_NAME'        => 'UF_OWNER_TYPE_ID',
					'USER_TYPE_ID'      => 'integer',
					'MULTIPLE'          => 'N',
					'MANDATORY'         => 'N',
					'SHOW_FILTER'       => 'N',
					'EDIT_FORM_LABEL'   => array(
						'ru'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_OWNER_TYPE_ID", null, 'ru'),
						'en'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_OWNER_TYPE_ID", null, 'en'),
					),
				),
				
				1 => array(
					'FIELD_NAME'        => 'UF_OWNER_ID',
					'USER_TYPE_ID'      => 'integer',
					'MULTIPLE'          => 'N',
					'MANDATORY'         => 'N',
					'SHOW_FILTER'       => 'N',
					'EDIT_FORM_LABEL'   => array(
						'ru'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_OWNER_ID", null, 'ru'),
						'en'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_OWNER_ID", null, 'en'),
					),
				),
				
				2 => array(
					'FIELD_NAME'        => 'UF_USER_ID',
					'USER_TYPE_ID'      => 'integer',
					'MULTIPLE'          => 'N',
					'MANDATORY'         => 'N',
					'SHOW_FILTER'       => 'N',
					'EDIT_FORM_LABEL'   => array(
						'ru'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_USER_ID", null, 'ru'),
						'en'    => Loc::getMessage("WCOMM_CALLMODIFICATIONS_HLIBLOCK_USER_ID", null, 'en'),
					),
				),
			);
			
			$this->CreateHLIBlock("CallDisplayPopup", $arrUserFieldsAll);
			
		}
		
		
	}
	
	function CreateHLIBlock($strHLIBName, $arrUserFieldsAll)
	{
		
		$strHLIBTableName = CIBlockPropertyDirectory::createHighloadTableName(strtolower($strHLIBName));
		$this->strActivityTableName = $strHLIBTableName;
			
		$result = HL\HighloadBlockTable::add(array(
			'NAME' => $strHLIBName,
			'TABLE_NAME' => $strHLIBTableName,
		));
			
		if (!$result->isSuccess()) {
			$arrErr = $result->getErrorMessages();
			foreach($arrErr as $strE)
			{
				$this->ErrLog($strE);
			}
			return false;
		}
			
		$hlibID = $result->getId();
			
		$objUserTypeEntity = new CUserTypeEntity();
		foreach($arrUserFieldsAll as $arrUserFields)
		{
			$arrUserFields['ENTITY_ID'] = 'HLBLOCK_' . $hlibID;
			$iUserFieldId = $objUserTypeEntity->Add($arrUserFields);
		}
	}
	
	function InstallFiles()
	{
		mkdir($_SERVER["DOCUMENT_ROOT"]."/local/components/bitrix", 0777 , true);
		
		CopyDirFiles( __DIR__ . "/components",
					 $_SERVER["DOCUMENT_ROOT"]."/local/components/bitrix", true, true);
					 
		CopyDirFiles( __DIR__ ."/css",
					 $_SERVER["DOCUMENT_ROOT"]."/bitrix/css/" . $this->MODULE_ID . "/", true, true);
	
		return true;
	}
	
	function UnInstallFiles()
	{
		
		\Bitrix\Main\IO\Directory::deleteDirectory(
			$_SERVER["DOCUMENT_ROOT"]."/local/components/bitrix/crm.activity.planner"
		);
		
		\Bitrix\Main\IO\Directory::deleteDirectory(
			$_SERVER["DOCUMENT_ROOT"]."/local/components/bitrix/crm.timeline"
		);
		
		
		\Bitrix\Main\IO\Directory::deleteDirectory(
			$_SERVER["DOCUMENT_ROOT"]."/local/components/bitrix/crm.activity.list"
		);
		
			
		return true;
	}
		
	function GetSiteList()
	{
		
		if(!isset($this->arrSiteList))
		{
			$rsSites = CSite::GetList($by="sort", $order="desc");
			while ($arSite = $rsSites->Fetch())
			{
				$this->arrSiteList[] = $arSite["ID"];
			}
		}
		return $this->arrSiteList;	
	}
	
	function GetListElementID($IblockID, $strCode, $strVal)
	{			
		$objIBlockPropertyEnum = CIBlockPropertyEnum::GetList(
				Array("DEF"=>"DESC", "SORT"=>"ASC"),
				Array("IBLOCK_ID" => $IblockID, "CODE" => $strCode, "VALUE" => $strVal));
		if($arrfields = $objIBlockPropertyEnum->GetNext())
		{
			return $arrfields["ID"];
		}
		return 0;
	}
	
	function ErrLog($text)
	{
		CEventLog::Add(array(
			'SEVERITY' => 'ERROR', //SECURITY, ERROR, WARNING, INFO, DEBUG
			'AUDIT_TYPE_ID' => 'INSTALLATION_ERROR',
			'MODULE_ID' => $this->MODULE_DIR,
			'DESCRIPTION' => $text));
	}	
	
}
?>
