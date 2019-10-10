<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var CBitrixComponentTemplate $this */

global $APPLICATION;

CUtil::InitJSCore(array('ajax', 'popup'));
\Bitrix\Main\UI\Extension::load(["sidepanel"]);

$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->AddHeadScript('/bitrix/js/crm/crm.js');
$selectorOptions = [
    'lazyLoad' => 'Y',
    'context' => (!empty($arParams['CONTEXT']) ? $arParams['CONTEXT'] : 'crmEntityCreate'),
    'contextCode' => '',
    'enableSonetgroups' => 'N',
    'enableUsers' => 'N',
    'useClientDatabase' => 'N',
    'enableAll' => 'N',
    'enableDepartments' => 'N',
    'enableCrm' => 'Y',
    'crmPrefixType' => 'SHORT'
];

$tabsCounter = 0;
if (in_array(\CCrmOwnerType::ContactName, $arParams['ENTITY_TYPE']))
{
    $selectorOptions['enableCrmContacts'] = 'Y';
    $selectorOptions['addTabCrmContacts'] = 'Y';
    $tabsCounter++;
}
if (in_array(\CCrmOwnerType::CompanyName, $arParams['ENTITY_TYPE']))
{
    $selectorOptions['enableCrmCompanies'] = 'Y';
    $selectorOptions['addTabCrmCompanies'] = 'Y';
    $tabsCounter++;
}
if (in_array(\CCrmOwnerType::LeadName, $arParams['ENTITY_TYPE']))
{
    $selectorOptions['enableCrmLeads'] = 'Y';
    $selectorOptions['addTabCrmLeads'] = 'Y';
    $tabsCounter++;
}
if (in_array(\CCrmOwnerType::DealName, $arParams['ENTITY_TYPE']))
{
    $selectorOptions['enableCrmDeals'] = 'Y';
    $selectorOptions['addTabCrmDeals'] = 'Y';
    $tabsCounter++;
}
if (in_array(\CCrmOwnerType::OrderName, $arParams['ENTITY_TYPE']))
{
    $selectorOptions['enableCrmOrders'] = 'Y';
    $selectorOptions['addTabCrmOrders'] = 'Y';
    $tabsCounter++;
}
if ($tabsCounter <= 1)
{
    $selectorOptions['addTabCrmContacts'] = 'N';
    $selectorOptions['addTabCrmCompanies'] = 'N';
    $selectorOptions['addTabCrmLeads'] = 'N';
    $selectorOptions['addTabCrmDeals'] = 'N';
    $selectorOptions['addTabCrmOrders'] = 'N';
}

$fieldName = $arParams['arUserField']['~FIELD_NAME'];
$formName = isset($arParams['form_name']) ? strval($arParams['form_name']) : '';

