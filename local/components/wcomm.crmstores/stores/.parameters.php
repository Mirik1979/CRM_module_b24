<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
    'PARAMETERS' => array(
        'SEF_MODE' => array(
            'details' => array(
                'NAME' => Loc::getMessage('CRMSTORES_DETAILS_URL_TEMPLATE'),
                'DEFAULT' => '#STORE_ID#/',
                'VARIABLES' => array('STORE_ID')
            ),
            'edit' => array(
                'NAME' => Loc::getMessage('CRMSTORES_EDIT_URL_TEMPLATE'),
                'DEFAULT' => '#STORE_ID#/edit/',
                'VARIABLES' => array('STORE_ID')
            ),
            'import'=>array(
                'NAME' => Loc::getMessage('CRMSTORES_IMPORT_URL_TEMPLATE'),
                'DEFAULT' => 'import/'

            )
        )
    )
);
