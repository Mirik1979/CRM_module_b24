<?php

use Wcomm\CrmStores\Entity\StoreTable;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

defined('B_PROLOG_INCLUDED') || die;

class CWcommCrmStoresStoreBoundDealsComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!Loader::includeModule('crm')) {
            ShowError(Loc::getMessage('CRMSTORES_NO_CRM_MODULE'));
            return;
        }

        if (!Loader::includeModule('wcomm.crmstores')) {
            ShowError(Loc::getMessage('CRMSTORES_NO_MODULE'));
            return;
        }

        if (intval($this->arParams['STORE_ID']) <= 0) {
            ShowError(Loc::getMessage('CRMSTORES_STORE_NOT_FOUND'));
            return;
        }

        $dbStore = StoreTable::getById($this->arParams['STORE_ID']);
        $store = $dbStore->fetch();

        if (empty($store)) {
            ShowError(Loc::getMessage('CRMSTORES_STORE_NOT_FOUND'));
            return;
        }

        $dealUfName = Option::get('wcomm.crmstores', 'DEAL_UF_NAME');

        $dbDeals = DealTable::getList(array(
            'filter' => array(
                $dealUfName => $store['ID']
            )
        ));
        $deals = $dbDeals->fetchAll();

        $this->arResult = array(
            'STORE' => $store,
            'DEALS' => $deals,
        );

        $this->includeComponentTemplate();
    }
}