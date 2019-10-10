<?php
namespace wcomm\callmodifications\controller;
 
use Bitrix\Main\Engine\Controller;
use local\Domain\Repository\CommCompanyRepository;
use local\Domain\Entity\CommCompany;
use local\Domain\Factory\CommCompanyFactory;
 
class ActivityAjax extends Controller
{
	
	const HLiBLOCK_CDP_NAME = "CallDisplayPopup";
	
	static $HLiBlock_Error = True;
	
	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [
			'callsexist' => [
				'prefilters' => []
			]
		];
	}
 
	/**
	 * @param string $param1
	 * @return array
	 */
	public static function callsexistAction($param1 = '0')
	{
		
		//error_log("-|".print_r($GLOBALS['USER']->GetID(),true)."|-", 3, $_SERVER["DOCUMENT_ROOT"]."/wcomm.log");
		
		
		
		$HLiBlock_CallDisplayPopupClass = self::GetHLiBlockID(self::HLiBLOCK_CDP_NAME);
		if(!self::$HLiBlock_Error)
		{
		
			$result = $HLiBlock_CallDisplayPopupClass::getList(
				array(
				"select" => array("*"),
				"order" => array("ID" => "DESC"),
				"filter" => Array("UF_USER_ID" => $GLOBALS['USER']->GetID()),
			));
			if($row = $result->fetch())
			{				
				$HLiBlock_CallDisplayPopupClass::Delete($row["ID"]);
				
				return ['show' => 'true', 'OWNER_TYPE_ID' => $row["UF_OWNER_TYPE_ID"], 'OWNER_ID' => $row["UF_OWNER_ID"]];
			}
			
		}

		
		return ['show' => 'false'];
	}
	
	/**
	 * @param string $param1
	 * @return array
	 */
	public static function archivecampaignAction($CompanyID = '0')
	{
		$CompanyID = intval($CompanyID);
				
		if(($CompanyID > 0) && \Bitrix\Main\Loader::IncludeModule("crm"))
		{
						
			$strUserFieldCode = 'UF_CRM_1568628386291';
			$strArchivalXMLID = '301400e7c947c60680079f02ffa87570';
			
			$arrUF = \CCrmCompany::GetUserFields($CompanyID);
			if(isset($arrUF[$strUserFieldCode]))
			{				
				$UFID = $arrUF[$strUserFieldCode]['ID'];
				$arrUFVar = \CUserFieldEnum::GetList(array(), array("USER_FIELD_ID" => $UFID, 'XML_ID' => $strArchivalXMLID));
				
				if($arrArchival = $arrUFVar->GetNext())
				{					
					$arFields = array(
						$strUserFieldCode => $arrArchival['ID']
					);
					
					$GLOBALS['USER_FIELD_MANAGER']->Update('CRM_COMPANY', $CompanyID, $arFields);
					return ['ok' => 'true'];
				}
			}
		}
		
		return ['ok' => 'false'];
		
	}
	
	static function GetHLiBlockID($HLiBlockName)
	{
		
		self::$HLiBlock_Error = True;
		
		$HLiBlock_EntityDataClass = null;
		
		\Bitrix\Main\Loader::IncludeModule("highloadblock");
		
		$result = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=NAME' => $HLiBlockName)));
		if($row = $result->fetch())
		{			
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($row["ID"])->fetch();
			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$HLiBlock_EntityDataClass = $entity->getDataClass();
			
			self::$HLiBlock_Error = False;
		}
		
		return $HLiBlock_EntityDataClass;
		
	}
}






