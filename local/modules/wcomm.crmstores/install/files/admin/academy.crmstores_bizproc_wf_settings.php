<?
define('MODULE_ID', 'academy.crmstores');
define('ENTITY', '\Academy\CrmStores\BizProc\StoreDocument');

$fp = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bizprocdesigner/admin/bizproc_wf_settings.php';
if (is_file($fp)) {
    require($fp);
}