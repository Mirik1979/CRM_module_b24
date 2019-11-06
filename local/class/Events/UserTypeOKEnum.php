<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 03.11.2019
 * Time: 12:17
 */

namespace local\Events;


class UserTypeOKEnum extends \CDBResult
{
    function GetTreeList($IBLOCK_ID, $ACTIVE_FILTER="N")
    {
        $rs = false;
        if(\CModule::IncludeModule('iblock'))
        {
            $arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID);
            if($ACTIVE_FILTER === "Y")
                $arFilter["GLOBAL_ACTIVE"] = "Y";

            $arFilter["SECTION_ID"]=self::GetParent($IBLOCK_ID);

            $rs = \CIBlockSection::GetList(
                Array("left_margin"=>"asc"),
                $arFilter,
                false,
                array("ID", "DEPTH_LEVEL", "NAME", "SORT", "XML_ID", "ACTIVE", "IBLOCK_SECTION_ID")
            );
            if($rs)
            {
                $rs = new self($rs);
            }
        }
        return $rs;
    }

    public function GetParent($IBLOCK_ID){
        $res = \CIBlockSection::GetList(
            ['left_margin' => 'asc'],
            [
                "IBLOCK_ID"=>$IBLOCK_ID,
                "ACTIVE"=>"Y",
                "NAME"=>"Операционные компании",
            ],
            false,
            $this->getSelect()
        );
        if($ob=$res->GetNext())
            return $ob["ID"];
        return false;
    }

    /**
     * @return array
     */
    private function getSelect(){
        return [
            "ID",
            "IBLOCK_ID",
            "IBLOCK_TYPE",
            "XML_ID",
            "ACTIVE",
            "IBLOCK_SECTION_ID",
            "NAME",
        ];
    }

    function GetNext($bTextHtmlAuto=true, $use_tilda=true)
    {
        $r = parent::GetNext($bTextHtmlAuto, $use_tilda);
        if($r)
            $r["VALUE"] = $r["NAME"];
        return $r;
    }
}