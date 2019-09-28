<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 14.08.2019
 * Time: 1:51
 */

namespace local\Domain\Repository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use local\Domain\Entity\CommCompany;
use local\Domain\Factory\CommCompanyFactory;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Exception;
use Bitrix\Main\ORM\Data\DataManager;

class CommCompanyRepository
{
    private $hlbl=HIGHLOAD_COMMCOMPANY;
    /**
     * RepositoryCertificate constructor.
     * @throws LoaderException
     */
    public function __construct()
    {
        Loader::includeModule("highloadblock");
    }

    /**
     * @return DataManager
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function getEntity(){
        $hlblock = HL\HighloadBlockTable::getById($this->hlbl)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        return $entity_data_class;
    }

    /**
     * @param CommCompany $el
     * @return array
     */
    private function getArParams($el){
        $return=[];
        if($el->getContactId())
            $return["UF_CONTACT_ID"]=$el->getContactId();
        if($el->getActivityId())
            $return["UF_ACTIVITY_ID"]=$el->getActivityId();
        if($el->getDescription())
            $return["UF_DESCRIPTION"]=$el->getDescription();
        if($el->getTitle())
            $return["UF_TITLE"]=$el->getTitle();
        return $return;
    }

    /**
     * @param array $arr
     * @return array
     */
    private function getParamToFabric($arr){
        $params=[];

        if(isset($arr['ID']))
            $params['id']=$arr['ID'];

        if(isset($arr['UF_ACTIVITY_ID']))
            $params['ActivityId']=$arr['UF_ACTIVITY_ID'];

        if(isset($arr['UF_CONTACT_ID']))
            $params['ContactId']=$arr['UF_CONTACT_ID'];

        if(isset($arr['UF_DESCRIPTION']))
            $params['Description']=$arr['UF_DESCRIPTION'];

        if(isset($arr['UF_TITLE']))
            $params['Title']=$arr['UF_TITLE'];

        return $params;
    }

    /**
     * @param CommCompany $el
     * @return string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function add(CommCompany $el){
        $entity_data_class=$this->getEntity();
        $result=$entity_data_class::add($this->getArParams($el));
        if (!$result->isSuccess())
            throw new Exception("Error adding item");
        return $result->getId();
    }

    /**
     * @param int $Id
     * @param CommCompany $el
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function update($Id,CommCompany $el){
        $arParams=$this->getArParams($el);
        $entity_data_class=$this->getEntity();
        $rsData = $entity_data_class::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => ["ID"=>$Id],
        ));
        if($arData = $rsData->Fetch()){
            $result = $entity_data_class::update($arData["ID"],$arParams);
            if (!$result->isSuccess())
                throw new Exception("Item update error");
        }else{
            throw new Exception("Item not found");
        }
        return true;
    }

    /**
     * @param int $Id
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function Delete($Id){
        $entity_data_class=$this->getEntity();
        $rsData = $entity_data_class::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => ["ID"=>$Id],
        ));
        if($arData = $rsData->Fetch()) {
            $result = $entity_data_class::delete($arData["ID"]);
            if (!$result->isSuccess())
                throw new Exception("Item delete error");
        }else{
            throw new Exception("Item not found");
        }
    }

    /**
     * @param int $Id
     * @return CommCompany
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function GetById($Id){
        $entity_data_class=$this->getEntity();
        $rsData = $entity_data_class::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "limit" => 1,
            "filter" => ["ID"=>$Id],
        ));
        if($arData = $rsData->Fetch()) {
            $param=$this->getParamToFabric($arData);
            return CommCompanyFactory::createFromArray($param);
        }else{
            throw new Exception("Item not found");
        }
    }

    /**
     * @param array $params
     * @return CommCompany[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function GetList($params=[]){
        $entity_data_class=$this->getEntity();
        $rsData = $entity_data_class::getList($params);
        $res=[];
        while($arData = $rsData->Fetch()) {
            $param=$this->getParamToFabric($arData);
            $res[]=$param;
        }
        return CommCompanyFactory::createFromCollection($res);
    }
}