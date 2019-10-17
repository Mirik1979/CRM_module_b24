<?php

namespace Wcomm\CrmStores\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\UserTable;

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

    public static function getMap()
    {
        return array(
            new IntegerField('ID', array('primary' => true, 'autocomplete' => true)),
            new StringField('NAME'),
            new StringField('ADDRESS'),
            new IntegerField('ASSIGNED_BY_ID'),
            new StringField('ADDRESS'),
            new StringField('SITE'),
            new IntegerField('SQZASTR'),
            new IntegerField('KISONUM'),
            new IntegerField('ETAG'),
            new IntegerField('OCHERED'),
            new DateField('DATEBEGIN', []),
            new DateField('DATEEND', []),
            //new DateField('DATEBEGIN'),
            //new DateField('DATEEND'),
            /*new EnumField('STAGE', array(
                'values' => array('Объект завершен', 'Внутренняя отделка',
                    'Несущие конструкцци', 'Отделка фасада',
                    'Кровля', 'Проект', 'Подготовка площадки', 'Фундамент',
                    'Объект заморожен', 'Ремонт и реконструкция',
                    'Объект построен')
            )), */
            /*new EnumField('TYPEOBJ', array(
                'values' => array('Торговое сооружение', 'Спортивное здание и сооружение',
                    'Промышленное здание и сооружение', 'Здравоохранение',
                    'Образование', 'Жилой дом', 'Дорога, мост, туннели',
                    'Бытовой объект строительства',
                    'Административное здание', 'Автосалон, автосервис, АЗС, ГСК',
                    'Коттердж','Логистический центр', 'Религиозный объект',
                    'Культурное сооружение', 'Транспортное сооружение',
                    'Сельхозобъект')
            )),
            new EnumField('TYPECONST', array(
                'values' => array('Новое строительсвто', 'Ремонт и реконструкция')
            )),
            new EnumField('PROJECT', array(
                'values' => array('Текущий ремонт ЖКХ', 'ТДС', 'Капитальный ремонт ЖКХ')
            )), */
            new TextField('DESCR'),
            new IntegerField('PICTURE', array(
                'validation' => function() {
                    return array(
                        function ($value) {
                            /*Валидация - файл может быть только картинкой*/
                            if(intval($value)>0){
                                $rsFile = CFile::GetByID($value);
                                if($arFile = $rsFile->Fetch()){
                                    if (!CFile::IsImage($arFile["FILE_NAME"], $arFile["CONTENT_TYPE"])) {
                                        return Loc::getMessage("FILE_ERROR");
                                    }
                                }
                            }
                            return true;
                        }
                    );
                }
            )),
            new IntegerField('FILE', array(
                'validation' => function() {
                    return array(
                        function ($value) {
                            /*Валидация - файл может быть только картинкой*/
                            if(intval($value)>0){
                                $rsFile = CFile::GetByID($value);
                                if(!$rsFile){
                                    return Loc::getMessage("FILE_ERROR");
                                }
                            }
                            return true;
                        }
                    );
                }
            )),
            new IntegerField('RASSCHET', array(
                'validation' => function() {
                    return array(
                        function ($value) {
                            /*Валидация - файл может быть только картинкой*/
                            if(intval($value)>0){
                                $rsFile = CFile::GetByID($value);
                                if(!$rsFile){
                                    return Loc::getMessage("FILE_ERROR");
                                }
                            }
                            return true;
                        }
                    );
                }
            )),
            new ReferenceField(
                'ASSIGNED_BY',
                UserTable::getEntity(),
                array('=this.ASSIGNED_BY_ID' => 'ref.ID')
            ),
            new IntegerField('STAGE_ID'),
            new ReferenceField(
                'STAGE',
                'Bitrix\Iblock\ElementTable',
                [
                    '=this.STAGE_ID' => 'ref.ID',
                    '=ref.IBLOCK_ID' =>  new \Bitrix\Main\DB\SqlExpression('?i', 37)
                ]
            ),
            new IntegerField('PRJTYPE_ID'),
            new ReferenceField(
                'PRJTYPE',
                'Bitrix\Iblock\ElementTable',
                [
                    '=this.PRJTYPE_ID' => 'ref.ID',
                    '=ref.IBLOCK_ID' =>  new \Bitrix\Main\DB\SqlExpression('?i', 40)
                ]
            ),
            new IntegerField('OBJYPE_ID'),
            new ReferenceField(
                'OBJYPE',
                'Bitrix\Iblock\ElementTable',
                [
                    '=this.OBJYPE_ID' => 'ref.ID',
                    '=ref.IBLOCK_ID' =>  new \Bitrix\Main\DB\SqlExpression('?i', 38)
                ]
            ),
            new IntegerField('CONSTYP_ID'),
            new ReferenceField(
                'CONSTYPE',
                'Bitrix\Iblock\ElementTable',
                [
                    '=this.CONSTYP_ID' => 'ref.ID',
                    '=ref.IBLOCK_ID' =>  new \Bitrix\Main\DB\SqlExpression('?i', 39)
                ]
            )
        );
    }

    public static function onBeforeDelete(Entity\Event $event)
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

    /*Удаление старого файла при обновлении элемента*/
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
    }

}
