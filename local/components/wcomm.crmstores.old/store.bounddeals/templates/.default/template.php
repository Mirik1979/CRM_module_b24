<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

global $APPLICATION;

/** @var CBitrixComponentTemplate $this */

if (!Loader::includeModule('currency')) {
    ShowError(Loc::getMessage('CRMSTORES_NO_CURRENCY_MODULE'));
    return;
}

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'disable-grid-mode');

$rows = array();
foreach ($arResult['DEALS'] as $deal) {

    $dealUrl = CComponentEngine::makePathFromTemplate(
        Option::get('wcomm.crmstores', 'DEAL_DETAIL_TEMPLATE'),
        array('DEAL_ID' => $deal['ID'])
    );

    $rows[] = array(
        'id' => $deal['ID'],
        'columns' => array(
            'ID' => $deal['ID'],
            'TITLE' => '<a href="' . htmlspecialcharsbx($dealUrl) . '">' . $deal['TITLE'] . '</a>',
            'OPPORTUNITY' => CurrencyFormat($deal['OPPORTUNITY'], $deal['CURRENCY_ID'])
        )
    );
}

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    array(
        'GRID_ID' => 'CRMSTORES_BOUND_DEALS',
        'HEADERS' => array(
            array(
                'id' => 'ID',
                'name' => Loc::getMessage('CRMSTORES_DEAL_ID'),
                'type' => 'int',
                'default' => false,
            ),
            array(
                'id' => 'TITLE',
                'name' => Loc::getMessage('CRMSTORES_DEAL_TITLE'),
                'default' => true
            ),
            array(
                'id' => 'OPPORTUNITY',
                'name' => Loc::getMessage('CRMSTORES_DEAL_OPPORTUNITY'),
                'default' => true,
            )
        ),
        'ROWS' => $rows
    ),
    $this->getComponent(),
    array('HIDE_ICONS' => 'Y')
);