<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 2:50
 */

namespace Tn\Plan\Domain\Factory;

use Tn\Plan\Domain\Entity\Segment;
use InvalidArgumentException;

class FactorySegment
{
    /**
     * @param array $params
     * @return Segment
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $params)
    {
        $el = new Segment();
        if($params['id'])
            $el->setId($params['id']);
        if($params['name'])
            $el->setName($params['name']);
        return $el;
    }

    /**
     * @param array $records
     * @throws InvalidArgumentException
     * @return Segment[]
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