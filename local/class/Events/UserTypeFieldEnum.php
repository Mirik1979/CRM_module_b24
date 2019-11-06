<?php

namespace local\Events;


class UserTypeFieldEnum extends \CUserTypeEnum
{
    function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => "UserTypeFieldEnum",
            "CLASS_NAME" => self::class,
            "DESCRIPTION" => "Привязка к элементам списка пользовательского поля",
            "BASE_TYPE" => "int",
        );
    }

    function PrepareSettings($arUserField)
    {
        $by = "FIELD_NAME";
        $order = "ASC";
        $rsData = \CUserTypeEntity::GetList([$by=>$order],[
            "USER_TYPE_ID" => "enumeration",
        ]);
        $arEntityTypes=[];
        while($arRes = $rsData->Fetch())
            $arEntityTypes[$arRes['ID']] = $arRes;
        $entityType = $arUserField['SETTINGS']['ENTITY_TYPE'];
        if(is_array($entityType))
        {
            $entityType = isset($entityType['ID']) ? $entityType['ID'] : '';
        }
        $disp = $arUserField["SETTINGS"]["DISPLAY"];
        if($disp!="CHECKBOX" && $disp!="LIST")
            $disp = "LIST";
        return array(
            "DISPLAY" => $disp,
            'ENTITY_TYPE' =>  (isset($arEntityTypes[$entityType])? $entityType: array_shift($arEntityTypes)),
        );
    }

    function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        $result = '';

        if($bVarsFromForm)
            $value = htmlspecialcharsbx($GLOBALS[$arHtmlControl['NAME']]['ENTITY_TYPE']);
        elseif(is_array($arUserField))
            $value = htmlspecialcharsbx($arUserField['SETTINGS']['ENTITY_TYPE']);
        else
            $value = '';

        $by = "FIELD_NAME";
        $order = "ASC";
        $rsData = \CUserTypeEntity::GetList([$by=>$order],[
            "USER_TYPE_ID" => "enumeration",
            ]);
        while($arRes = $rsData->Fetch())
        {
            $arr['reference'][] = $arRes['FIELD_NAME'].'['.$arRes['ID'].']';
            $arr['reference_id'][] = $arRes['ID'];
        }

        $result .= '
		<tr>
			<td>'.'Доступная сущность'.':</td>
			<td>
				'.SelectBoxFromArray($arHtmlControl["NAME"].'[ENTITY_TYPE]', $arr, $value).'
			</td>
		</tr>
		';

        if($bVarsFromForm)
            $value = $GLOBALS[$arHtmlControl["NAME"]]["DISPLAY"];
        elseif(is_array($arUserField))
            $value = $arUserField["SETTINGS"]["DISPLAY"];
        else
            $value = "LIST";
        $result .= '
		<tr>
			<td class="adm-detail-valign-top">'.GetMessage("USER_TYPE_ENUM_DISPLAY").':</td>
			<td>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="LIST" '.("LIST"==$value? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_IBSEC_LIST").'</label><br>
				<label><input type="radio" name="'.$arHtmlControl["NAME"].'[DISPLAY]" value="CHECKBOX" '.("CHECKBOX"==$value? 'checked="checked"': '').'>'.GetMessage("USER_TYPE_IBSEC_CHECKBOX").'</label><br>
			</td>
		</tr>
		';

        return $result;
    }

    function GetList($arUserField)
    {
        $obSection = new UserTypeFieldEnumEnum;
        $rsSection = $obSection->GetList($arUserField["SETTINGS"]["ENTITY_TYPE"]);
        return $rsSection;
    }

}