<?php

//Подключаем autoload
require_once($_SERVER['DOCUMENT_ROOT'].'/local/vendor/autoload.php');

//Выносим вызов событий в одно меесто
//local\Helpers\SetEvents::init();
local\Helpers\SetConst::init();

//wcomm {
	

require_once dirname(__DIR__) ."/standard_classes_replacement/StandardClassesReplacementAutoloader.php";
StandardClassesReplacementAutoloader::RegisterAutoloadFunction();

	
if(!(isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y"))
{
	
	if(LANGUAGE_ID == "ru")
	{
		Bitrix\Main\Page\Asset::getInstance()->addJs('/local/js/ru.wcomm.js');
	} else {
		Bitrix\Main\Page\Asset::getInstance()->addJs('/local/js/en.wcomm.js');
	}
	
	Bitrix\Main\Page\Asset::getInstance()->addJs('/local/js/wcomm.js');
	
}

// } wcomm

