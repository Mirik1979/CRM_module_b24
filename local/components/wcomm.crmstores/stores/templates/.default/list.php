<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

if (!Loader::includeModule('wcomm.crmstores')) {
    ShowError(Loc::getMessage('CRMSTORES_NO_MODULE'));
    return;
}

/** @var CBitrixComponentTemplate $this */

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

$urlTemplates = array(
    'DETAIL' => $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES']['details'],
    'EDIT' => $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES']['edit'],
);

/*$APPLICATION->IncludeComponent(
    "wcomm.crmstores:stores.test",
    ".default",
    Array(
    ),
    false
); */


/*$APPLICATION->IncludeComponent(
    'bitrix:crm.company.menu',
    '',
    array(
        'PATH_TO_COMPANY_LIST' => $arResult['PATH_TO_COMPANY_LIST'],
        'PATH_TO_COMPANY_SHOW' => $arResult['PATH_TO_COMPANY_SHOW'],
        'PATH_TO_COMPANY_EDIT' => $arResult['PATH_TO_COMPANY_EDIT'],
        'PATH_TO_COMPANY_IMPORT' => $arResult['PATH_TO_COMPANY_IMPORT'],
        'PATH_TO_COMPANY_DEDUPE' => $arResult['PATH_TO_COMPANY_DEDUPE'],
        'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
        'ELEMENT_ID' => $arResult['VARIABLES']['company_id'],
        'TYPE' => 'list',
        'MYCOMPANY_MODE' => ($arResult['MYCOMPANY_MODE'] === 'Y' ? 'Y' : 'N')
    ),
    $component
); */

$APPLICATION->IncludeComponent(
    'bitrix:crm.interface.toolbar',
    'title',
    array(
        'TOOLBAR_ID' => 'toolbar_crm_stores',
        'BUTTONS' => array(
            array(
                'TEXT' => Loc::getMessage('CRMSTORES_ADD'),
                'TITLE' => Loc::getMessage('CRMSTORES_ADD'),
                'LINK' => CComponentEngine::makePathFromTemplate($urlTemplates['EDIT'], array('STORE_ID' => 0)),
                'ICON' => 'btn-add',
            ),
            array(
                'NEWBAR' => true
            ),
            array(
                'TEXT' => Loc::getMessage('CRMSTORES_IMPORT'),
                'TITLE' => Loc::getMessage('CRMSTORES_IMPORT'),
                'LINK' => '/crm/company/import/',
                'ICON' => 'btn-import'
            ),
            array(
                'TEXT' => Loc::getMessage('CRMSTORES_EXPORT'),
                'TITLE' => Loc::getMessage('CRMSTORES_EXPORT'),
                'ONCLICK' => 'BX.Crm.ExportManager.items[\'STEXPORT_COMPANY_MANAGER_MEg2VEg38A\'].startExport(\'excel\')',
                'ICON' => 'btn-import'
            ),
            array(
                'TEXT' => Loc::getMessage('CRMSTORES_DEDUPE'),
                'TITLE' => Loc::getMessage('CRMSTORES_DEDUPE'),
                'LINK' => '/crm/company/dedupe/'
            )
        )
    ),
    $this->getComponent(),
    array('HIDE_ICONS' => 'Y')
);

$APPLICATION->IncludeComponent(
    'wcomm.crmstores:stores.list',
    '',
    array(
        'URL_TEMPLATES' => $urlTemplates,
        'SEF_FOLDER' => $arResult['SEF_FOLDER'],
    ),
    $this->getComponent(),
    array('HIDE_ICONS' => 'Y',)
);