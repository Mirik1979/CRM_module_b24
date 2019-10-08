<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 08.10.2019
 * Time: 2:08
 */

namespace Tn\Plan\Service;

use Tn\Plan\Domain\Repository\RepositoryTradeDepartment;

class ServiceTradeDepartment
{
    /**
     * @return \Tn\Plan\Domain\Entity\TradeDepartment[]
     * @throws \Bitrix\Main\LoaderException
     */
    public static function get($id=[]){
        $RepositoryYear=new RepositoryTradeDepartment();
        return $list=$RepositoryYear->GetList($id);
    }
}