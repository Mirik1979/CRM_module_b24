<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI;

UI\Extension::load("ui.tooltip");

global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm-preview.css');
?>

<div class="crm-preview">
	<div class="crm-preview-header">
		<span class="crm-preview-header-icon crm-preview-header-icon-construction"></span>
		<? if($arResult['HEAD_IMAGE_URL'] !== ''): ?>
			<span class="crm-preview-header-img">
					<img alt="" src="<?=htmlspecialcharsbx($arResult['HEAD_IMAGE_URL'])?>" />
				</span>
		<? endif; ?>
		<span class="crm-preview-header-title">
			<?=GetMessage("CRM_TITLE_CONSTRUCTION")?>:
			<a href="<?=htmlspecialcharsbx($arParams['URL'])?>" target="_blank"><?=htmlspecialcharsbx($arResult['FULL_NAME'])?></a>
		</span>
	</div>
	<table class="crm-preview-info">
		<tr>
			<td><?= GetMessage('CRM_CONSTRUCTION_RESPONSIBLE')?>: </td>
			<td>
				<a id="a_<?=htmlspecialcharsbx($arResult['ASSIGNED_BY_UNIQID'])?>" href="<?=htmlspecialcharsbx($arResult["ASSIGNED_BY_PROFILE"])?>" bx-tooltip-user-id="<?=htmlspecialcharsbx($arResult["ASSIGNED_BY_ID"])?>">
					<?=htmlspecialcharsbx($arResult['ASSIGNED_BY_FORMATTED_NAME'])?>
				</a>
			</td>
		</tr>
		<? foreach($arResult['CONSTRUCTION_INFO'] as $constructionInfoType => $constructionInfoValue): ?>
			<tr>
				<td><?= GetMessage('CRM_CONSTRUCTION_INFO_'.$constructionInfoType)?>: </td>
				<td>
					<?
					$constructionInfoValue = htmlspecialcharsbx($constructionInfoValue);
					switch($constructionInfoType)
					{
						case 'EMAIL':
							?><a href="mailto:<?=$constructionInfoValue?>" title="<?=$constructionInfoValue?>"><?=$constructionInfoValue?></a><?
							break;
						case 'PHONE':
							?><a href="callto://<?=$constructionInfoValue?>" onclick="if(typeof(BXIM) !== 'undefined') { BXIM.phoneTo('8 4012 531249'); return BX.PreventDefault(event); }" title="<?=$constructionInfoValue?>"><?=$constructionInfoValue?></a><?
							break;
						case 'WEB':
							?><a href="http://<?=$constructionInfoValue?>"><?=$constructionInfoValue?></a><?
							break;
						default:
							echo $constructionInfoValue;
					}
					?>
				</td>
			</tr>
		<? endforeach ?>
	</table>
</div>