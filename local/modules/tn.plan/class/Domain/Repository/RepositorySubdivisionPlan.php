<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 3:13
 */

namespace Tn\Plan\Domain\Repository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Tn\Plan\Domain\Entity\SubdivisionPlan;
use Tn\Plan\Domain\Factory\FactorySubdivisionPlan;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use InvalidArgumentException;

class RepositorySubdivisionPlan
{
    private $hlbl=HIGHLOAD_TNPLANSUBDIVISIONPLAN;
    /**
     * RepositoryCertificate constructor.
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule("highloadblock");
    }

    /**
     * @param SubdivisionPlan $el
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function add(SubdivisionPlan $el){
        $arParams=$this->getArParams($el);
        $entity_data_class = $this->getEntityDataClass();
        $result=$entity_data_class::add($arParams);
        if (!$result->isSuccess()) {
            $errors = $result->getErrorMessages();
            throw new InvalidArgumentException($errors);
        } else {
            $Id = $result->getId();
        }
        return $Id;
    }

    /**
     * @param SubdivisionPlan $el
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function update(SubdivisionPlan $el){
        $arParams=$this->getArParams($el);
        $entity_data_class = $this->getEntityDataClass();
        $result=$entity_data_class::update($el->getId(),$arParams);
        if (!$result->isSuccess()) {
            $errors = $result->getErrorMessages();
            throw new InvalidArgumentException($errors);
        }
    }

    /**
     * @param int $Id
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function Delete($Id){
        $entity_data_class = $this->getEntityDataClass();
        $entity_data_class::Delete($Id);
    }

    /**
     * @param string $Id
     * @return bool|SubdivisionPlan
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function GetById($Id){
        $list=$this->GetList([
            "limit"=>1,
            "filter" => ["ID"=>$Id],
        ]);
        if(count($list)==1)
            return $list[0];
        return false;
    }

    /**
     * @param array $param
     * @return SubdivisionPlan[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function GetList($param=[]){
        if(!$param["cache"])
            $param["cache"]=["ttl"=>3600];
        $entity_data_class = $this->getEntityDataClass();
        $rsEnum = $entity_data_class::getList($param);
        while($arEnum = $rsEnum->Fetch())
        {
            $param=$this->getParamToFabric($arEnum);
            $Bres[]=$param;
        }
        return FactorySubdivisionPlan::createFromCollection($Bres);
    }

    /**
     * @return \Bitrix\Main\ORM\Data\DataManager
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getEntityDataClass(){
        $hlblock = HL\HighloadBlockTable::getById($this->hlbl)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
    }

    /**
     * @param array $arr
     * @return array
     */
    private function getParamToFabric($arr){
        $params=[];

        if($arr['ID'])
            $params['id']=$arr['ID'];

        if($arr['UF_UNIT_ID'])
            $params['unit_id']=$arr['unit_id'];

        if($arr['UF_SEGMENT_ID'])
            $params['segment_id']=$arr['UF_SEGMENT_ID'];

        if($arr['UF_YEAR_ID'])
            $params['year_id']=$arr['UF_YEAR_ID'];

        if($arr['UF_REVENUE'])
            $params['revenue']=$arr['UF_REVENUE'];

        return $params;
    }

    /**
     * @param SubdivisionPlan $el
     * @return array
     */
    private function getArParams($el){
        $arParams=[];
        if($el->getUnitId())
            $arParams['UF_UNIT_ID']=$el->getUnitId();
        if($el->getSegmentId())
            $arParams['UF_SEGMENT_ID']=$el->getSegmentId();
        if($el->getYearId())
            $arParams['UF_YEAR_ID']=$el->getYearId();
        if($el->getRevenue())
            $arParams['UF_REVENUE']=$el->getRevenue();
        return $arParams;
    }
}