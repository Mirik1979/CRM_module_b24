<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 2:51
 */

namespace Tn\Plan\Domain\Repository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Tn\Plan\Domain\Entity\Segment;
use Tn\Plan\Domain\Factory\FactorySegment;
use CIBlockElement;

class RepositorySegment
{
    private $IBLOCK=IBLOCK_PLANNING_SEGMENTS;
    /**
     * RepositoryCertificate constructor.
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule("iblock");
    }

    /**
     * @return Segment[]
     */
    public function GetList(){
        $Bres=[];
        $res = CIBlockElement::GetList(
            ['SORT' => 'asc'],
            [
                "IBLOCK_ID"=>$this->IBLOCK,
                "ACTIVE"=>"Y",
            ],
            false,
            false,
            $this->getSelect()
        );
        while($ob=$res->GetNext()){
            $param=$this->getParamToFabric($ob);
            $Bres[]=$param;
        }
        return FactorySegment::createFromCollection($Bres);
    }

    /**
     * @return array
     */
    private function getSelect(){
        return [
            "ID",
            "IBLOCK_ID",
            "IBLOCK_TYPE",
            "ACTIVE",
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