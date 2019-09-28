<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 14.08.2019
 * Time: 1:50
 */

namespace local\Domain\Factory;

use local\Domain\Entity\CommCompany;
use InvalidArgumentException;

class CommCompanyFactory
{
    /**
     * @param array $params
     * @return CommCompany
     * @throws InvalidArgumentException
     */
    public static function createFromArray(array $params)
    {
        $el = new CommCompany();

        if($params['id'])
            $el->setId($params['id']);

        if($params['ContactId'])
            $el->setContactId($params['ContactId']);

        if($params['ActivityId'])
            $el->setActivityId($params['ActivityId']);

        if($params['Description'])
            $el->setDescription($params['Description']);

        if($params['Title'])
            $el->setTitle($params['Title']);

        return $el;
    }

    /**
     * @param array $records
     * @throws InvalidArgumentException
     * @return CommCompany[]
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