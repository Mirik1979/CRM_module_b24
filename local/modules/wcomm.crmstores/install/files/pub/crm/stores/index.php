<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->IncludeComponent(
	'wcomm.crmstores:stores',
	'', 
	array(
		'SEF_MODE' => 'Y',
		'SEF_FOLDER' => '/crm/stores/',
		'SEF_URL_TEMPLATES' => array(
			'details' => '#STORE_ID#/',
			'edit' => '#STORE_ID#/edit/',
		)
	),
	false
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');