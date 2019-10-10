<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 2:44
 */

namespace Tn\Plan\Service;

use Tn\Plan\Domain\Repository\RepositoryManager;
use Tn\Plan\Domain\Repository\RepositoryManagerFact;
use Tn\Plan\Domain\Entity\ManagerFact;
use Tn\Plan\Domain\Repository\RepositoryManagerPlan;
use Tn\Plan\Domain\Entity\ManagerPlan;

class ServiceManager
{
    /**
     * @param int $departamentId
     * @return \Tn\Plan\Domain\Entity\Manager[]
     * @throws \Bitrix\Main\LoaderException
     */
    public static function get($departamentId=0){
        $RepositoryYear=new RepositoryManager();
        return $list=$RepositoryYear->GetList($departamentId);
    }

    /**
     * @param int $ManagerId
     * @param int $SegmentId
     * @param int $YearId
     * @param float $Revenue
     * @param float $Akb
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function setManagerFactAsYear($ManagerId, $SegmentId, $YearId,  $Revenue, $Akb){
        $Repository=new RepositoryManagerFact();
        $Manager=new ManagerFact();
        $Manager->setManagerId($ManagerId);
        $Manager->setSegmentId($SegmentId);
        $Manager->setYearId($YearId);
        $Manager->setRevenue($Revenue);
        $Manager->setAkb($Akb);
        $list=$Repository->GetList([
            "filter"=>[
                "UF_MANAGER_ID"=>$ManagerId,
                "UF_SEGMENT_ID"=>$SegmentId,
                "UF_YEAR_ID"=>$YearId,
            ],
            'limit'=>1
        ]);
        if(count($list)==1){
            $Manager->setId($list[0]->getId());
            $Repository->update($Manager);
        }else
            $Repository->add($Manager);
    }

    /**
     * @param int $ManagerId
     * @param int $SegmentId
     * @param float $Revenue
     * @param float $Akb
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function setManagerFact($ManagerId, $SegmentId, $Revenue, $Akb){
        $YearId=ServiceYear::getYearForFact();
        self::setManagerFactAsYear($ManagerId, $SegmentId, $YearId,  $Revenue, $Akb);
    }

    /**
     * @param int $ManagerId
     * @param int $SegmentId
     * @param int $YearId
     * @param float $Revenue
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function setManagerPlanAsYear($ManagerId, $SegmentId, $YearId,  $Revenue){
        $Repository=new RepositoryManagerPlan();
        $Manager=new ManagerPlan();
        $Manager->setManagerId($ManagerId);
        $Manager->setSegmentId($SegmentId);
        $Manager->setYearId($YearId);
        $Manager->setRevenue($Revenue);
        $list=$Repository->GetList([
            "filter"=>[
                "UF_MANAGER_ID"=>$ManagerId,
                "UF_SEGMENT_ID"=>$SegmentId,
                "UF_YEAR_ID"=>$YearId,
            ],
            'limit'=>1
        ]);
        if(count($list)==1){
            $Manager->setId($list[0]->getId());
            $Repository->update($Manager);
        }else
            $Repository->add($Manager);
    }

    /**
     * @param int $ManagerId
     * @param int $SegmentId
     * @param float $Revenue
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function setManagerPlan($ManagerId, $SegmentId,  $Revenue){
        $YearId=ServiceYear::getYearForThePlan();
        self::setManagerPlanAsYear($ManagerId, $SegmentId, $YearId,  $Revenue);
    }

    /**
     * @param array $ManagerId
     * @param array $SegmentId
     * @param int $YearId
     * @return ManagerFact[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getManagerFactAsYear($ManagerId=[], $SegmentId=[], $YearId){
        $Repository=new RepositoryManagerFact();
        $filter=["UF_YEAR_ID"=>$YearId];
        if(count($ManagerId)>0)
            $filter["UF_MANAGER_ID"]=$ManagerId;
        if(count($SegmentId)>0)
            $filter["UF_SEGMENT_ID"]=$SegmentId;
        $list=$Repository->GetList([
            "filter"=>$filter,
        ]);
        return $list;
    }

    /**
     * @param array $ManagerId
     * @param array $SegmentId
     * @return ManagerFact[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getManagerFact($ManagerId=[], $SegmentId=[]){
        $YearId=ServiceYear::getYearForFact();
        return self::getManagerFactAsYear($ManagerId, $SegmentId, $YearId);
    }

    /**
     * @param array $ManagerId
     * @param array $SegmentId
     * @param int $YearId
     * @return ManagerPlan[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getManagerPlanAsYear($ManagerId=[], $SegmentId=[], $YearId){
        $Repository=new RepositoryManagerPlan();
        $filter=["UF_YEAR_ID"=>$YearId];
        if(count($ManagerId)>0)
            $filter["UF_MANAGER_ID"]=$ManagerId;
        if(count($SegmentId)>0)
            $filter["UF_SEGMENT_ID"]=$SegmentId;
        $list=$Repository->GetList([
            "filter"=>$filter,
        ]);
        return $list;
    }

    /**
     * @param array $ManagerId
     * @param array $SegmentId
     * @return ManagerPlan[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getManagerPlan($ManagerId=[], $SegmentId=[]){
        $YearId=ServiceYear::getYearForThePlan();
        return self::getManagerPlanAsYear($ManagerId, $SegmentId, $YearId);
    }
}