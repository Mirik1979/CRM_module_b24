<?php

use Bitrix\Crm\ContactTable;

$archiveContacts = array_column(ContactTable::getList([
    'filter' => ['UF_CRM_1566294330350' => true, 'COMPANY_ID' => $arResult['ENTITY_DATA']['ID']],
    'select' => ['ID'],
    'cache' => ['ttl' => 3600]
])->fetchAll(), 'ID');

$arr = [];

foreach ($arResult['ENTITY_DATA']['CLIENT_INFO']['CONTACT_DATA'] as $index => $item) {
    if ('CONTACT' === $item['typeName'] && !in_array($item['id'], $archiveContacts)) {
        $arr[] = $item;
    }
}

$arResult['PATH_TO_COMPANY_SHOW'] = "";
$arResult['PATH_TO_COMPANY_EDIT'] = "";

$arResult['ENTITY_DATA']['CLIENT_INFO']['CONTACT_DATA'] = $arr;


