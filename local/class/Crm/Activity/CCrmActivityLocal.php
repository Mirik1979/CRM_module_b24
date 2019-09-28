<?php

namespace local\Crm\Activity;

use CAllCrmActivity;
use CCrmActivity;
use CCrmCompany;
use CCrmContact;

class CCrmActivityLocal extends CAllCrmActivity
{
    public static function FindContactCommunications($needle, $communicationType, $top = 50,$companyId=0)
    {
        //$companyId=0;
        $needle = trim($needle);
        $communicationType = trim($communicationType);
        $top = intval($top);

        if($needle === '')
        {
            //return array();
        }

        global $DB;
        $fieldMultiTableName = CCrmActivity::FIELD_MULTI_TABLE_NAME;
        $contactTableName = CCrmContact::TABLE_NAME;
        $companyTableName = CCrmCompany::TABLE_NAME;
        $result = array();

        $needleSql = $DB->ForSqlLike($needle.'%');
        $firstNameSql = '';
        $lastNameSql = '';

        $nameParts = array();
        \Bitrix\Crm\Format\PersonNameFormatter::tryParseName(
            $needle,
            \Bitrix\Crm\Format\PersonNameFormatter::getFormatID(),
            $nameParts
        );

        if(isset($nameParts['NAME'])
            && $nameParts['NAME'] !== ''
            && isset($nameParts['LAST_NAME'])
            && $nameParts['LAST_NAME'] !== ''
        )
        {
            $firstNameSql = $DB->ForSqlLike($nameParts['NAME'].'%');
            $lastNameSql = $DB->ForSqlLike($nameParts['LAST_NAME'].'%');
        }

        if($communicationType === '')
        {
            if($firstNameSql !== '' && $lastNameSql !== '')
            {
                $sql  = "SELECT C.ID AS ELEMENT_ID, '' AS VALUE_TYPE, '' AS VALUE, C.NAME, C.SECOND_NAME, C.LAST_NAME, C.HONORIFIC, C.PHOTO, CO.TITLE COMPANY_TITLE FROM {$contactTableName} C LEFT OUTER JOIN {$companyTableName} CO ON C.COMPANY_ID = CO.ID LEFT JOIN b_uts_crm_contact CA ON C.ID = CA.VALUE_ID WHERE C.NAME LIKE '{$firstNameSql}' AND C.LAST_NAME LIKE '{$lastNameSql}' AND (NOT CA.UF_CRM_1566294330350 IS NOT NULL OR NOT CA.UF_CRM_1566294330350=1)";
            }
            else
            {
                $sql  = "SELECT C.ID AS ELEMENT_ID, '' AS VALUE_TYPE, '' AS VALUE, C.NAME, C.SECOND_NAME, C.LAST_NAME, C.HONORIFIC, C.PHOTO, CO.TITLE COMPANY_TITLE FROM {$contactTableName} C LEFT OUTER JOIN {$companyTableName} CO ON C.COMPANY_ID = CO.ID LEFT JOIN b_uts_crm_contact CA ON C.ID = CA.VALUE_ID WHERE (C.NAME LIKE '{$needleSql}' OR C.LAST_NAME LIKE '{$needleSql}') AND (NOT CA.UF_CRM_1566294330350 IS NOT NULL OR NOT CA.UF_CRM_1566294330350=1)";
            }

            if($companyId>0){
                $sql.=" AND (C.COMPANY_ID=$companyId OR C.ID IN (SELECT F.CONTACT_ID  FROM b_crm_contact_company F where F.COMPANY_ID=$companyId))";
            }

            //print_r($sql);

            if($top > 0)
            {
                $sql = $DB->TopSql($sql, $top);
            }

            $dbRes = $DB->Query(
                $sql,
                false,
                'FILE: '.__FILE__.'<br /> LINE: '.__LINE__
            );

            while($arRes = $dbRes->Fetch())
            {
                $result[] = CAllCrmActivity::ReadContactCommunication($arRes, $communicationType);
            }

            return $result;
        }

        //Search by Name
        if($firstNameSql !== '' && $lastNameSql !== '')
        {
            $sql  = "SELECT FM.ELEMENT_ID, FM.VALUE_TYPE, FM.VALUE, C.NAME, C.SECOND_NAME, C.LAST_NAME, C.HONORIFIC, C.PHOTO, CO.TITLE COMPANY_TITLE FROM {$fieldMultiTableName} FM INNER JOIN {$contactTableName} C ON FM.ELEMENT_ID = C.ID AND FM.ENTITY_ID = 'CONTACT' AND FM.TYPE_ID = '{$DB->ForSql($communicationType)}' AND C.NAME LIKE '{$firstNameSql}' AND C.LAST_NAME LIKE '{$lastNameSql}' LEFT OUTER JOIN {$companyTableName} CO ON C.COMPANY_ID = CO.ID LEFT JOIN b_uts_crm_contact CA ON C.ID = CA.VALUE_ID WHERE (NOT CA.UF_CRM_1566294330350 IS NOT NULL OR NOT CA.UF_CRM_1566294330350=1)";
        }
        else
        {
            $sql  = "SELECT FM.ELEMENT_ID, FM.VALUE_TYPE, FM.VALUE, C.NAME, C.SECOND_NAME, C.LAST_NAME, C.HONORIFIC, C.PHOTO, CO.TITLE COMPANY_TITLE FROM {$fieldMultiTableName} FM INNER JOIN {$contactTableName} C ON FM.ELEMENT_ID = C.ID AND FM.ENTITY_ID = 'CONTACT' AND FM.TYPE_ID = '{$DB->ForSql($communicationType)}' AND (C.NAME LIKE '{$needleSql}' OR C.LAST_NAME LIKE '{$needleSql}') LEFT OUTER JOIN {$companyTableName} CO ON C.COMPANY_ID = CO.ID LEFT JOIN b_uts_crm_contact CA ON C.ID = CA.VALUE_ID WHERE (NOT CA.UF_CRM_1566294330350 IS NOT NULL OR NOT CA.UF_CRM_1566294330350=1)";
        }

        if($companyId>0){
            $sql.=" AND (C.COMPANY_ID=$companyId OR C.ID IN (SELECT F.CONTACT_ID  FROM b_crm_contact_company F where F.COMPANY_ID=$companyId))";
        }

        if($top > 0)
        {
            $sql = $DB->TopSql($sql, $top);
        }

        $dbRes = $DB->Query(
            $sql,
            false,
            'FILE: '.__FILE__.'<br /> LINE: '.__LINE__
        );

        while($arRes = $dbRes->Fetch())
        {
            $result[] = CAllCrmActivity::ReadContactCommunication($arRes, $communicationType);
        }

        //Search By Communication
        $sql  = "SELECT FM.ELEMENT_ID, FM.VALUE_TYPE, FM.VALUE, C.NAME, C.SECOND_NAME, C.LAST_NAME, C.HONORIFIC, C.PHOTO, CO.TITLE COMPANY_TITLE FROM {$fieldMultiTableName} FM INNER JOIN {$contactTableName} C ON FM.ELEMENT_ID = C.ID AND FM.ENTITY_ID = 'CONTACT' AND FM.TYPE_ID = '{$DB->ForSql($communicationType)}' AND FM.VALUE LIKE '{$needleSql}' LEFT OUTER JOIN {$companyTableName} CO ON C.COMPANY_ID = CO.ID LEFT JOIN b_uts_crm_contact CA ON C.ID = CA.VALUE_ID WHERE (NOT CA.UF_CRM_1566294330350 IS NOT NULL OR NOT CA.UF_CRM_1566294330350=1)g";

        if($companyId>0){
            $sql.=" AND (C.COMPANY_ID=$companyId OR C.ID IN (SELECT F.CONTACT_ID  FROM b_crm_contact_company F where F.COMPANY_ID=$companyId))";
        }

        if($top > 0)
        {
            $sql = $DB->TopSql($sql, $top);
        }

        $dbRes = $DB->Query(
            $sql,
            false,
            'FILE: '.__FILE__.'<br /> LINE: '.__LINE__
        );

        while($arRes = $dbRes->Fetch())
        {
            $result[] = CAllCrmActivity::ReadContactCommunication($arRes, $communicationType);
        }

        return $result;
    }
}