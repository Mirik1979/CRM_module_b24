<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

Bitrix\Main\UI\Extension::load("ui.tooltip");

\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/crm.css');
if(\CCrmSipHelper::isEnabled())
	\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/common.js');

$publicMode = isset($arParams["PUBLIC_MODE"]) && $arParams["PUBLIC_MODE"] === true;
foreach ($arResult["VALUE"] as $entityType => $arEntity):
    foreach ($arEntity as $entityId => $entity):
?>
    <?
        if ($publicMode)
        {
            ?>
            <div class="crm-entity-widget-client-block">
                <div class="crm-entity-widget-client-box">
                    <div class="crm-entity-widget-client-box-name-container">
                        <div class="crm-entity-widget-client-box-name-row">
                            <?=htmlspecialcharsbx($entity['ENTITY_TITLE'])?>
                        </div>
                    </div>
                    <div class="crm-entity-widget-client-box-position"><?=(isset($entity['ENTITY_TYPE'])? ', '.htmlspecialcharsbx($entity['ENTITY_TYPE']): '')?></div>
                </div>
            </div>
            <?
        }
        else
        {
            $entityTypeLower = strtolower($entityType);

            if($entityType == 'ORDER')
            {
                $url = '/bitrix/components/bitrix/crm.order.details/card.ajax.php';
            }
            else
            {
                $url = '/bitrix/components/bitrix/crm.'.$entityTypeLower.'.show/card.ajax.php';
            }

            ?>

            <div class="crm-entity-widget-client-block">
                <div class="crm-entity-widget-client-box">
                    <div class="crm-entity-widget-client-box-name-container">
                        <div class="crm-entity-widget-client-box-name-row">
                            <a class="crm-entity-widget-client-box-name" href="<?=htmlspecialcharsbx($entity['ENTITY_LINK'])?>" target="_blank"
                               bx-tooltip-user-id="<?=htmlspecialcharsbx($entity['ID'])?>"
                               bx-tooltip-loader="<?=htmlspecialcharsbx($url)?>"
                               bx-tooltip-classname="crm_balloon<?=($entityType == 'LEAD' || $entityType == 'DEAL'? '_no_photo': '_'.$entityTypeLower)?>">
                                <?=htmlspecialcharsbx($entity['ENTITY_TITLE'])?>
                            </a>
                        </div>
                    </div>
                    <div class="crm-entity-widget-client-box-position"><?=(isset($entity['ENTITY_TYPE'])? htmlspecialcharsbx($entity['ENTITY_TYPE']): '')?></div>
                </div>
            </div>
            <?
        }
    ?>
    <?
    endforeach;
endforeach;
?>
<?if(\CCrmSipHelper::isEnabled()):?>
<script type="text/javascript">
	BX.ready(
		function()
		{
			if(typeof(window["BXIM"]) === "undefined" || typeof(BX.CrmSipManager) === "undefined")
			{
				return;
			}

			if(typeof(BX.CrmSipManager.messages) === "undefined")
			{
				BX.CrmSipManager.messages =
				{
					"unknownRecipient": "<?= GetMessageJS('CRM_SIP_MGR_UNKNOWN_RECIPIENT')?>",
					"makeCall": "<?= GetMessageJS('CRM_SIP_MGR_MAKE_CALL')?>"
				};
			}

			var sipMgr = BX.CrmSipManager.getCurrent();
			sipMgr.setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::LeadName)?>",
				"/bitrix/components/bitrix/crm.lead.show/ajax.php?<?=bitrix_sessid_get()?>"
			);

			sipMgr.setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::ContactName)?>",
				"/bitrix/components/bitrix/crm.contact.show/ajax.php?<?=bitrix_sessid_get()?>"
			);

			sipMgr.setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::CompanyName)?>",
				"/bitrix/components/bitrix/crm.company.show/ajax.php?<?=bitrix_sessid_get()?>"
			);
		}
	);
</script>
<? endif ?>