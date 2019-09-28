<?php
/**
 * Created by PhpStorm.
 * @author Alexander Danilin <danilin2010@yandex.ru>
 * Date: 09.09.2019
 * Time: 2:18
 */

namespace local\Crm\Activity;

use CCrmActivity;
use local\Domain\Repository\CommCompanyRepository;

class CCrmActivityAltLocal extends CCrmActivity
{

    public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array(), $arOptions = array())
    {
        $lb = new \CCrmEntityListBuilder(
            self::DB_TYPE,
            self::TABLE_NAME,
            self::TABLE_ALIAS,
            self::GetFields(),
            static::UF_ENTITY_TYPE,
            '',
            array('\local\Crm\Activity\CCrmActivityAltLocal', 'BuildPermSql'),
            array('\local\Crm\Activity\CCrmActivityAltLocal', '__AfterPrepareSql')
        );

        if(!is_array($arSelectFields))
        {
            $arSelectFields = array();
        }

        $result = $lb->Prepare($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields, $arOptions);
        return (is_object($result) && is_subclass_of($result, 'CAllDBResult'))
            ? new \CCrmActivityDbResult($result, $arSelectFields)
            : $result;
    }

    public static function PrepareBindingsFilterSql(&$arBindings, $tableAlias = '')
    {
        if(!is_array($arBindings))
        {
            return '';
        }

        $qty = count($arBindings);
        if($qty === 0)
        {
            return '';
        }

        $tableAlias = strval($tableAlias);
        if($tableAlias === '')
        {
            $tableAlias = CAllCrmActivity::TABLE_ALIAS;
        }

        $bindingTableName = self::BINDING_TABLE_NAME;
        $sql = '';

        if($qty === 1)
        {
            $binding = $arBindings[0];
            $ownerTypeID = isset($binding['OWNER_TYPE_ID']) ? intval($binding['OWNER_TYPE_ID']) : 0;
            if($ownerTypeID > 0)
            {
                $sql = "B.OWNER_TYPE_ID = {$ownerTypeID}";
                $ownerID = isset($binding['OWNER_ID']) ? intval($binding['OWNER_ID']) : 0;
                if($ownerID > 0)
                {
                    $sql .= " AND B.OWNER_ID = {$ownerID}";
                }
            }

            if($ownerTypeID==3 && $ownerID>0){
                $id=[];
                $CommCompanyRepository=new CommCompanyRepository();
                $list=$CommCompanyRepository->GetList([
                    "limit" => 100,
                    'filter'=>[
                        'UF_CONTACT_ID'=>$ownerID,
                    ],
                ]);
                foreach ($list as $el)
                    $id[]=$el->getActivityId();
                if(count($id)>0){
                    $id=implode(',',$id);
                    $sql="((B.OWNER_TYPE_ID = {$ownerTypeID} AND B.OWNER_ID = {$ownerID}) OR B.ACTIVITY_ID IN ({$id}))";
                }
            }

            return $sql !== '' ? "INNER JOIN {$bindingTableName} B ON B.ACTIVITY_ID = {$tableAlias}.ID AND {$sql}" : '';
        }
        else
        {
            foreach($arBindings as &$binding)
            {
                $ownerTypeID = isset($binding['OWNER_TYPE_ID']) ? intval($binding['OWNER_TYPE_ID']) : 0;
                if($ownerTypeID <= 0)
                {
                    continue;
                }

                $s = "B.OWNER_TYPE_ID = {$ownerTypeID}";
                $ownerID = isset($binding['OWNER_ID']) ? intval($binding['OWNER_ID']) : 0;
                if($ownerID > 0)
                {
                    $s .= " AND B.OWNER_ID = {$ownerID}";
                }

                if($sql !== '')
                {
                    $sql .= ' OR ';
                }

                $sql .= "({$s})";
            }
            unset($binding);
            return $sql !== '' ? "INNER JOIN {$bindingTableName} B ON B.ACTIVITY_ID = {$tableAlias}.ID AND ({$sql})" : '';
        }
    }

    public static function __AfterPrepareSql(/*CCrmEntityListBuilder*/ $sender, $arOrder, $arFilter, $arGroupBy, $arSelectFields)
    {
        $sqlData = array('FROM' => array(), 'WHERE' => array());
        if(isset($arFilter['SEARCH_CONTENT']) && $arFilter['SEARCH_CONTENT'] !== '')
        {
            $tableAlias = $sender->GetTableAlias();
            $queryWhere = new CSQLWhere();
            $queryWhere->SetFields(
                array(
                    'SEARCH_CONTENT' => array(
                        'FIELD_NAME' => "{$tableAlias}.SEARCH_CONTENT",
                        'FIELD_TYPE' => 'string',
                        'JOIN' => false
                    )
                )
            );
            $query = $queryWhere->GetQuery(
                Crm\Search\SearchEnvironment::prepareEntityFilter(
                    CCrmOwnerType::Activity,
                    array(
                        'SEARCH_CONTENT' => Crm\Search\SearchEnvironment::prepareSearchContent($arFilter['SEARCH_CONTENT'])
                    )
                )
            );
            if($query !== '')
            {
                $sqlData['WHERE'][] = $query;
            }
        }

        if(isset($arFilter['BINDINGS']))
        {
            $sql = self::PrepareBindingsFilterSql($arFilter['BINDINGS'], $sender->GetTableAlias());
            if($sql !== '')
            {
                $sqlData['FROM'][] = $sql;
            }
        }

        $result = array();
        if(!empty($sqlData['FROM']))
        {
            $result['FROM'] = implode(' ', $sqlData['FROM']);
        }
        if(!empty($sqlData['WHERE']))
        {
            $result['WHERE'] = implode(' AND ', $sqlData['WHERE']);
        }

        return !empty($result) ? $result : false;
    }
}