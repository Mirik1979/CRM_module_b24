<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 07.11.2019
 * Time: 23:56
 */

namespace local\Events;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Tasks;
use Bitrix\Crm;

class OnTaskAdd
{
    /**
     * @param $arFields
     * @throws \Bitrix\Main\SystemException
     */
    public static function OnTaskAdd(&$arEventFields){

        global $USER_FIELD_MANAGER;
        \Bitrix\Main\Diag\Debug::writeToFile("taskmatch", "taskarray", "__miros.log");
        \Bitrix\Main\Diag\Debug::writeToFile($arEventFields, "taskarray", "__miros.log");
        $task = new \Bitrix\Tasks\Item\Task($arEventFields);
        //$rsTask = $task->getData();
        $beyond = false;
        $data = $task->export();
        if($data['UF_CRM_TASK']) {
            foreach ($data['UF_CRM_TASK'] as $val) {
                \Bitrix\Main\Diag\Debug::writeToFile($val, "taskarray", "__miros.log");
                 $link = substr($val, 0, 1);
                 if ($link != "S") {
                     $beyond = true;
                 } else {
                     \Bitrix\Main\Diag\Debug::writeToFile($val, "val2", "__miros.log");
                     $strlen = strlen($val) - 2;
                     $rest = substr($val, 2, $strlen);
                 }
            }
        }
        \Bitrix\Main\Diag\Debug::writeToFile($beyond, "beyond", "__miros.log");
        \Bitrix\Main\Diag\Debug::writeToFile($rest, "objid", "__miros.log");

        // попытка обновить задачу неудачна
        /*if ($beyond) {
            sleep(3;)
            if (\Bitrix\Main\Loader::includeModule('crm'))
            {
                $res = \CCrmActivity::GetList(array(), array("ASSOCIATED_ENTITY_ID" => $arEventFields));


                while($fields = $res->Fetch())
                {
                    \Bitrix\Main\Diag\Debug::writeToFile($fields, "fields", "__miros.log");
                }

                $res = \CCrmActivity::GetbyId(258);
                //while($fields = $res->Fetch())
                //{
                    \Bitrix\Main\Diag\Debug::writeToFile($res, "fields222", "__miros.log");
                //}



            }
        } */

        if ($rest && $beyond == false) {
            $activity = array(
                'OWNER_ID' => 20,
                'OWNER_TYPE_ID' => 4,
                'TYPE_ID' => 3,
                'PROVIDER_ID' => 'TASKS',
                'PROVIDER_TYPE_ID' => 'TASK',
                'ASSOCIATED_ENTITY_ID' => $arEventFields,
                'SUBJECT' => $data['TITLE'],
                'COMPLETED' => 'N',
                'RESPONSIBLE_ID' => $data['CHANGED_BY'],
                'PRIORITY' => 2,
                'DESCRIPTION_TYPE' => 1,
                'NOTIFY_TYPE' => 0,
                'NOTIFY_VALUE' => 0,
                'URN' => $rest,
                'DIRECTION' => 0,
                'START_TIME' => $data['CREATED_DATE'],
                'END_TIME' => $data['CREATED_DATE'],
                'STORAGE_TYPE_ID' => 3,
                //STORAGE_ELEMENT_IDS' => 'a:0:{}',
                'AUTHOR_ID' => $data['CHANGED_BY']
            );
            $id = \CCrmActivity::Add($activity);
            //\Bitrix\Main\Diag\Debug::writeToFile($id, "iddd", "__miros.log");

            $arFields['UF_STORE'] = $rest;
            $res = $USER_FIELD_MANAGER->Update('CRM_ACTIVITY', $id, $arFields);
        }



    }
}
