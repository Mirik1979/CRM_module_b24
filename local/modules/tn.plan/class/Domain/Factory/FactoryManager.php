<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 2:13
 */

namespace Tn\Plan\Domain\Factory;

use Tn\Plan\Domain\Entity\Manager;
use InvalidArgumentException;

class FactoryManager
{
    /**
     * @param array $params
     * @return Manager
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $params)
    {
        $el = new Manager();
        if($params['id'])
            $el->setId($params['id']);
        if($params['name'])
            $el->setName($params['name']);
        if($params['last_name'])
            $el->setLastName($params['last_name']);
        if($params['second_name'])
            $el->setSecondName($params['second_name']);
        return $el;
    }

    /**
     * @param array $records
     * @throws InvalidArgumentException
     * @return Manager[]
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