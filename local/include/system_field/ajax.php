<?require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");?>
<?
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
global $regionID;
$request = Application::getInstance()->getContext()->getRequest();


if(!$request->isAjaxRequest())
{
    die();
}
Loader::includeModule('crm');

$arParams = $request->get('params');
$arParams['~arUserField']['VALUE'] = [];
$arParams['arUserField']['MULTIPLE'] = 'N';
$arParams['~arUserField']['MULTIPLE'] = 'N';
$arParams['~arUserField']['MULTIPLE'] = 'N';
$arParams['arUserField']["FIELD_NAME"] = $arParams['~arUserField']['FIELD_NAME'];
$arParams['AJAX'] = 'Y';

ob_start();
$GLOBALS['APPLICATION']->IncludeComponent(
    'bitrix:system.field.edit',
    'crm_status_entity',
    $arParams,
    false,
    array('HIDE_ICONS' => 'Y')
);
$html = ob_get_contents();
ob_end_clean();
echo $html;

?>
