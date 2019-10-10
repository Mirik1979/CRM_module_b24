<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('CRM_ACTIVITY_COMPLEX_NAME'),
	'DESCRIPTION' => GetMessage('CRM_ACTIVITY_COMPLEX_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'SORT' => 10,
	'COMPLEX' => 'Y',
	'PATH' => array(
		'ID' => 'crm',
		'NAME' => GetMessage('CRM_NAME'),
		'CHILD' => array(
			'ID' => 'activity',
			'NAME' => GetMessage('CRM_ACTIVITY_NAME')
		)
	),
	'CACHE_PATH' => 'Y'	
);
?>