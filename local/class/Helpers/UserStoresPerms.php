<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 29.10.2019
 * Time: 23:57
 */

namespace local\Helpers;

use Bitrix\Main\Loader;
use CCrmRole;

class UserStoresPerms
{

    /*
    Варианты доступа
    READ = Чтение
    ADD = Добавление
    WRITE = Обновление
    DELETE = Удаление
    Уровни доступа
    "" = "Нет доступа"
    "X" = "Все"
    "A" = "Свои"
    "D" = "Свои + своего отдела"
    "F" = "Свои + своего отдела + подотделов"
    "O" = "Все открытые"
    "S" = "Руководитель сегмента"
    "T" = "Руководитель товарного направления"
    */

    /**
     * @param $arr
     * @return array
     */
    protected static function prepareArray($arr){
        $res=[];
        foreach ($arr as $key=>$val)
            $res[$key]=$val['-'];
        return $res;
    }

    /**
     * @param $id
     * @return array
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getPermsById($id){
        $perms=[];
        Loader::includeModule("crm");
        $obRes = CCrmRole::GetUserPerms($id);
        if($obRes["STORES"])
            return self::prepareArray($obRes["STORES"]);
        return $perms;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getPerms(){
        global $USER;
        $id=$USER->GetID();
        if($id>0)
            return self::getPermsById($id);
        return [];
    }

}