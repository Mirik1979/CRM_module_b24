<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 1:42
 */

namespace Tn\Plan\Service;

use Tn\Plan\Domain\Repository\RepositoryOperatingCompany;

class ServiceOperatingCompany
{
    /**
     * @return \Tn\Plan\Domain\Entity\OperatingCompany[]
     * @throws \Bitrix\Main\LoaderException
     */
    public static function get(){
        $RepositoryYear=new RepositoryOperatingCompany();
        return $list=$RepositoryYear->GetList();
    }
}