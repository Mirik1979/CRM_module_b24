<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.11.2019
 * Time: 0:32
 */

namespace local\Helpers;

use Bitrix\Main\Loader;
use CCrmDeal;
use CUserFieldEnum;
use Wcomm\CrmStores\Entity\StoreTable;
use Bitrix\Highloadblock as HL;
use CUserTypeEntity;
use CUser;
use CIBlockSection;

class PotentialsHelper
{

    const codePotential='UF_CRM_1570712675';
    const codeType='UF_CRM_1572591277';

    public static function getPotentials($DealId){

        Loader::includeModule('crm');
        Loader::includeModule('iblock');
        Loader::includeModule("highloadblock");
        Loader::includeModule("wcomm.crmstores");
        Loader::includeModule("intranet");

        $Potential=[];

        $res = CCrmDeal::GetList([],[
            'CHECK_PERMISSIONS'=> 'N',
            'ID'=>$DealId,
        ],[],1);

        if($arr=$res->GetNext()){

            $Potential['opportunity'] = $arr['OPPORTUNITY'];
            $Potential['store'] = $arr['UF_STORE'];

            $Potential['IdPotential']=(int)$arr[self::codePotential];

            if($Potential['IdPotential']>0 && $Potential['store']){
                $Potential['NamePotential']=self::getNamePotential($Potential['IdPotential']);

                $Potential['store_array']=StoreTable::getListEx(['filter'=>['ID'=>$Potential['store']]]);
                if(count($Potential['store_array'])>0)
                    $Potential['store_array']=$Potential['store_array'][0];

                $Potential['v']=self::getV($Potential['IdPotential'],$Potential['store_array']);
                $Potential['k']=self::getK($Potential['IdPotential'],$Potential['store_array']);
                $Potential['p']=$Potential['v']*$Potential['k'];
            }

        }

        return $Potential;

    }

    private static function getSelect(){
        return [
            "ID",
            "IBLOCK_ID",
            "IBLOCK_TYPE",
            "XML_ID",
            "ACTIVE",
            "IBLOCK_SECTION_ID",
            "NAME",
            "LEFT_MARGIN",
            "RIGHT_MARGIN",
        ];
    }

    private static function getSections($sections){
        $arr=$sections;
        $result=[];
        foreach ($sections as $section){
            $filter=[
                "IBLOCK_ID"=>IBLOCK_DEPARTMENTS,
                "ACTIVE"=>"Y",
            ];
            $filter["ID"]=$section;

            $res = CIBlockSection::GetList(
                ['left_margin' => 'asc'],
                $filter,
                false,
                self::getSelect()
            );
            if($ob=$res->GetNext()){
                $res2 = CIBlockSection::GetList(
                    ['left_margin' => 'asc'],
                    [
                        "IBLOCK_ID"=>IBLOCK_DEPARTMENTS,
                        "ACTIVE"=>"Y",
                        'LEFT_MARGIN' => $ob["LEFT_MARGIN"],
                        'RIGHT_MARGIN' => $ob["RIGHT_MARGIN"],
                    ],
                    false,
                    self::getSelect()
                );
                while($ob2=$res2->GetNext())
                {
                    if (!in_array($ob2["ID"], $arr)) {
                        $arr[] = $ob2["ID"];
                    }
                }
            }

            $nav = CIBlockSection::GetNavChain(false, $section);
            while($item=$nav->GetNext()){
                if (!in_array($item["ID"], $arr)) {
                    $arr[] = $item["ID"];
                }
            }
        }

        $res = CIBlockSection::GetList(
            ['left_margin' => 'asc'],
            [
                "IBLOCK_ID"=>IBLOCK_DEPARTMENTS,
                "ACTIVE"=>"Y",
                "NAME"=>"Операционные компании",
            ],
            false,
            self::getSelect()
        );
        if($ob=$res->GetNext())
        {
            $res2 = CIBlockSection::GetList(
                ['left_margin' => 'asc'],
                [
                    "IBLOCK_ID"=>IBLOCK_DEPARTMENTS,
                    "ACTIVE"=>"Y",
                    "SECTION_ID"=>$ob["ID"],
                ],
                false,
                self::getSelect()
            );
            while($ob2=$res2->GetNext()){
                if(in_array($ob2["ID"],$arr))
                    $result[]=$ob2["ID"];
            }
        }

        return $result;
    }

    private static function getK($Constructive,$Store){
        $k=0;
        $filter=[
            "UF_CONSTRUCTIVE"=>$Constructive,
        ];

        if($Store["ASSIGNED_BY_ID"]){
            $u=\CIntranetUtils::GetUserDepartments($Store["ASSIGNED_BY_ID"]);
            $section=self::getSections($u);
            $filter["UF_OK_ID"]=$section;
        }

        if($Store[self::codeType]){
            $filter["UF_TYPE_STORES"]=$Store[self::codeType];
        }

        $hlblock = HL\HighloadBlockTable::getById(HIGHLOAD_POTENTIALCALCULATIONCOEFFICIENT)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $rsData = $entity_data_class::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => $filter,
        ));

        if($arData = $rsData->Fetch()){
            return $arData["UF_K"];
        }

        return $k;
    }

    private static function getV($id,$Store){
        $hlblock = HL\HighloadBlockTable::getById(HIGHLOAD_SOURCESCALCULATINGPOTENTIALS)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        $rsData = $entity_data_class::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => ["UF_CONSTRUCTIVE"=>$id],
        ));
        $k=0;
        if($arData = $rsData->Fetch()){
            if($arData["UF_INDICATOR_1"])
            {
                $ar_res = CUserTypeEntity::GetByID( $arData["UF_INDICATOR_1"]);
                if($ar_res){
                    $k=(float)$Store[$ar_res["FIELD_NAME"]];
                }
            }
            if($arData["UF_INDICATOR_2"])
            {
                $ar_res = CUserTypeEntity::GetByID( $arData["UF_INDICATOR_2"]);
                if($ar_res){
                    if($k>0)
                        $k=$k*(float)$Store[$ar_res["FIELD_NAME"]];
                    else
                        $k=(float)$Store[$ar_res["FIELD_NAME"]];
                }
            }
        }
        return $k;
    }

    private static function getNamePotential($id){
        $CUserFieldEnum=new CUserFieldEnum();
        $rsGender = $CUserFieldEnum->GetList(array(), array(
            "ID" => $id,
        ));
        if($arGender = $rsGender->GetNext())
            return $arGender["VALUE"];
        return '';
    }

}