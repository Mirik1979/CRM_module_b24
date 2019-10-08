<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 2:15
 */

namespace Tn\Plan\Domain\Repository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Tn\Plan\Domain\Entity\Manager;
use Tn\Plan\Domain\Factory\FactoryManager;
use CIBlockSection;
use CIntranetUtils;

class RepositoryManager
{
    private $IBLOCK=IBLOCK_DEPARTMENTS;

    /**
     * RepositoryCertificate constructor.
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule("intranet");
        Loader::includeModule("iblock");
    }

    /**
     * @param int $departamentId
     * @return Manager[]
     */
    public function GetList($departamentId=0){
        $Bres=[];
        $arDepartments=$this->GetParents($departamentId);
        $list=CIntranetUtils::getDepartmentEmployees($arDepartments, false, true, 'Y', null);
        while($user=$list->GetNext()){
            $param=$this->getParamToFabric($user);
            $Bres[]=$param;
        }
        return FactoryManager::createFromCollection($Bres);
    }

    /**
     * @param $id
     * @return array
     */
    public function GetParents($id){
        $result=[];
        $filter=[
            "IBLOCK_ID"=>$this->IBLOCK,
            "ACTIVE"=>"Y",
        ];
        if($id>0)
            $filter["ID"]=$id;
        else
            $filter["NAME"]="Операционные компании";
        $res = CIBlockSection::GetList(
            ['left_margin' => 'asc'],
            $filter,
            false,
            $this->getSelect()
        );
        if($ob=$res->GetNext()){
            $res2 = CIBlockSection::GetList(
                ['left_margin' => 'asc'],
                [
                    "IBLOCK_ID"=>$this->IBLOCK,
                    "ACTIVE"=>"Y",
                    'LEFT_MARGIN' => $ob["LEFT_MARGIN"],
                    'RIGHT_MARGIN' => $ob["RIGHT_MARGIN"],
                ],
                false,
                $this->getSelect()
            );
            while($ob2=$res2->GetNext())
                $result[]=$ob2["ID"];
        }
        return $result;
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
            "LEFT_MARGIN",
            "RIGHT_MARGIN",
        ];
    }

    /**
     * @param array $arr
     * @return array
     */
    private function getParamToFabric($arr){

        $params=[];

        if($arr['ID'])
            $params['id']=$arr['ID'];

        if($arr['NAME'])
            $params['name']=$arr['NAME'];

        if($arr['LAST_NAME'])
            $params['last_name']=$arr['LAST_NAME'];

        if($arr['SECOND_NAME'])
            $params['second_name']=$arr['SECOND_NAME'];

        return $params;
    }
}