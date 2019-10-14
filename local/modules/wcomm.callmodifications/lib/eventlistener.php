<?

namespace wcomm\callmodifications;

//error_log(print_r($arrDeal,true), 3, $_SERVER["DOCUMENT_ROOT"]."/wcomm.log");

class EventListener 
{
	
	const HLiBLOCK_CL_NAME = "CallResults";
	const HLiBLOCK_CDP_NAME = "CallDisplayPopup";
	
	static $HLiBlock_Error = True;
	static $HLiBlock_CallResultsClass = null;
	
	static $HLiBlock_CallDisplayPopupClass = null;
	
	static function OnActivityAddV2($id, $arrParam)
    {
		
				
		self::AddOrUpdateCallResults($id, $arrParam);
		
    }
	
	static function OnActivityUpdateV2($id, $arrParam)
	{
				
		self::AddOrUpdateCallResults($id, $arrParam);
		
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
	
	static function AddOrUpdateCallResults($id, $arrParam)
	{

		self::$HLiBlock_CallResultsClass = self::GetHLiBlockID(self::HLiBLOCK_CL_NAME);
		if(!self::$HLiBlock_Error)
		{
		
			$result = self::$HLiBlock_CallResultsClass::getList(
				array(
				"select" => array("*"),
				"order" => array("ID" => "DESC"),
				"filter" => Array("UF_CALL_ID" => $id),
			));
			if($row = $result->fetch())
			{
				
				
				$arFields = array (
					"UF_RESULT_ID" => $arrParam["CALL_FIELDS_DIR"],
					"UF_RESULT_STRING" => $arrParam["CALL_FIELDS_STR"],
				);
								
				$result = self::$HLiBlock_CallResultsClass::update($row["ID"],$arFields);
				
				if($result->isSuccess())
				{					
					self::UpdateCallsExists($arrParam);
				}

			} else {
				
				$arFields = array (
					"UF_CALL_ID" => $id,
					"UF_RESULT_ID" => $arrParam["CALL_FIELDS_DIR"],
					"UF_RESULT_STRING" => $arrParam["CALL_FIELDS_STR"],
				);
								
				$result = self::$HLiBlock_CallResultsClass::add($arFields);
				if($result->isSuccess()) 
				{                    
					//$id2 = $result->getId();
										
					self::UpdateCallsExists($arrParam);
				}
				
				
			}
			
		}
		
	}
	
	static function UpdateCallsExists($arrParam)
	{
				
		if(!\Bitrix\Main\Loader::IncludeModule("crm"))
		{

			return false;
		}
		
		if(!(isset($arrParam["OWNER_ID"]) || isset($arrParam["OWNER_TYPE_ID"])|| isset($arrParam["BINDINGS"])))
		{
			return false;
		}
		
		$CompanyID = null;
		if($arrParam["OWNER_TYPE_ID"] == \CCrmOwnerType::Company)
		{
			$CompanyID = $arrParam["OWNER_ID"];
		} 
		elseif($arrParam["OWNER_TYPE_ID"] == \CCrmOwnerType::Deal)
		{
						
			$objDeal = \CCrmDeal::GetById($arrParam["OWNER_ID"], false);
            if ($objDeal && isset($objDeal['COMPANY_ID']))
			{
				$CompanyID = $objDeal['COMPANY_ID'];
			}
		}
		elseif($arrParam["OWNER_TYPE_ID"] == \CCrmOwnerType::Contact)
		{
						
			$objContact = \CCrmContact::GetById($arrParam["OWNER_ID"], false);
            if ($objContact && isset($objContact['COMPANY_ID']))
			{
				$CompanyID = $objContact['COMPANY_ID'];
			}
		}
		
		if($CompanyID == null)
		{
			return false;
		}
		
		$CallsCount = self::GetNotCompletedCallsAndMeetingsCount(\CCrmOwnerType::Company, $CompanyID);
				
		$arFilter = [
			'COMPANY_ID' => $CompanyID
		];
		
		$dbResult = \CCrmDeal::GetList(array(), $arFilter);
		while($arrDeal = $dbResult->GetNext())
		{
					
			$CallsCount += self::GetNotCompletedCallsAndMeetingsCount(\CCrmOwnerType::Deal, $arrDeal['ID']);
						
		}
		
		$dbResult = \CCrmContact::GetList(array(), $arFilter);
		while($arrContact = $dbResult->GetNext())
		{
					
			$CallsCount += self::GetNotCompletedCallsAndMeetingsCount(\CCrmOwnerType::Contact, $arrContact['ID']);
						
		}
		
		//error_log(print_r($CallsCount,true)."|", 3, $_SERVER["DOCUMENT_ROOT"]."/wcomm.log");
		
		//if(true || ($CallsCount < 1))
		if($CallsCount < 1)
		{
			
			self::AddOrUpdateCallDisplayPopup($GLOBALS['USER']->GetID(), $arrParam["OWNER_TYPE_ID"], $arrParam["OWNER_ID"], $CompanyID);
			
			return true;
		}
		
		return false;
		
	}
	
	static function GetNotCompletedCallsAndMeetingsCount($OwnerTypeID, $OwnerID)
	{
				
		$arFilter = [
			'TYPE_ID' => null,
			'OWNER_TYPE_ID' => $OwnerTypeID,
			'OWNER_ID' => $OwnerID,
			'COMPLETED' => 'N',
			'TYPE_ID' => \CCrmActivityType::Call
		];
			
		$dbResult = \CCrmActivity::GetList(
				array(),
				$arFilter,
				false,
				false,
				array('ID')
		);
		$CountC = $dbResult->SelectedRowsCount();
		
		$arFilter = [
			'TYPE_ID' => null,
			'OWNER_TYPE_ID' => $OwnerTypeID,
			'OWNER_ID' => $OwnerID,
			'COMPLETED' => 'N',
			'TYPE_ID' => \CCrmActivityType::Meeting
		];
			
		$dbResult = \CCrmActivity::GetList(
				array(),
				$arFilter,
				false,
				false,
				array('ID')
		);
		$CountM = $dbResult->SelectedRowsCount();
		
		return ($CountC + $CountM);
	}
	
	static function AddOrUpdateCallDisplayPopup($UserID, $OwnerTypeID, $OwnerID, $CompanyID)
	{

		self::$HLiBlock_CallDisplayPopupClass = self::GetHLiBlockID(self::HLiBLOCK_CDP_NAME);
		if(!self::$HLiBlock_Error)
		{
		
			$result = self::$HLiBlock_CallDisplayPopupClass::getList(
				array(
				"select" => array("*"),
				"order" => array("ID" => "DESC"),
				"filter" => Array("UF_USER_ID" => $UserID),
			));
			if($row = $result->fetch())
			{
								
				$arFields = array (
					"UF_OWNER_TYPE_ID" => $OwnerTypeID,
					"UF_OWNER_ID" => $OwnerID,
					"UF_USER_ID" => $UserID,
					"UF_COMPANY_ID" => $CompanyID
				);
								
				$result = self::$HLiBlock_CallDisplayPopupClass::update($row["ID"],$arFields);
				
				if($result->isSuccess())
				{					
					return true;
				}

			} else {
				
				$arFields = array (
					"UF_OWNER_TYPE_ID" => $OwnerTypeID,
					"UF_OWNER_ID" => $OwnerID,
					"UF_USER_ID" => $UserID,
					"UF_COMPANY_ID" => $CompanyID
				);
								
				$result = self::$HLiBlock_CallDisplayPopupClass::add($arFields);
				if($result->isSuccess()) 
				{                    
					//$id2 = $result->getId();
										
					return true;
				}
				
				
			}
			
		}
		
		return false;
		
	}
	
}









