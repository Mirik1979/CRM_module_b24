<?php

namespace Tn\Plan\Domain\Repository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Tn\Plan\Domain\Entity\Year;
use Tn\Plan\Domain\Factory\FactoryYear;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use InvalidArgumentException;

class RepositoryYear
{
    private $hlbl=HIGHLOAD_TNPLANYEAR;
    /**
     * RepositoryCertificate constructor.
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule("highloadblock");
    }

    /**
     * @param Year $el
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function add(Year $el){
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
     * @param Year $el
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function update(Year $el){
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
     * @return bool|Year
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
     * @return Year[]
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
        return FactoryYear::createFromCollection($Bres);
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

        if($arr['UF_YEAR'])
            $params['year']=$arr['UF_YEAR'];

        return $params;
    }

    /**
     * @param Year $el
     * @return array
     */
    private function getArParams($el){
        $arParams=[];
        if($el->getYear())
            $arParams['UF_YEAR']=$el->getYear();
        return $arParams;
    }
}