<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $USER;
$ext = 0;
if ($USER->isAdmin()==false) {
    $arResult["REST_USE"] = false;
    if ($arResult['ENTITY_TYPE_ID']==2) {
        unset($arResult["TABS"][3]);
        unset($arResult["TABS"][4]);
        unset($arResult["TABS"][5]);
    } elseif ($arResult['ENTITY_TYPE_ID']==3) {
        unset($arResult["TABS"][0]);
        unset($arResult["TABS"][1]);
        unset($arResult["TABS"][2]);
        unset($arResult["TABS"][3]);
        unset($arResult["TABS"][4]);
        unset($arResult["TABS"][6]);

    } elseif ($arResult['ENTITY_TYPE_ID']==4) {
        unset($arResult["TABS"][3]);
        unset($arResult["TABS"][4]);
        unset($arResult["TABS"][6]);
    }
}


