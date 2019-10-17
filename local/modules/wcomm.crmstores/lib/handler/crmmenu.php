<?php

namespace Wcomm\CrmStores\Handler;

use Bitrix\Main\Localization\Loc;

class CrmMenu
{
    public static function addStores(&$items)
    {
        $items[] = array(
            'ID' => 'STORES',
            'MENU_ID' => 'menu_crm_stores',
            'NAME' => Loc::getMessage('CRMSTORES_MENU_ITEM_STORES'),
            'TITLE' => Loc::getMessage('CRMSTORES_MENU_ITEM_STORES'),
            'URL' => '/crm/stores/'
        );
    }
}