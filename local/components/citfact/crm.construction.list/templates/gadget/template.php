<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');

if (empty($arResult['CONSTRUCTION']))
	echo GetMessage('CRM_DATA_EMPTY');
else
{
	foreach($arResult['CONSTRUCTION'] as $arConstruction)
	{
		?>
		<div class="crm-construction-element">
			<div class="crm-construction-element-date"><?=FormatDate('x', MakeTimeStamp($arConstruction['DATE_CREATE']), (time() + CTimeZone::GetOffset()))?></div>
			<div class="crm-construction-element-title"><a href="<?=$arConstruction['PATH_TO_CONSTRUCTION_SHOW']?>" title="<?=$arConstruction['CONSTRUCTION_FORMATTED_NAME']?>" bx-tooltip-user-id="CONSTRUCTION_<?=$arConstruction['~ID']?>" bx-tooltip-loader="<?=htmlspecialcharsbx('/local/components/citfact/crm.construction.show/card.ajax.php')?>" bx-tooltip-classname="crm_balloon_construction"><?=$arConstruction['CONSTRUCTION_FORMATTED_NAME']?></a></div>
			<div class="crm-construction-element-status"><?=GetMessage('CRM_COLUMN_CONSTRUCTION_TYPE')?>: <span><?=$arResult['TYPE_LIST'][$arConstruction['TYPE_ID']]?></span></div>
		</div>
		<?
	}
}
?>