<?php

namespace local;


class CurrencyFromUser
{
	
	const OP_COMPANY_FIELD_NAME = "UF_OP_COMPANY";
	const ADMIN_TOO = true;
	const USERFIELDS_TOO = true;
	
	
	protected static $strCurrencyID = null;
	
	public static function NeedChangeCurrency()
	{
		
		return (self::ADMIN_TOO || !(array_key_exists('USER', $GLOBALS) && $GLOBALS['USER']->IsAdmin()));
		
	}
	
	public static function NeedChangeCurrencyUserFields()
	{
		return (self::NeedChangeCurrency() && self::USERFIELDS_TOO);
	}
		
	public static function GetCurrencyID()
	{
		if(self::$strCurrencyID === null)
		{
			self::$strCurrencyID = self::GetCompanyCurrency();
			
		}
		
		return self::$strCurrencyID;
		
	}
		
	protected static function GetCompanyCurrency()
	{
		
		$arrCurr = Array(
			'BY'		=>	'BYN',
			'CI'		=>	'UB',
			'HQ'		=>	'RUB',
			'KZ'		=>	'KZT',
			'RU-CNT'	=>	'RUB',
			'RU-DE'		=>	'RUB',
			'RU-DV'		=>	'RUB',
			'RU-KSC'	=>	'RUB',
			'RU-SVR'	=>	'RUB',
			'RU-URL'	=>	'RUB',
			'RU-VLG'	=>	'RUB',
			'RU-YUG'	=>	'RUB',
			'UA'		=>	'UAH',
			
			'NULL'		=>	'RUB'
		);
		
		$strCurrency = self::GetCurrentUserCompany();
		if(array_key_exists($strCurrency, $arrCurr))
		{
			return $arrCurr[$strCurrency];
		}
		
		return $arrCurr['NULL'];
		
	}
	
	protected static function GetCurrentUserCompany()
	{
		if(array_key_exists('USER', $GLOBALS) && $GLOBALS['USER']->IsAuthorized())
		{
			$rsUser = \CUser::GetList($by, $order,
				array(
					"ID" => $GLOBALS['USER']->GetID(),
				),
				array(
					"SELECT" => array(
						"UF_OP_COMPANY",
					),
				)
			);
			if($arrUser = $rsUser->Fetch())
			{
				if(array_key_exists(self::OP_COMPANY_FIELD_NAME, $arrUser))
				{
					$rsOpCompany = \CUserFieldEnum::GetList(array(), array("ID" => $arrUser[self::OP_COMPANY_FIELD_NAME], ));
					if($arrOpCompany = $rsOpCompany->GetNext())
					{
						if($arrOpCompany["XML_ID"] != "")
						{
							return $arrOpCompany["XML_ID"];
						}
					}
				}
			}
			
		}
		
		return "NULL";
		
	}
	
	
}