<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

if(!$USER->IsAdmin())
    return;
Loc::loadMessages(__FILE__);
$module_id="tn.plan";

if (Loader::includeModule($module_id)):
    $MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);
    if($MOD_RIGHT>="R"){
        $arAllOptions = Array(
            Array("year_for_the_plan",Loc::getMessage("YEAR_FOR_THE_PLAN"), Array("string", "")),
            Array("year_for_fact",Loc::getMessage("YEAR_FOR_FACT"), Array("string", "")),
        );
    };
    if($MOD_RIGHT>="W")
    {
        if ($REQUEST_METHOD=="GET" && strlen($RestoreDefaults)>0)
        {
            Option::delete($module_id);
            reset($arGROUPS);
            while(list(,$value)=each($arGROUPS))
                $APPLICATION->DelGroupRight($module_id, array($value["ID"]));
        };
        if($REQUEST_METHOD=="POST" && strlen($Update)>0)
        {
            foreach($arAllOptions as $arOption)
            {
                $name=$arOption[0];
                $val=$_REQUEST[$name];
                if($arOption[2][0]=="checkbox" && $val!="Y")
                    $val="N";
                Option::set($module_id, $name, $val);
            }
        };
    };

    $aTabs = array(
        array("DIV" => "edit1", "TAB" => Loc::getMessage("PROPERTY"), "ICON" => "vote_settings", "TITLE" => Loc::getMessage("PROPERTY_TITLE")),
    );
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    ?>



    <?
    $tabControl->Begin();
    ?>
    <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?=LANGUAGE_ID?>">
        <?=bitrix_sessid_post()?>
        <?$tabControl->BeginNextTab();?>

        <?foreach($arAllOptions as $arOption){?>
            <?if($arOption[2][0]=="string"){?>
                <tr>
                    <td valign="middle" width="50%"><?=$arOption[1]?>:</td>
                    <td valign="middle" width="50%">
                        <?$val = Option::get($module_id, $arOption[0]);?>
                        <input name="<?=$arOption[0]?>" id="<?=$arOption[0]?>" type="text" value="<?=$val?>" >
                    </td>
                </tr>
            <?}elseif($arOption[2][0]=="checkbox"){?>
                <tr>
                    <td valign="top"  width="50%"><?=$arOption[1]?>:</td>
                    <td valign="middle" width="50%">
                        <?$val = Option::get($module_id, $arOption[0],"N");?>
                        <input type="checkbox" name="<?=$arOption[0]?>" id="<?=$arOption[0]?>" value="Y" <?if($val=="Y"){echo "checked";};?>>
                    </td>
                </tr>
            <?}elseif($arOption[2][0]=="select"){?>

            <?}?>
        <?}?>

        <?$tabControl->Buttons();?>
        <script language="JavaScript">
            function RestoreDefaults()
            {
                if(confirm('<?=CUtil::JSEscape(Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
                    window.location = "<?=$APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANGUAGE_ID?>&mid=<?echo urlencode($mid)?>";
            }
        </script>
        <input <?//if ($FORUM_RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?echo Loc::getMessage("PATH_SAVE")?>">
        <input type="hidden" name="Update" value="Y">
        <?$tabControl->End();?>
    </form>





<?endif?>