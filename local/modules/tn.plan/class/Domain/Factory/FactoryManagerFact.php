<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 3:10
 */

namespace Tn\Plan\Domain\Factory;

use Tn\Plan\Domain\Entity\ManagerFact;
use InvalidArgumentException;

class FactoryManagerFact
{
    /**
     * @param array $params
     * @return ManagerFact
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $params)
    {
        $el = new ManagerFact();
        if($params['id'])
            $el->setId($params['id']);
        if($params['manager_id'])
            $el->setManagerId($params['manager_id']);
        if($params['segment_id'])
            $el->setSegmentId($params['segment_id']);
        if($params['year_id'])
            $el->setYearId($params['year_id']);
        if($params['revenue'])
            $el->setRevenue($params['revenue']);
        if($params['akb'])
            $el->setAkb($params['akb']);
        return $el;
    }

    /**
     * @param array $records
     * @throws InvalidArgumentException
     * @return ManagerFact[]
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