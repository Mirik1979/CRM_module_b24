<?php
defined('B_PROLOG_INCLUDED') || die;

use WComm\CrmStores\BizProc\StoreDocument;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

Loader::includeModule('wcomm.crmstores');

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
    'BP_EDIT' => $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES']['bizproc_workflow_edit'],
    'BP_LIST' => $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES']['bizproc_workflow_admin']
);

$APPLICATION->IncludeComponent(
    'bitrix:bizproc.workflow.edit',
    '',
    array(
        'MODULE_ID' => 'wcomm.crmstores',
        'ENTITY' => StoreDocument::class,
        'DOCUMENT_TYPE' => 'store',
        'ID' => (int)$arResult['VARIABLES']['ID'],
        'EDIT_PAGE_TEMPLATE' => $urlTemplates['BP_EDIT'],
        'LIST_PAGE_URL' => $urlTemplates['BP_LIST'],
        'SHOW_TOOLBAR' => 'Y',
        'SET_TITLE' => 'Y',
    )
);
