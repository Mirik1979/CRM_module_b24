<?
define('MODULE_ID', 'academy.crmstores');
define('ENTITY', '\Academy\CrmStores\BizProc\StoreDocument');

$fp = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bizproc/admin/bizproc_selector.php';
if (is_file($fp)) {
    require($fp);
}