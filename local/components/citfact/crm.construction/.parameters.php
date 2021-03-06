<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule('crm'))
	return false;  

$arComponentParameters = Array(
	'GROUPS' => array(
	
	),
	'PARAMETERS' => array(
		'VARIABLE_ALIASES' => Array(
			'construction_id' => Array(
				'NAME' => GetMessage('CRM_CONSTRUCTION_VAR'),
				'DEFAULT' => 'cid'
			)
		),
		'SEF_MODE' => Array(
			'index' => array(
				'NAME' => GetMessage('CRM_SEF_PATH_TO_INDEX'),
				'DEFAULT' => 'index.php',
				'VARIABLES' => array()
			),
			'list' => array(
				'NAME' => GetMessage('CRM_SEF_PATH_TO_LIST'),
				'DEFAULT' => 'list/',
				'VARIABLES' => array('construction_id')
			),
			'edit' => array(
				'NAME' => GetMessage('CRM_SEF_PATH_TO_EDIT'),
				'DEFAULT' => 'edit/#construction_id#/',
				'VARIABLES' => array('construction_id')
			),
			'show' => array(
				'NAME' => GetMessage('CRM_SEF_PATH_TO_SHOW'),
				'DEFAULT' => 'show/#construction_id#/',
				'VARIABLES' => array('construction_id')
			),
			'service' => array(
				'NAME' => GetMessage('CRM_SEF_PATH_TO_SERVICE'),
				'DEFAULT' => 'service/',
				'VARIABLES' => array()
			),
			'import' => array(
				'NAME' => GetMessage('CRM_SEF_PATH_TO_IMPORT'),
				'DEFAULT' => 'import/',
				'VARIABLES' => array()			
			)							
		),		
		'ELEMENT_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CRM_ELEMENT_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '={$_REQUEST["construction_id"]}'
		),
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("CRM_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
	)
);


?>