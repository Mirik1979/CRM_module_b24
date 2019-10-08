<?php

namespace Tn\Plan\Service;

use Tn\Plan\Domain\Repository\RepositoryYear;
use Tn\Plan\Domain\Entity\Year;
use Bitrix\Main\Config\Option;

class ServiceYear
{

    const module_id="tn.plan";

    /**
     * @param string $Year
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function addYear($Year){
        if(strlen($Year)<=0)
            return;
        $RepositoryYear=new RepositoryYear();
        $list=$RepositoryYear->GetList(["=UF_YEAR"=>$Year,'limit'=>1]);
        if(count($list)==1)
            return;
        $EntityYear=new Year();
        $EntityYear->setYear($Year);
        $RepositoryYear->add($EntityYear);
    }

    /**
     * @param string $Year
     * @return bool|int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getAndAddYear($Year){
        if(strlen($Year)<=0)
            return false;
        $RepositoryYear=new RepositoryYear();
        $list=$RepositoryYear->GetList(["=UF_YEAR"=>$Year,'limit'=>1]);
        if(count($list)==1)
            return $list[0]->getId();
        $EntityYear=new Year();
        $EntityYear->setYear($Year);
        return $RepositoryYear->add($EntityYear);
    }

    /**
     * @return bool|int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getYearForThePlan(){
        $Year=Option::get(self::module_id, "year_for_the_plan");
        return self::getAndAddYear($Year);
    }

    /**
     * @return bool|int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getYearForFact(){
        $Year=Option::get(self::module_id, "year_for_fact");
        return self::getAndAddYear($Year);
    }

}