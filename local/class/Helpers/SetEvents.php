<?php

namespace local\Helpers;

use Bitrix\Main\EventManager;

class SetEvents
{

    public static function init()
    {

        EventManager::getInstance()->addEventHandler(
            'main',
            'OnUserTypeBuildList',
            array(
                "local\\Events\\UserTypeOK",
                "GetUserTypeDescription"
            )
        );

        EventManager::getInstance()->addEventHandler(
            'main',
            'OnUserTypeBuildList',
            array(
                "local\\Events\\UserTypeFieldEnum",
                "GetUserTypeDescription"
            )
        );

        EventManager::getInstance()->addEventHandler(
            'main',
            'OnUserTypeBuildList',
            array(
                "local\\Events\\UserTypeFieldEnumList",
                "GetUserTypeDescription"
            )
        );

        EventManager::getInstance()->addEventHandler(
            'crm',
            'OnBeforeCrmDealAdd',
            array(
                "local\\Events\\OnCrmDeal",
                "OnBeforeCrmDealAdd"
            )
        );

        EventManager::getInstance()->addEventHandler(
            'tasks',
            'OnTaskAdd',
            array(
                "local\\Events\\OnTaskAdd",
                "OnTaskAdd"
            )
        );

    }

}