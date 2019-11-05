<?php

namespace Wcomm\CrmStores\Entity;



use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\UserTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Crm;

class StoreTable extends DataManager
{

    public static function getTableName()
    {
        return 'wcomm_crmstores_store';
    }

    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getUfId()
    {
        return 'CRM_STORES';
    }

    public static function getMap()
    {
        return array(
            new IntegerField('ID', array('primary' => true, 'autocomplete' => true)),
            new StringField('NAME'),
            new StringField('ADDRESS'),
            new IntegerField('ASSIGNED_BY_ID'),
            new ReferenceField(
                'ASSIGNED_BY',
                UserTable::getEntity(),
                array('=this.ASSIGNED_BY_ID' => 'ref.ID')
            )
        );
    }

    public static function GetFieldsInfo()
    {
        return array(
            'ID' => array (
                'TYPE' => 'interger',
                'ATTRIBUTES' => array('R-0'
                )
            ),
            'NAME' => array (
                'TYPE' => 'string',
                'ATTRIBUTES' => array('REQ'
                )
            ),
            //'ADDRESS' => array (
            //    'TYPE' => 'string',
            //    'ATTRIBUTES' => array('REQ'
            //    )
            //),
            'ASSIGNED_BY_ID' => array (
                'TYPE' => 'user'
            )
        );
    }

    public static function GetFieldsInfoUf()
    {

        global $USER_FIELD_MANAGER;
        $arF = self::GetFieldsInfo();
        $arUserFields = $USER_FIELD_MANAGER->GetUserFields(self::getUfId());
        array_merge($arF, $arUserFields);

    }


    public static function getListEx($params = array()) {
        global $USER_FIELD_MANAGER;
        $dbResult = self::GetList($params);
        $stores = $dbResult->fetchAll();
        $arUserFields = $USER_FIELD_MANAGER->GetUserFields(self::getUfId());
        foreach ($stores as $key => $store) {
            foreach($arUserFields as $FIELD_ID => $arField) {
                $stores[$key][$FIELD_ID] = $USER_FIELD_MANAGER->GetUserFieldValue(self::getUfId(), $FIELD_ID, $store['ID']);

            }
        }
        return $stores;
    }




    /*public static function onBeforeDelete(Entity\Event $event)
    {
        $primary = $event->getParameter("primary");
        $rs = static::GetByID($primary["ID"]);
        if($ar = $rs->Fetch()){
            if (intval($ar['PICTURE'])>0)
            {
                CFile::Delete($ar['PICTURE']);
            }
        }
    }


    public static function onBeforeUpdate(Entity\Event $event)
    {
        $fields = $event->getParameter("fields");
        $primary = $event->getParameter("primary");
        if(intval($fields['PICTURE'])>0){
            $rs = static::GetByID($primary["ID"]);
            if($old = $rs->Fetch()){
                if (intval($old['PICTURE'])>0 && $fields["PICTURE"]!=$old["PICTURE"])
                {
                    CFile::Delete($old['PICTURE']);
                }
            }
        }
    } */

}
