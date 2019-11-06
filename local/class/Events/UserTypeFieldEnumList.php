<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 04.11.2019
 * Time: 12:07
 */

namespace local\Events;


class UserTypeFieldEnumList extends \CUserTypeEnum
{
    function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => "UserTypeFieldEnumList",
            "CLASS_NAME" => self::class,
            "DESCRIPTION" => "Список числовых параметров Объектов",
            "BASE_TYPE" => "int",
        );
    }

    function PrepareSettings($arUserField)
    {
        $disp = $arUserField["SETTINGS"]["DISPLAY"];
        if($disp!="CHECKBOX" && $disp!="LIST")
            $disp = "LIST";
        return array(
            "DISPLAY" => $disp,
        );
    }

    function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        $result = '';

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
        $obSection = new UserTypeFieldEnumListEnum;
        $rsSection = $obSection->GetList();
        return $rsSection;
    }
}