<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 09.10.2019
 * Time: 0:06
 */

namespace Tn\Plan\Service;


use Tn\Plan\Domain\Repository\RepositorySubdivisionPlan;
use Tn\Plan\Domain\Entity\SubdivisionPlan;

class ServiceSubdivisionPlan
{
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
    public static function setAsYear($UnitId, $SegmentId, $YearId,  $Revenue){
        $Repository=new RepositorySubdivisionPlan();
        $Manager=new SubdivisionPlan();
        $Manager->setUnitId($UnitId);
        $Manager->setSegmentId($SegmentId);
        $Manager->setYearId($YearId);
        $Manager->setRevenue($Revenue);
        $list=$Repository->GetList([
            "filter"=>[
                "UF_UNIT_ID"=>$UnitId,
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
    public static function set($UnitId, $SegmentId,  $Revenue){
        $YearId=ServiceYear::getYearForThePlan();
        self::setAsYear($UnitId, $SegmentId, $YearId,  $Revenue);
    }

    /**
     * @param array $ManagerId
     * @param array $SegmentId
     * @param int $YearId
     * @return SubdivisionPlan[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getAsYear($UnitId=[], $SegmentId=[], $YearId){
        $Repository=new RepositorySubdivisionPlan();
        $filter=["UF_YEAR_ID"=>$YearId];
        if(count($UnitId)>0)
            $filter["UF_UNIT_ID"]=$UnitId;
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
     * @return SubdivisionPlan[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function get($UnitId=[], $SegmentId=[]){
        $YearId=ServiceYear::getYearForThePlan();
        return self::getAsYear($UnitId, $SegmentId, $YearId);
    }
}