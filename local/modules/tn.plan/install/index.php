<?
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use \Bitrix\Main\IO\Directory;
use \Bitrix\Main\IO\File;
use \Bitrix\Main\GroupTable;
use \Bitrix\Main\UserGroupTable;
use \Bitrix\Main\SiteTable;
use \Bitrix\Main\UrlRewriter;
use GetMessage;

Loc::loadMessages(__FILE__);

if (class_exists("tn_plan")) return;

Class tn_plan extends CModule
{
    var $MODULE_ID = "tn.plan";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "Y";
    var $errors;

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__)."/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("TN_PLAN_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TN_PLAN_MODULE_DESC");
        $this->PARTNER_NAME = GetMessage("TN_PLAN_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("TN_PLAN_PARTNER_URI");
    }

    function DoInstall()
    {
        global $USER;
        if ($USER->IsAdmin())
        {
            $this->InstallDB();
            $this->InstallFiles();
            $this->AddGroupAdmin();
            $this->InstallEvents();
            $this->InstallUrlRewrite();
            $this->InstallMenu();
            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallAgent();
        }
    }

    function DoUninstall()
    {
        global $USER;
        if ($USER->IsAdmin() && check_bitrix_sessid())
        {
            $this->UnInstallDB();
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->DelGroupAdmin();
            $this->UnInstallUrlRewrite();
            $this->UnInstallMenu();
            $this->UnInstallAgent();
            ModuleManager::unRegisterModule($this->MODULE_ID);
        }
    }

    function InstallUrlRewrite()
    {

    }

    function UnInstallUrlRewrite()
    {

    }

    function InstallMenu()
    {

    }

    function UnInstallMenu()
    {

    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    function getSite()
    {
        $arSite=[];
        $SiteTable = new SiteTable;
        $rsSite = $SiteTable->GetList();
        if($Site=$rsSite->fetch())
            $arSite[]=$Site;
        return $arSite;
    }

    function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        /*$eventManager->registerEventHandler(
            "socialnetwork",
            "OnFillSocNetLogEvents",
            $this->MODULE_ID,
            "Rocket\Bastion\App\Helpers\BastionLogHandlers",
            "OnFillSocNetLogEvents"
        );*/
        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        /*$eventManager->unRegisterEventHandler(
            "socialnetwork",
            "OnFillSocNetLogEvents",
            $this->MODULE_ID,
            "Rocket\Bastion\App\Helpers\BastionLogHandlers",
            "OnFillSocNetLogEvents"
        );*/
        return true;
    }

    function InstallFiles()
    {

        return true;
    }

    function UnInstallFiles()
    {

        return true;
    }

    function getDBFilePatch($file,$DBType)
    {
        $filePatch = "";
        $filePatch .= Application::getDocumentRoot();
        $filePatch .= getLocalPath("modules/{$this->MODULE_ID}/install/db/".$DBType."/".$file);
        return $filePatch;
    }

    /**
     * @param $file
     * @throws \Bitrix\Main\IO\FileNotFoundException
     */
    function runSqlFile($file)
    {
        $connection = Application::getConnection();
        $file=new File($this->getDBFilePatch($file,$connection->getType()));
        $connection->executeSqlBatch($file->getContents());
    }

    function InstallDB()
    {

        return true;
    }

    function UnInstallDB()
    {

        return true;
    }

    function GetModuleRightList()
    {
        $arr = array(
            "reference_id" => array("D","R","W"),
            "reference" => array(
                Loc::getMessage("TN_PLAN_FORM_DENIED"),
                Loc::getMessage("TN_PLAN_FORM_OPENED"),
                Loc::getMessage("TN_PLAN_FORM_FULL")
            ),
        );
        return $arr;
    }

    function AddGroupAdmin()
    {

    }

    function InstallAgent()
    {

        return true;
    }

    function UnInstallAgent()
    {

        return true;
    }

    function DelGroupAdmin()
    {

    }

};





