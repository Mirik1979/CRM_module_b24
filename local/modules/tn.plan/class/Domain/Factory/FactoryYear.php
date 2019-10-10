<?php

namespace Tn\Plan\Domain\Factory;

use Tn\Plan\Domain\Entity\Year;
use InvalidArgumentException;

class FactoryYear
{
    /**
     * @param array $params
     * @return Year
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $params)
    {
        $el = new Year();
        if($params['id'])
            $el->setId($params['id']);
        if($params['year'])
            $el->setYear($params['year']);
        return $el;
    }

    /**
     * @param array $records
     * @throws InvalidArgumentException
     * @return Year[]
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