<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 0:58
 */

namespace Tn\Plan\Domain\Repository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Tn\Plan\Domain\Entity\OperatingCompany;
use Tn\Plan\Domain\Factory\FactoryOperatingCompany;
use CIBlockSection;

class RepositoryOperatingCompany
{

    private $IBLOCK=IBLOCK_DEPARTMENTS;
    /**
     * RepositoryCertificate constructor.
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule("iblock");
    }

    /**
     * @return OperatingCompany[]
     */
    public function GetList(){
        $Bres=[];
        $res = CIBlockSection::GetList(
            ['left_margin' => 'asc'],
            [
                "IBLOCK_ID"=>$this->IBLOCK,
                "ACTIVE"=>"Y",
                "SECTION_ID"=>$this->GetParent(),
            ],
            false,
            $this->getSelect()
        );
        while($ob=$res->GetNext()){
            $param=$this->getParamToFabric($ob);
            $Bres[]=$param;
        }
        return FactoryOperatingCompany::createFromCollection($Bres);
    }

    /**
     * @return bool||int
     */
    public function GetParent(){
        $res = CIBlockSection::GetList(
            ['left_margin' => 'asc'],
            [
                "IBLOCK_ID"=>$this->IBLOCK,
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

        return $params;
    }

}