<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 07.11.2019
 * Time: 23:56
 */

namespace local\Events;

use Bitrix\Main\Application;

class OnCrmDeal
{
    /**
     * @param $arFields
     * @throws \Bitrix\Main\SystemException
     */
    public static function OnBeforeCrmDealAdd(&$arFields){
        $request = Application::getInstance()->getContext()->getRequest();
        $store_id = (int)$request->get("store_id");
        if($store_id>0){
            $arFields["UF_STORE"]=$store_id;
        }
    }
}