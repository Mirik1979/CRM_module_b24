<?php

namespace local\Events;

class UserTypeFieldEnumListEnum extends \CDBResult
{
    function GetList()
    {
        $by = "FIELD_NAME";
        $order = "ASC";
        $rs = false;
        $rs = \CUserTypeEntity::GetList([$by=>$order],[
            'LANG' => 'ru',
            'ENTITY_ID' => 'CRM_STORES',
            'USER_TYPE_ID' => 'double',
        ]);
        if($rs)
            $rs = new self($rs);
        return $rs;
    }

    function GetNext($bTextHtmlAuto=true, $use_tilda=true)
    {
        $r = parent::GetNext($bTextHtmlAuto, $use_tilda);
        if($r){
            $res=[
                "ID" => $r["ID"],
                "VALUE" => $r["EDIT_FORM_LABEL"]
            ];
            return $res;
        }
        return $r;
    }
}