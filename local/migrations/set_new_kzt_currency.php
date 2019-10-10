<?php

use Bitrix\Main\Loader;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

if (Loader::includeModule('currency')) {
    CCurrency::Add([
        'CURRENCY' => 'KZT',
        'NUMCODE' => 398,
        'AMOUNT' => 5.94,
        'AMOUNT_CNT' => 1,
    ]);
    
    CCurrencyLang::Add([
        'FORMAT_STRING' => '# Тенге',
        'FULL_NAME' => 'Тенге',
        'DEC_POINT' => '.',
        'THOUSANDS_SEP' => '\xA0',
        'DECIMALS' => 2,
        'CURRENCY' => 'KZT',
        'LID' => 'ru'
    ]);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