if ($arResult['PERMISSION_DENIED'])
{
	?><div id="crm-<?=$fieldUID?>-box">
		<div class="crm-button-open"><?=GetMessage('CRM_SFE_PERMISSION_DENIED')?></div>
	</div><?
}
else
{
    ?>
    <div id="main-crm-status-entity" class="main-crm-status-entity">
    <?
    if($arParams['AJAX']=='Y') {$APPLICATION->RestartBuffer();}
    foreach($arResult['ELEMENTS'] as $element) {
        $randString = $this->randString(4).rand();
        $fieldUID = strtolower(str_replace('_', '-', $fieldName)) . '_' . $randString;
        if ($formName !== '') {
            $fieldUID = strtolower(str_replace('_', '-', $formName)) . '-' . $fieldUID;
        }

        $jsObject = 'CrmEntitySelector_' . $randString;

        ?>
        <div id="crm-<?= $fieldUID ?>-box">
        <span id="crm-<?= $fieldUID ?>-open"><?


        $APPLICATION->IncludeComponent(
            "bitrix:main.user.selector",
            "",
            [
                "ID" => $fieldUID,
                "LIST" => (
                !empty($element['SELECTED_LIST'])
                    ? $element['SELECTED_LIST']
                    : []
                ),
                "LAZYLOAD" => "Y",
                "INPUT_NAME" => $fieldName . '_STATUS_ENTITY_COMPANY_' . $randString,
                "USE_SYMBOLIC_ID" => $arResult['USE_SYMBOLIC_ID'],
                "CONVERT_TO_SYMBOLIC_ID" => (!$arResult['USE_SYMBOLIC_ID'] ? 'N' : false),
                "API_VERSION" => 3,
                "SELECTOR_OPTIONS" => $selectorOptions
            ]
        );
        ?>
        </span><?
        if (!empty($arParams['createNewEntity'])) {
            ?>
            <script>

                BX.ready(function () {
                    BX['<?=$jsObject?>'] = new BX.CrmEntitySelector({
                        randomString: '<?=$randString?>',
                        jsObject: '<?=$jsObject?>',
                        fieldUid: '<?=$fieldUID?>',
                        fieldName: '<?=$fieldName?>',
                        usePrefix: '<?=$arResult['PREFIX']?>',
                        multiple: '<?=$arResult['MULTIPLE']?>',
                        context: '<?=!empty($arParams['CONTEXT']) ? $arParams['CONTEXT'] : 'crmEntityCreate'?>',
                        listPrefix: <?=\Bitrix\Main\Web\Json::encode($arResult['LIST_PREFIXES'])?>,
                        selectorEntityTypes: <?=\Bitrix\Main\Web\Json::encode($arResult['SELECTOR_ENTITY_TYPES'])?>,
                        listElement: <?=\Bitrix\Main\Web\Json::encode($element['ELEMENT'])?>,
                        listEntityType: <?=\Bitrix\Main\Web\Json::encode($arResult['ENTITY_TYPE'])?>,
                        listEntityCreateUrl: <?=\Bitrix\Main\Web\Json::encode($arResult['LIST_ENTITY_CREATE_URL'])?>,
                        pluralCreation: '<?=!empty($arResult['PLURAL_CREATION']) ? 'true' : '' ?>',
                        currentEntityType: '<?=!empty($arResult['CURRENT_ENTITY_TYPE']) ? $arResult['CURRENT_ENTITY_TYPE'] : null?>'
                    });

                    BX.message({
                        CRM_FF_LEAD: '<?=GetMessageJS("CRM_FF_LEAD")?>',
                        CRM_FF_CONTACT: '<?=GetMessageJS("CRM_FF_CONTACT")?>',
                        CRM_FF_COMPANY: '<?=GetMessageJS("CRM_FF_COMPANY")?>',
                        CRM_FF_DEAL: '<?=GetMessageJS("CRM_FF_DEAL")?>',
                        CRM_FF_ORDER: '<?=GetMessageJS("CRM_FF_ORDER")?>',
                        CRM_FF_QUOTE: '<?=GetMessageJS("CRM_FF_QUOTE")?>',
                        CRM_FF_OK: '<?=GetMessageJS("CRM_FF_OK")?>',
                        CRM_FF_CANCEL: '<?=GetMessageJS("CRM_FF_CANCEL")?>',
                        CRM_FF_CLOSE: '<?=GetMessageJS("CRM_FF_CLOSE")?>',
                        CRM_FF_SEARCH: '<?=GetMessageJS("CRM_FF_SEARCH")?>',
                        CRM_FF_NO_RESULT: '<?=GetMessageJS("CRM_FF_NO_RESULT")?>',
                        CRM_FF_CHOISE: '<?=GetMessageJS("CRM_FF_CHOISE")?>',
                        CRM_FF_CHANGE: '<?=GetMessageJS("CRM_FF_CHANGE")?>',
                        CRM_FF_LAST: '<?=GetMessageJS("CRM_FF_LAST")?>',
                        CRM_CES_CREATE_LEAD: '<?=GetMessageJS("CRM_CES_CREATE_LEAD")?>',
                        CRM_CES_CREATE_CONTACT: '<?=GetMessageJS("CRM_CES_CREATE_CONTACT")?>',
                        CRM_CES_CREATE_COMPANY: '<?=GetMessageJS("CRM_CES_CREATE_COMPANY")?>',
                        CRM_CES_CREATE_DEAL: '<?=GetMessageJS("CRM_CES_CREATE_DEAL")?>'
                    });
                });

            </script>

            <div class="crm-button-open"><span
                        onclick="BX['<?= $jsObject ?>'].createNewEntity(event);"><?= GetMessage('CRM_CES_CREATE'); ?></span>
            </div><?
        }

        $bWasSelect = false;
        ?>
        <span class="fields crm_status field-wrap crm_status_entity">
        <select name="<?= $fieldName . '_STATUS_ENTITY_COMPANY_TYPE_' . $randString ?>">
            <?

            foreach ($arResult['CRM_STATUS'] as $key => $val) {
                $bSelected = ($key == $element['TYPE_COMPANY']) && (!$bWasSelect);
                $bWasSelect = $bWasSelect || $bSelected;

                ?>
                <option value="<?
                echo $key ?>"<?
                echo($bSelected ? " selected" : "") ?>><?
                echo $val ?></option><?
            }
            ?></select>
        </span><?

        ?></div><?
    }
    if($arParams['AJAX']=='Y') {die();}
?>
    </div>
    <?
    if ($arParams["arUserField"]["MULTIPLE"] == "Y" && $arParams["SHOW_BUTTON"] != "N"):
        ?>
        <input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onClick="addElement()">
        <script>
            var params = <?=\Bitrix\Main\Web\Json::encode($arParams)?>;
            BX.ready(function () {
                addElement = function () {
                    BX.ajax({
                        url: '/local/include/system_field/ajax.php',
                        method: 'POST',
                        data: {params: params},
                        dataType: 'html',
                        onsuccess: function (data) {
                            document.getElementById('main-crm-status-entity').insertAdjacentHTML('beforeend', data);
                        },
                        onfailure: function(){
                            console.log('ошибка');
                        }
                    });
                }
            });
        </script>
    <?endif;?>
<?
}
?>
