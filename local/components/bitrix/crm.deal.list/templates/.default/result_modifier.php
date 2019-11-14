<?php
use Bitrix\Main\Loader;
use Wcomm\CrmStores\Entity\StoreTable;

$arResult['HEADERS']['UF_STORE']['name'] = "Объекты";

$newdeal = $arResult['DEAL'];

foreach ($newdeal as $key => $dealval) {
    if ($arResult['DEAL'][$key]['UF_STORE']) {
        $viewstore = '/crm/stores/details/'.$arResult['DEAL'][$key]['UF_STORE'].'/';
        Loader::includeModule('wcomm.crmstores');
        $addid = Wcomm\CrmStores\Entity\StoreTable::getbyId($arResult['DEAL'][$key]['UF_STORE']);
        $newadd = $addid->fetchAll();
        //$items[$itemID]['STORE'] = $newadd[0]['NAME'];
        $arResult['DEAL'][$key]['UF_STORE']  = '<a href="' . $viewstore . '" target="_self">' . $newadd[0]['NAME'] . '</a>';
    }
}

