<?php
defined('B_PROLOG_INCLUDED') || die;

use Wcomm\CrmStores\Entity\StoreTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class CWcommCrmStoresStoreShowComponent extends CBitrixComponent
{
    const FORM_ID = 'CRMSTORES_SHOW';

    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        CBitrixComponent::includeComponentClass('wcomm.crmstores:stores.list');
        CBitrixComponent::includeComponentClass('wcomm.crmstores:store.edit');
    }

    public function executeComponent()
    {
        global $APPLICATION;

        $APPLICATION->SetTitle(Loc::getMessage('CRMSTORES_SHOW_TITLE_DEFAULT'));

        if (!Loader::includeModule('wcomm.crmstores')) {
            ShowError(Loc::getMessage('CRMSTORES_NO_MODULE'));
            return;
        }

        $dbStore = StoreTable::getById($this->arParams['STORE_ID']);
        $store = $dbStore->fetch();

        if (empty($store)) {
            ShowError(Loc::getMessage('CRMSTORES_STORE_NOT_FOUND'));
            return;
        }

        $APPLICATION->SetTitle(Loc::getMessage(
            'CRMSTORES_SHOW_TITLE',
            array(
                '#ID#' => $store['ID'],
                '#NAME#' => $store['NAME']
            )
        ));

        $this->arResult =array(
            'FORM_ID' => self::FORM_ID,
            'TACTILE_FORM_ID' => CWcommCrmStoresStoreEditComponent::FORM_ID,
            'GRID_ID' => CWcommCrmStoresStoresListComponent::GRID_ID,
            'STORE' => $store
        );

        $this->includeComponentTemplate();
    }
}