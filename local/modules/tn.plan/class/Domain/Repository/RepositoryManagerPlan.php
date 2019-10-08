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
use Tn\Plan\Domain\Entity\ManagerPlan;
use Tn\Plan\Domain\Factory\FactoryManagerPlan;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use InvalidArgumentException;

class RepositoryManagerPlan
{
    private $hlbl=HIGHLOAD_TNPLANMANAGERPLAN;
    /**
     * RepositoryCertificate constructor.
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule("highloadblock");
    }

    /**
     * @param ManagerPlan $el
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function add(ManagerPlan $el){
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
     * @param ManagerPlan $el
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function update(ManagerPlan $el){
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
     * @return bool|ManagerPlan
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
     * @return ManagerPlan[]
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
        return FactoryManagerPlan::createFromCollection($Bres);
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

        if($arr['UF_MANAGER_ID'])
            $params['manager_id']=$arr['UF_MANAGER_ID'];

        if($arr['UF_SEGMENT_ID'])
            $params['segment_id']=$arr['UF_SEGMENT_ID'];

        if($arr['UF_YEAR_ID'])
            $params['year_id']=$arr['UF_YEAR_ID'];

        if($arr['UF_REVENUE'])
            $params['revenue']=$arr['UF_REVENUE'];

        return $params;
    }

    /**
     * @param ManagerPlan $el
     * @return array
     */
    private function getArParams($el){
        $arParams=[];
        if($el->getManagerId())
            $arParams['UF_MANAGER_ID']=$el->getManagerId();
        if($el->getSegmentId())
            $arParams['UF_SEGMENT_ID']=$el->getSegmentId();
        if($el->getYearId())
            $arParams['UF_YEAR_ID']=$el->getYearId();
        if($el->getRevenue())
            $arParams['UF_REVENUE']=$el->getRevenue();
        return $arParams;
    }
}