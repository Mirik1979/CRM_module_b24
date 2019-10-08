<?php

namespace local\Helpers;

use Bitrix\Main\Application as App;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Data\Cache;

class SetConst{

    public static function init()
    {
        self::IsPrint();
        self::IsAjax();
        self::IsPost();
        self::IsMainPage();
        self::IblockConst();
        self::IblockPropConst();
        //self::FormConst();
        self::HighloadIblockConst();
        self::customConst();
    }

    private static function customConst(){

    }

    private static function IsPrint(){
        if (!defined("IS_PRINT"))
        {
            $request = App::getInstance()->getContext()->getRequest();
            $print = htmlspecialchars($request->getQuery("print"));
            if($print=="Y" ||$print=="y")
                define("IS_PRINT", true);
            else
                define("IS_PRINT", false);
        }
    }

    private static function IsMainPage(){
        if (!defined("IS_MAIN_PAGE"))
        {
            global $APPLICATION;
            if($APPLICATION->GetCurPage(true)==SITE_DIR.'index.php')
                define("IS_MAIN_PAGE", true);
            else
                define("IS_MAIN_PAGE", false);
        }
    }

    private static function IsAjax(){
        $ajax=false;
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            $ajax=true;
        if (!defined("IS_AJAX"))
                define("IS_AJAX", $ajax);
    }

    private static function IsPost(){
        if (!defined("IS_POST"))
        {
            $request = App::getInstance()->getContext()->getRequest();
            if($request->isPost())
                define("IS_POST", true);
            else
                define("IS_POST", false);
        }
    }

    private static function IblockConst(){
        /**
         * Определение констант кодов инфоблоков
         * Правило определения: все небуквенные символы заменяются на "_", получившаяся строка переводится в верхний регистр.
         * Добавляеться префикс iblock_
         */
        $cache = Cache::createInstance();
        $cacheTime = 30*60;
        $cacheId = 'IblockConst';
        $cacheDir = 'iblock_const';
        $arResult = array();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $arResult = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            if (Loader::includeModule('iblock'))
            {
                $cIBlock = \CIBlock::GetList([],["CHECK_PERMISSIONS" => "N"]);
                while($items = $cIBlock->Fetch())
                {
                    $items['CODE']=trim($items['CODE']);
                    $items['CODE']=mb_convert_case($items['CODE'], MB_CASE_LOWER);
                    //echo "<pre>";print_r($items['NAME']);echo "</pre>";
                    if(strlen($items['CODE'])>0){
                        $code = "iblock_".trim($items['CODE']);
                        $id = (int) $items['ID'];
                        $arResult[$id]=$code;
                    }
                }
            }
            $cache->endDataCache($arResult);
        }

        foreach ($arResult as $id=>$code)
            self::initConst($id,$code);
    }

    private static function IblockPropConst(){
        /**
         * Определение констант кодов инфоблоков
         * Правило определения: все небуквенные символы заменяются на "_", получившаяся строка переводится в верхний регистр.
         * Добавляеться префикс iblock_
         */
        $cache = Cache::createInstance();
        $cacheTime = 30*60;
        $cacheId = 'IblockPropertyConst';
        $cacheDir = 'iblock_property_const';
        $arResult = array();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $arResult = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            if (Loader::includeModule('iblock'))
            {
                $arIBLOCK=array();
                $cIBlock = \CIBlock::GetList();
                while($items = $cIBlock->Fetch())
                {
                    if(strlen($items['CODE'])>0){
                        $id = (int) $items['ID'];
                        $arIBLOCK[$id]=$items['CODE'];
                    }
                }
                $properties = \CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y"));
                while ($prop_fields = $properties->GetNext())
                {
                    if(strlen($prop_fields['CODE'])>0 && $arIBLOCK[$prop_fields["IBLOCK_ID"]])
                    {
                        $code = "iblock_".trim($arIBLOCK[$prop_fields["IBLOCK_ID"]])."_property_".trim($prop_fields['CODE']);
                        $id = (int) $prop_fields['ID'];
                        $arResult[$id]=$code;
                    }
                }
            }
            $cache->endDataCache($arResult);
        }
        foreach ($arResult as $id=>$code)
            self::initConst($id,$code);
    }

    private static function HighloadIblockConst(){
        /**
         * Определение констант кодов Highload инфоблоков
         * Правило определения: все небуквенные символы заменяются на "_", получившаяся строка переводится в верхний регистр.
         * Добавляеться префикс highload_
         */
        $cache = Cache::createInstance();
        $cacheTime = 30*60;
        $cacheId = 'HighloadIblockConst';
        $cacheDir = 'highload_const';
        $arResult = array();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $arResult = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            if (Loader::includeModule('highloadblock'))
            {
                $hlblock = HL\HighloadBlockTable::getList();
                while($reshlblock=$hlblock->fetch())
                {
                    $code = "highload_".trim($reshlblock['NAME']);
                    $id = (int) $reshlblock['ID'];
                    $arResult[$id]=$code;
                }
            }
            $cache->endDataCache($arResult);
        }
        foreach ($arResult as $id=>$code)
            self::initConst($id,$code);
    }

    private static function FormConst(){
        /**
         * Определение констант кодов форм
         * Правило определения: все небуквенные символы заменяются на "_", получившаяся строка переводится в верхний регистр.
         * Добавляеться префикс form_
         */
        $cache = Cache::createInstance();
        $cacheTime = 30*60;
        $cacheId = 'FormConst';
        $cacheDir = 'form_const';
        $arResult = array();
        if ($cache->initCache($cacheTime, $cacheId, $cacheDir)) {
            $arResult = $cache->getVars();
        } elseif ($cache->startDataCache()) {

            if (Loader::includeModule('form'))
            {
                $rsForms = \CForm::GetList();
                while ($arForm = $rsForms->Fetch())
                {
                    $code = "form_".trim($arForm['VARNAME']);
                    $id = (int) $arForm['ID'];
                    $arResult[$id]=$code;
                }
            }
            $cache->endDataCache($arResult);
        }
        foreach ($arResult as $id=>$code)
            self::initConst($id,$code);
    }

    private static function initConst($id,$code)
    {
        if (!empty($code))
        {
            $const = preg_replace('/\W/', '_', $code);
            $const = mb_convert_case($const, MB_CASE_UPPER);
            if (!defined($const))
            {
                define($const, $id);
            }
        }
    }

}