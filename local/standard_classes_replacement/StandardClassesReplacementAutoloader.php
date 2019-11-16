<?php



class StandardClassesReplacementAutoloader
{
	
	static $arrReplaceableClasses = null; 
	
	public static function RegisterAutoloadFunction()
	{
		spl_autoload_register(array(__class__, 'SCRAutoloader'),false,true);
	}
	
	public static function PrepareReplaceableClassesArray()
	{
		if(self::$arrReplaceableClasses === null)
		{
			self::$arrReplaceableClasses = Array(
				strtolower("CCrmCurrencyHelper") => "/modules/crm/classes/general/crm_currency_helper.php",
                //strtolower("CCrmFields") => "/modules/crm/classes/general/crm_fields.php",
                strtolower("Bitrix\Crm\Integrity\Duplicate") => "/modules/crm/lib/integrity/duplicate.php",
                strtolower("Bitrix\Crm\Integrity\DuplicateList") => "/modules/crm/lib/integrity/duplicatelist.php",
				strtolower("CCrmCurrency") => "/modules/crm/classes/general/crm_currency.php",
				strtolower("Bitrix\Currency\Helpers\Editor") => "/modules/currency/lib/helpers/editor.php",
				strtolower("Bitrix\Currency\UserField\Money") => "/modules/currency/lib/userfield/money.php",
				
				strtolower("DealSearchContentBuilder") => "/modules/crm/lib/search/dealsearchcontentbuilder.php",				
				strtolower("InvoiceSearchContentBuilder") => "/modules/crm/lib/search/invoicesearchcontentbuilder.php",
				strtolower("LeadSearchContentBuilder") => "/modules/crm/lib/search/leadsearchcontentbuilder.php",
				strtolower("QuoteSearchContentBuilder") => "/modules/crm/lib/search/quotesearchcontentbuilder.php",
				strtolower("CCrmTemplateMapperBase") => "/modules/crm/classes/general/template_mapper.php",
				strtolower("CCrmTemplateMapper") => "/modules/crm/classes/general/template_mapper.php",

                strtolower("Bitrix\Crm\Controller\Action\Entity\SearchAction") => "/modules/crm/lib/controller/action/entity/searchaction.php",
			);
		}
				
	}
		
	public static function SCRAutoloader($strClassName)
	{
		self::PrepareReplaceableClassesArray();
		
		$strClassNameLC = strtolower($strClassName);
				
		foreach(self::$arrReplaceableClasses as $strCurrClassName => $strCFileName )
		{
			if(substr($strClassNameLC, -1 * strlen ($strCurrClassName)) === $strCurrClassName)
			{
				$strClassFileName = __DIR__ . $strCFileName;
			
				//error_log("-=(StandardClassesReplacementAutoloader[".print_r($strClassFileName,true)."])=-".PHP_EOL, 3, $_SERVER["DOCUMENT_ROOT"]."/wcomm.log");
				
				if (file_exists($strClassFileName))
				{
					//error_log("-=(StandardClassesReplacementAutoloader[1])=-".PHP_EOL, 3, $_SERVER["DOCUMENT_ROOT"]."/wcomm.log");
					
					require_once $strClassFileName;
					
					//error_log("-=(StandardClassesReplacementAutoloader[2])=-".PHP_EOL, 3, $_SERVER["DOCUMENT_ROOT"]."/wcomm.log");
				}
			}
		}
		
	}
	
}