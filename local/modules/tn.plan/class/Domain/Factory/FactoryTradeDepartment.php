<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 1:52
 */

namespace Tn\Plan\Domain\Factory;

use Tn\Plan\Domain\Entity\TradeDepartment;
use InvalidArgumentException;

class FactoryTradeDepartment
{
    /**
     * @param array $params
     * @return TradeDepartment
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $params)
    {
        $el = new TradeDepartment();
        if($params['id'])
            $el->setId($params['id']);
        if($params['name'])
            $el->setName($params['name']);
        return $el;
    }

    /**
     * @param array $records
     * @throws InvalidArgumentException
     * @return TradeDepartment[]
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