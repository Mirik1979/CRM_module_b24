<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 3:10
 */

namespace Tn\Plan\Domain\Factory;

use Tn\Plan\Domain\Entity\SubdivisionPlan;
use InvalidArgumentException;

class FactorySubdivisionPlan
{
    /**
     * @param array $params
     * @return SubdivisionPlan
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $params)
    {
        $el = new SubdivisionPlan();
        if($params['id'])
            $el->setId($params['id']);
        if($params['unit_id'])
            $el->setUnitId($params['unit_id']);
        if($params['segment_id'])
            $el->setSegmentId($params['segment_id']);
        if($params['year_id'])
            $el->setYearId($params['year_id']);
        if($params['revenue'])
            $el->setRevenue($params['revenue']);
        return $el;
    }

    /**
     * @param array $records
     * @throws InvalidArgumentException
     * @return SubdivisionPlan[]
     */
    public static function createFromCollection(array $records)
    {
        $output = [];
        array_map(function ($item) use (&$output) {
            $output[] = self::createFromArray($item);
        }, $records);
        return $output;
    }
}