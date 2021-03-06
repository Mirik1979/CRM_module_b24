<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;
use Bitrix\Crm;
use Bitrix\Main;


/** @var CBitrixComponentTemplate $this */

if(!Main\Loader::includeModule('crm'))
{
    ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
    return;
}


/*$APPLICATION->IncludeComponent(
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

$editUrl = CComponentEngine::makePathFromTemplate(
    $urlTemplates['EDIT'],
    array('STORE_ID' => $arResult['VARIABLES']['STORE_ID'])
);

$viewUrl = CComponentEngine::makePathFromTemplate(
    $urlTemplates['DETAIL'],
    array('STORE_ID' => $arResult['VARIABLES']['STORE_ID'])
);

$editUrl = new Uri($editUrl);
$editUrl->addParams(array('backurl' => $viewUrl));

/*$APPLICATION->IncludeComponent(
    'bitrix:crm.interface.toolbar',
    'type2',
    array(
        'TOOLBAR_ID' => 'CRMSTORES_TOOLBAR',
        'BUTTONS' => array(
            array(
                'TEXT' => Loc::getMessage('CRMSTORES_EDIT'),
                'TITLE' => Loc::getMessage('CRMSTORES_EDIT'),
                'LINK' => $editUrl->getUri(),
                'ICON' => 'btn-edit',
            ),
        )
    ),
    $this->getComponent(),
    array('HIDE_ICONS' => 'Y')
);
// старая карточка
$APPLICATION->IncludeComponent(
    'wcomm.crmstores:store.show',
    '',
    array(
        'STORE_ID' => $arResult['VARIABLES']['STORE_ID']
    ),
    $this->getComponent(),
    array('HIDE_ICONS' => 'Y',)
); */

// подключение новой карточкиыыыыы
$APPLICATION->IncludeComponent(
    'bitrix:crm.entity.details.frame',
    '',
    array(
        'ENTITY_TYPE_ID' => 'CRM_STORES',
        'ENTITY_ID' => $arResult['VARIABLES']['STORE_ID'],
        'ENABLE_TITLE_EDIT' => true
    )
);