<?php
defined('B_PROLOG_INCLUDED') || die;


use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;


class CWcommCrmStoresStoresComponent extends CBitrixComponent
{
    const SEF_DEFAULT_TEMPLATES = array(
        'details' => '#STORE_ID#/',
        'edit' => '#STORE_ID#/edit/',
        'import' => 'import/',
        'dedupe' => 'dedupe/',
        'bizproc_workflow_admin' => 'bp_list/',
        'bizproc_workflow_edit' => 'bp_edit/#ID#/',
    );


    public function executeComponent()
    {
        // Полноценное управление адресами страниц не существенно для данного урока.

        if (empty($this->arParams['SEF_MODE']) || $this->arParams['SEF_MODE'] != 'Y') {
            ShowError(Loc::getMessage('CRMSTORES_SEF_NOT_ENABLED'));
            return;
        }

        if (empty($this->arParams['SEF_FOLDER'])) {
            ShowError(Loc::getMessage('CRMSTORES_SEF_BASE_EMPTY'));
            return;
        }

        if (!is_array($this->arParams['SEF_URL_TEMPLATES'])) {
            $this->arParams['SEF_URL_TEMPLATES'] = array();
        }

        $sefTemplates = array_merge(self::SEF_DEFAULT_TEMPLATES, $this->arParams['SEF_URL_TEMPLATES']);

        $page = CComponentEngine::parseComponentPath(
            $this->arParams['SEF_FOLDER'],
            $sefTemplates,
            $arVariables
        );

        if (empty($page)) {
            $page = 'list';
        }
        global $APPLICATION;
        $pathtostore = '/crm/stores/';
        $import = '/crm/stores/import/';
        $dedupe = '/crm/stores/dedupe/';


        $this->arResult = array(
            'SEF_FOLDER' => $this->arParams['SEF_FOLDER'],
            'SEF_URL_TEMPLATES' => $sefTemplates,
            'VARIABLES' => $arVariables,
            'PATH_TO_STORE_LIST' => $pathtostore,
            'PATH_TO_STORE_IMPORT' => $import,
            'PATH_TO_STORE_DEDUPE' => $dedupe,
        );

        $this->includeComponentTemplate($page);
    }
}
