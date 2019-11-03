<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

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

//echo "<pre>";
//print_r($arResult);
//echo "</pre>";

$APPLICATION->IncludeComponent(
    'wcomm.crmstores:store.import',
    '',
    array(
        'PATH_TO_COMPANY_LIST' => $arResult['PATH_TO_STORE_LIST'],
        'PATH_TO_COMPANY_IMPORT' => $arResult['PATH_TO_STORE_IMPORT']
    ),
    $component
);?>
