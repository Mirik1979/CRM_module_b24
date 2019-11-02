<?
defined('B_PROLOG_INCLUDED') || die;

/** @var CBitrixComponentTemplate $this */

use WComm\CrmStores\BizProc\StoreDocument;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UI\Extension;

Loader::includeModule('wcomm.crmstores');

$APPLICATION->SetTitle(Loc::getMessage('CRMSTORES_BP_LIST_TITLE'));

$APPLICATION->IncludeComponent(
    'bitrix:crm.control_panel',
    '',
    array(
        'ID' => 'STORES',
        'ACTIVE_ITEM_ID' => 'STORES',
    ),
    $component
);

$editUrlTemplate = $arResult['SEF_FOLDER'] . $arResult['SEF_URL_TEMPLATES']['bizproc_workflow_edit'];
$urlTemplates = array(
    'BP_EDIT' => $editUrlTemplate,
    'BP_EDIT_STATEMACHINE' => $editUrlTemplate . '?init=statemachine',
    'LIST' => $arResult['SEF_FOLDER'],
);

Extension::load('ui.buttons');

$this->SetViewTarget('pagetitle');
?>
<a class="ui-btn ui-btn-link" href="<?= HtmlFilter::encode($urlTemplates['LIST']) ?>">
    <?= Loc::getMessage('CRMSTORES_BACK_TO_LIST') ?>
</a>
<?
$this->EndViewTarget();

$APPLICATION->IncludeComponent(
    'bitrix:main.interface.toolbar',
    '',
    array(
        'BUTTONS'=>array(
            array(
                'TEXT' => Loc::getMessage('CRMSTORES_NEW_BP_STATEMACHINE'),
                'TITLE' => Loc::getMessage('CRMSTORES_NEW_BP_STATEMACHINE'),
                'LINK' => CComponentEngine::makePathFromTemplate($urlTemplates['BP_EDIT_STATEMACHINE'], array('ID' => 0)),
                'ICON' => 'btn-new',
            ),
            array(
                'TEXT' => Loc::getMessage('CRMSTORES_NEW_BP_SEQUENTAL'),
                'TITLE' => Loc::getMessage('CRMSTORES_NEW_BP_SEQUENTAL'),
                'LINK' => CComponentEngine::makePathFromTemplate($urlTemplates['BP_EDIT'], array('ID' => 0)),
                'ICON' => 'btn-new',
            ),
        ),
    )
);

$APPLICATION->IncludeComponent(
    'bitrix:bizproc.workflow.list',
    '.default',
    Array(
        'MODULE_ID' => 'wcomm.crmstores',
        'ENTITY' => StoreDocument::class,
        'DOCUMENT_ID' => 'store',
        'CREATE_DEFAULT_TEMPLATE' => 'N',
        'EDIT_URL' => $editUrlTemplate,
        'SET_TITLE' => 'N',
        'TARGET_MODULE_ID' => 'wcomm.crmstores',
    )
);
