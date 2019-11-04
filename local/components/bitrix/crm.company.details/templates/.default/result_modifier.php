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

$arResult['ENTITY_DATA']['CLIENT_INFO']['CONTACT_DATA'] = $arr;

//$arResult['ENTITY_INFO']['TITLE'] = "";
//$arResult['ENTITY_FIELDS'][1]['isHeading'] = "";
//$arResult['ENTITY_DATA']['IS_MY_COMPANY'] = true;

