<?php

namespace local\Events;

class UserTypeFieldEnumEnum extends \CDBResult
{
    function GetList($USER_FIELD_ID)
    {
        $rs = false;
        $obEnum = new \CUserFieldEnum;
        $rs = $obEnum->GetList(array(), array("USER_FIELD_ID" => $USER_FIELD_ID));
        if($rs)
            $rs = new self($rs);
        return $rs;
    }
    function GetNext($bTextHtmlAuto=true, $use_tilda=true)
    {
        $r = parent::GetNext($bTextHtmlAuto, $use_tilda);
        return $r;
    }
}