<?php

namespace local\Events;

class UserTypeOK extends \CUserTypeIBlockSection
{
    function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => "CUserTypeIBlockSection",
            "CLASS_NAME" => self::class,
            "DESCRIPTION" => "Привязка к ОК",
            "BASE_TYPE" => "int",
            "VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
            "EDIT_CALLBACK" => array(__CLASS__, 'GetPublicEdit'),
        );
    }

    function GetList($arUserField)
    {
        $rsSection = false;
        if(\CModule::IncludeModule('iblock'))
        {
            $obSection = new UserTypeOKEnum;
            $rsSection = $obSection->GetTreeList($arUserField["SETTINGS"]["IBLOCK_ID"], $arUserField["SETTINGS"]["ACTIVE_FILTER"]);
        }
        return $rsSection;
    }

    protected static function getEnumList(&$arUserField, $arParams = array())
    {
        if(!\CModule::IncludeModule('iblock'))
        {
            return;
        }

        $obSection = new UserTypeOKEnum;
        $rsSection = $obSection->GetTreeList($arUserField["SETTINGS"]["IBLOCK_ID"], $arUserField["SETTINGS"]["ACTIVE_FILTER"]);
        if(!is_object($rsSection))
        {
            return;
        }

        $result = array();
        $showNoValue = $arUserField["MANDATORY"] != "Y"
            || $arUserField['SETTINGS']['SHOW_NO_VALUE'] != 'N'
            || (isset($arParams["SHOW_NO_VALUE"]) && $arParams["SHOW_NO_VALUE"] == true);

        if($showNoValue
            && ($arUserField["SETTINGS"]["DISPLAY"] != "CHECKBOX" || $arUserField["MULTIPLE"] <> "Y")
        )
        {
            $result = array(null => htmlspecialcharsbx(static::getEmptyCaption($arUserField)));
        }

        while($arSection = $rsSection->Fetch())
        {
            $result[$arSection["ID"]] = $arSection["NAME"];
        }
        $arUserField["USER_TYPE"]["FIELDS"] = $result;
    }


}