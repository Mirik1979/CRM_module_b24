<?php

namespace Wcomm\CrmStores\UserType;


use Wcomm\CrmStores\Entity\StoreTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\TypeBase;

class StoreBinding extends TypeBase
{
    const USER_TYPE_ID = 'storebinding';

    function GetUserTypeDescription ()
    {
        return array(
            'USER_TYPE_ID' => static::USER_TYPE_ID,
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => Loc::getMessage('CRMSTORES_STOREBINDING'),
            'BASE_TYPE' => \CUserTypeManager::BASE_TYPE_INT,
            'EDIT_CALLBACK' => array(__CLASS__, 'GetPublicEdit'),
            'VIEW_CALLBACK' => array(__CLASS__, 'GetPublicView')
        );
    }

    function GetDBColumnType ($arUserField)
    {
        global $DB;
        switch(strtolower($DB->type))
        {
            case "mysql":
                return "int(18)";
            case "oracle":
                return "number(18)";
            case "mssql":
                return "int";
        }
        return "int";
    }

    function GetFilterHTML($arUserField, $arHtmlControl)
    {
        return sprintf(
            '<input type="text" name="%s" size="%s" value="%s">',
            $arHtmlControl['NAME'],
            $arUserField['SETTINGS']['SIZE'],
            $arHtmlControl['VALUE']
        );
    }

    function GetFilterData($arUserField, $arHtmlControl)
    {
        return array(
            'id' => $arHtmlControl['ID'],
            'name' => $arHtmlControl['NAME'],
            'filterable' => ''
        );
    }

    function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        return !empty($arHtmlControl['VALUE']) ? self::getStoreLink($arHtmlControl['VALUE']) : '&nbsp;';
    }

    function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        return self::getStoreSelector($arHtmlControl["NAME"], $arHtmlControl["VALUE"]);
    }

    function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        return self::getStoreSelector($arHtmlControl["NAME"], $arHtmlControl["VALUE"]);
    }

    public static function GetPublicView($arUserField, $arAdditionalParameters = array())
    {
        return !empty($arUserField['VALUE']) ? self::getStoreLink($arUserField['VALUE']) : '&nbsp;';
    }

    public static function GetPublicEdit($arUserField, $arAdditionalParameters = array())
    {
        $fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
        $value = static::getFieldValue($arUserField, $arAdditionalParameters);
        $value = reset($value);

        return self::getStoreSelector($fieldName, $value);
    }

    function OnSearchIndex($arUserField)
    {
        if(is_array($arUserField["VALUE"]))
            return implode("\r\n", $arUserField["VALUE"]);
        else
            return $arUserField["VALUE"];
    }

    private static function getStoreSelector($fieldName, $fieldValue = null)
    {
        if (!Loader::includeModule('wcomm.crmstores')) {
            return '';
        }

        $dbStores = StoreTable::getList(array('select' => array('ID', 'NAME')));
        $stores = $dbStores->fetchAll();

        $isNoValue = $fieldValue === null;

        ob_start();
        ?>
        <select name="<?= $fieldName ?>">
            <option value="" <?= $isNoValue ? 'selected' : '' ?>>
                <?= Loc::getMessage('CRMSTORES_NO_BINDING') ?>
            </option>
            <? foreach ($stores as $store): ?>
                <?
                $selected = $store['ID'] == $fieldValue ? 'selected' : '';
                ?>
                <option value="<?= $store['ID'] ?>" <?= $selected ?>>
                    <?= htmlspecialcharsbx($store['NAME']) ?>
                </option>
            <? endforeach; ?>
        </select>
        <?
        $selectorHtml = ob_get_clean();

        return $selectorHtml;
    }

    private static function getStoreLink($storeId)
    {
        if (!Loader::includeModule('wcomm.crmstores')) {
            return '';
        }

        $dbStore = StoreTable::getById($storeId);
        $store = $dbStore->fetch();

        if (empty($store)) {
            return '';
        }

        $storeDetailTemplate = Option::get('wcomm.crmstores', 'STORE_DETAIL_TEMPLATE');
        $storeUrl = \CComponentEngine::makePathFromTemplate(
            $storeDetailTemplate,
            array('STORE_ID' => $store['ID'])
        );

        return '<a href="' . htmlspecialcharsbx($storeUrl) . '">' . htmlspecialcharsbx($store['NAME']) . '</a>';
    }
}