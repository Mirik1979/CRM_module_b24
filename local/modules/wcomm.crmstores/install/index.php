<?php
defined('B_PROLOG_INCLUDED') || die;

use Wcomm\CrmStores\Entity\StoreTable;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class wcomm_crmstores extends CModule
{
    const MODULE_ID = 'wcomm.crmstores';
    var $MODULE_ID = self::MODULE_ID;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('WCOMM_CRMSTORES.MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('WCOMM_CRMSTORES.MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('WCOMM_CRMSTORES.PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('WCOMM_CRMSTORES.PARTNER_URI');
    }

    function DoInstall()
    {
        ModuleManager::registerModule(self::MODULE_ID);

        $this->InstallDB();
        $this->InstallFiles();
        $this->InstallEvents();
    }

    function DoUninstall()
    {
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        $this->UnInstallDB();

        ModuleManager::unRegisterModule(self::MODULE_ID);
    }

    function InstallDB()
    {
        Loader::includeModule('wcomm.crmstores');

        $db = Application::getConnection();

        $storeEntity = StoreTable::getEntity();
        if (!$db->isTableExists($storeEntity->getDBTableName())) {
            $storeEntity->createDbTable();
        }
    }

    function UnInstallDB()
    {
        // Не существенно в данном примере.
    }

    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandlerCompatible(
            'crm',
            'OnAfterCrmControlPanelBuild',
            self::MODULE_ID,
            '\Wcomm\CrmStores\Handler\CrmMenu',
            'addStores'
        );

        $eventManager->registerEventHandlerCompatible(
            'main',
            'OnUserTypeBuildList',
            self::MODULE_ID,
            '\Wcomm\CrmStores\UserType\StoreBinding',
            'GetUserTypeDescription'
        );
    }

    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'crm',
            'OnAfterCrmControlPanelBuild',
            self::MODULE_ID,
            '\Wcomm\CrmStores\Handler\CrmMenu',
            'addStores'
        );

        $eventManager->unRegisterEventHandler(
            'main',
            'OnUserTypeBuildList',
            self::MODULE_ID,
            '\Wcomm\CrmStores\UserType\StoreBinding',
            'GetUserTypeDescription'
        );
    }

    function InstallFiles()
    {
        $documentRoot = Application::getDocumentRoot();

        CopyDirFiles(
            __DIR__ . '/files/components',
            $documentRoot . '/local/components',
            true,
            true
        );

        CopyDirFiles(
            __DIR__ . '/files/pub/crm',
            $documentRoot . '/crm',
            true,
            true
        );

        CUrlRewriter::Add(array(
            'CONDITION' => '#^/crm/stores/#',
            'RULE' => '',
            'ID' => 'wcomm.crmstores:stores',
            'PATH' => '/crm/stores/index.php',
        ));
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx('/crm/stores');
        DeleteDirFilesEx('/local/components/wcomm.crmstores');

        CUrlRewriter::Delete(array(
            'ID' => 'wcomm.crmstores:stores',
            'PATH' => '/crm/stores/index.php',
        ));
    }
}