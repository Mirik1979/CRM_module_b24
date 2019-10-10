<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 3:05
 */

namespace Tn\Plan\Service;

use Tn\Plan\Domain\Repository\RepositorySegment;

class ServiceSegment
{
    /**
     * @return \Tn\Plan\Domain\Entity\Segment[]
     * @throws \Bitrix\Main\LoaderException
     */
    public static function get(){
        $RepositoryYear=new RepositorySegment();
        return $list=$RepositoryYear->GetList();
    }
}