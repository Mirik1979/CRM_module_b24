<?php
define('MODULE_ID', 'wcomm.crmstores');
define('ENTITY', '\WComm\CrmStores\BizProc\StoreDocument');

$fp = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bizprocdesigner/admin/bizproc_activity_settings.php';
if (is_file($fp)) {
    require($fp);
}
