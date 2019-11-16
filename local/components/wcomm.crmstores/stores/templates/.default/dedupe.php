<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!Loader::includeModule('wcomm.crmstores')) {
    ShowError(Loc::getMessage('CRMSTORES_NO_MODULE'));
    return;
}

$APPLICATION->SetTitle(Loc::getMessage('CRMSTORES_LIST_TITLE'));

$APPLICATION->IncludeComponent(
    'bitrix:crm.control_panel',
    '',
    array(
        'ID' => 'STORES',
        'ACTIVE_ITEM_ID' => 'STORES',
    ),
    $component
);

//echo "контроль дубликатов здесь";
$APPLICATION->IncludeComponent(
    'wcomm.crmstores:store.dedupe.list',
    '',
    array(
        'ENTITY_TYPE' => 'CONTACT' //,
        //'MYCOMPANY_MODE' => ($arResult['MYCOMPANY_MODE'] === 'Y' ? 'Y' : 'N')
    ),
    $component
);


/*$APPLICATION->IncludeComponent(
    'wcomm.crmstores:store.dedupe.list2',
    '',
    array(
        'ENTITY_TYPE' => 'CONSTRUCTION' //,
        //'MYCOMPANY_MODE' => ($arResult['MYCOMPANY_MODE'] === 'Y' ? 'Y' : 'N')
    ),
    $component
); */

