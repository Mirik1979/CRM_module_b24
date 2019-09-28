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






